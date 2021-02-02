<?php

namespace App\Domain\Services;

use App\Domain\Models\Driver;
use App\Domain\Models\User;
use App\Domain\Models\Document;
use App\Domain\Models\DriverDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;
use App\Domain\Models\DeletedUser;

class DriverService extends BaseService
{
    /**
     * @var Driver
     */
    public $model = Driver::class;

    /**
     * @var User
     */
    public $userModel = User::class;

    /**
     * @var DriverDocument
     */
    public $modelDriverDocument = DriverDocument::class;

    public $searchColumns = [
        ['relationship' => 'supplier', 'column' => 'name'],
        ['relationship' => 'user', 'column' => 'name'],
        ['relationship' => 'user', 'column' => 'phone'],
    ];

    public $filterColumns = ['from_date', 'to_date', 'suppliers'];

    public $with = ['supplier', 'user'];

    public $detailWith = ['supplier', 'user', 'driverDocuments', 'driverDocuments.document'];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    protected function filterQuery(Builder $query, $input = [])
    {
        foreach (Arr::get($input, 'filters', []) as $column => $value) {
            if (($value && in_array($column, $this->filterColumns))) {
                if ((in_array($column, ['from_date', 'to_date']))) {
                    $query->where(function ($q) use ($column, $value) {
                        if ($column === 'from_date' && !empty($value)) {
                            $q->whereDate('created_at', '>=', $value);
                        }

                        if ($column === 'to_date' && !empty($value)) {
                            $q->whereDate('created_at', '<=', $value);
                        }
                    });
                } else if ($column == 'suppliers') {
                    $query->orWhereHas('supplier', function ($qry) use ($value, $column) {
                        $qry->whereIn('suppliers.guid', $value);
                    });
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    public function create(array $inputs)
    {
        DB::beginTransaction();
        $inputs['password'] = bcrypt($inputs['password']);
        $inputs['email_verified_at'] = ($inputs['is_active'] > 0 ? now() : null);
        $user = $this->userModel::query()->create(Arr::except($inputs, ['supplier_id', 'is_active']));

        $driverRole = Role::whereSlug('driver')->first();
        $user->attachRole($driverRole);

        $inputs['user_id'] = $user->id;
        $driver = $this->model::query()->create(Arr::only($inputs, ['is_active', 'supplier_id', 'driver_documents', 'user_id', 'author_id']));
        $this->addUpdateDriverDocument($inputs, $driver);

        DB::commit();
        return $driver;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);
        $user = $this->userModel::query()->findOrFail($entity['user_id']);
        $input['email_verified_at'] = ($input['is_active'] > 0 ? now() : null);
        $password = $input['password'];
        unset($input['password']);
        $user->update($input, Arr::except($input, ['supplier_id', 'is_active']));

        if (!blank($password)) {
            $user->password = bcrypt($password);
            $user->save();
        }

        $entity->is_active = $input['is_active'];
        $entity->save();

        $this->addUpdateDriverDocument($input, $entity);

        DB::commit();
        return $entity;
    }

    private function addUpdateDriverDocument($input, $driver)
    {
        $this->modelDriverDocument::query()->where('driver_id', $driver->id)->delete();
        $driverDocumentIds = [];
        $documentIds = [];
        foreach ($input['driver_documents'] as $key => $value) {
            $driverDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $driverDocument = $this->modelDriverDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $driverDocument->deleted_at = null;
            }

            if (empty($driverDocument) || is_null($driverDocument)) {
                $driverDocument = new $this->modelDriverDocument();
            }

            $driverDocument->title = $value['title'];
            $driverDocument->driver_id = $driver->id;
            $driverDocument->document_id = $value['document_id'];
            $driverDocument->save();

            Document::whereObjectId($driver->id)
                ->whereObjectType('driver_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'driver_document',
                        'object_id' => $driver->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $driverDocumentIds[] = $driverDocument->id;
        }

        Document::whereNotIn('object_id', $driverDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('driver_document')
            ->delete();
    }

    public function deleteByGuid($id, array $inputs = [])
    {
        DB::transaction(function () use ($id, $inputs) {
            $entity = $this->model::query()->where('guid', $id)->first();

            $user = $this->userModel::query()->where('id', $entity->user_id)->first();

            DeletedUser::customCreate($user);

            $user->delete();
            $entity->delete();
        });
    }
}
