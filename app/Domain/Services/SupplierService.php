<?php

namespace App\Domain\Services;

use App\Domain\Models\DeletedUser;
use App\Domain\Models\Document;
use App\Domain\Models\Supplier;
use App\Domain\Models\SupplierDocument;
use App\Domain\Models\User;
use App\Domain\Models\SupplierContact;
use App\Domain\Models\SupplierLocation;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SupplierService extends BaseService
{
    /**
     * @var Supplier
     */
    public $model = Supplier::class;

    /**
     * @var User
     */
    public $userModel = User::class;

    /**
     * @var SupplierContact
     */
    public $modelSupplierContact = SupplierContact::class;

    /**
     * @var SupplierLocation
     */
    public $modelSupplierLocation = SupplierLocation::class;

    /**
     * @var SupplierDocument
     */
    public $modelSupplierDocument = SupplierDocument::class;

    public $searchColumns = ['name', 'first_name', 'last_name', 'phone', 'email', 'fax', 'identification_number'];

    public $filterColumns = ['start_date', 'end_date'];

    public $with = ['mainLocation', 'supplierLogisticTypes', 'supplierUserTypes'];

    public $detailWith = [
        'mainLocation', 'supplierContacts', 'supplierContacts.contact', 'supplierContacts.locations',
        'supplierLocations', 'supplierLocations.location', 'supplierLocations.locationType',
        'supplierDocuments', 'supplierDocuments.document',
        'supplierUserTypes', 'supplierContracts', 'supplierLogisticTypes'
    ];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    protected function filterQuery(Builder $query, $input = [])
    {
        foreach (Arr::get($input, 'filters', []) as $column => $value) {
            if (($value && in_array($column, $this->filterColumns))) {
                if ((in_array($column, ['start_date', 'end_date']))) {
                    $query->where(function ($q) use ($column, $value) {
                        if ($column === 'start_date' && !empty($value)) {
                            $q->whereDate('start_date', '>=', $value);
                        }

                        if ($column === 'end_date' && !empty($value)) {
                            $q->whereDate('end_date', '<=', $value);
                        }
                    });
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    public function create(array $input)
    {
        DB::beginTransaction();
        $userInput = Arr::only($input, ['email', 'name', 'first_name', 'last_name', 'phone', 'is_active']);
        $userInput = array_merge($userInput, ['email_verified_at' => ($userInput['is_active'] ? now() : null), 'password' => bcrypt($input['password']) ]);
        $user = $this->userModel::query()->create($userInput);
        $supplierRole = Role::whereSlug('supplier')->first();
        $user->attachRole($supplierRole);

        $input['user_id'] = $user->id;

        $entity = $this->model::create(Arr::except($input, ['supplier_contacts', 'supplier_locations', 'supplier_documents', 'supplier_logistic_types', 'supplier_user_types', 'password']));
        $entity->supplierLogisticTypes()->attach($input['supplier_logistic_types']);
        $entity->supplierUserTypes()->attach($input['supplier_user_types']);
        $this->addUpdateSupplierContact($input, $entity);
        $this->addUpdateSupplierLocation($input, $entity);
        $this->addUpdateSupplierDocument($input, $entity);
        DB::commit();

        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);

        $user = $this->userModel::query()->findOrFail($entity['user_id']);
        $userInput = Arr::only($input, ['name', 'first_name', 'last_name', 'phone', 'is_active']);
        $userInput = array_merge($userInput, ['email_verified_at' => ($userInput['is_active'] ? now() : null), 'password' => (!blank($input['password']) ? bcrypt($input['password']) : $user->password) ]);
        $user->update($userInput);

        $entity->supplierLogisticTypes()->sync($input['supplier_logistic_types']);
        $entity->supplierUserTypes()->sync($input['supplier_user_types']);
        $entity->update(Arr::except($input, ['supplier_contacts', 'supplier_locations', 'supplier_documents', 'supplier_logistic_types', 'supplier_user_types', 'password']));
        $this->addUpdateSupplierContact($input, $entity);
        $this->addUpdateSupplierLocation($input, $entity);
        $this->addUpdateSupplierDocument($input, $entity);
        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdateSupplierContact($input, $supplier)
    {
        $this->modelSupplierContact::query()->where('supplier_id', $supplier->id)->delete();

        foreach ($input['supplier_contacts'] as $key => $value) {
            $supplierContact = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $supplierContact = $this->modelSupplierContact::query()->withTrashed()->where('id', $value['id'])->first();
                $supplierContact->deleted_at = null;
            }

            if (empty($supplierContact) || is_null($supplierContact)) {
                $supplierContact = new $this->modelSupplierContact();
            }

            $supplierContact->supplier_id = $supplier->id;
            $supplierContact->contact_id = $value['contact_id'];
            $supplierContact->save();

            $supplierContact->locations()->sync($value['locations']);
            DB::table('supplier_contact_locations')->where('supplier_contact_id', $supplierContact->id)->update(['supplier_id' => $supplier->id]);
        }
    }

    private function addUpdateSupplierLocation($input, $supplier)
    {
        $this->modelSupplierLocation::query()->where('supplier_id', $supplier->id)->delete();

        foreach ($input['supplier_locations'] as $key => $value) {
            $supplierLocation = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $supplierLocation = $this->modelSupplierLocation::query()->withTrashed()->where('id', $value['id'])->first();
                $supplierLocation->deleted_at = null;
            }

            if (empty($supplierLocation) || is_null($supplierLocation)) {
                $supplierLocation = new $this->modelSupplierLocation();
            }

            $supplierLocation->supplier_id = $supplier->id;
            $supplierLocation->location_type_id = $value['location_type_id'];
            $supplierLocation->location_id = $value['location_id'];
            $supplierLocation->save();
        }
    }

    private function addUpdateSupplierDocument($input, $supplier)
    {
        $this->modelSupplierDocument::query()->where('supplier_id', $supplier->id)->delete();
        $supplierDocumentIds = [];
        $documentIds = [];
        foreach ($input['supplier_documents'] as $key => $value) {
            $supplierDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $supplierDocument = $this->modelSupplierDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $supplierDocument->deleted_at = null;
            }

            if (empty($supplierDocument) || is_null($supplierDocument)) {
                $supplierDocument = new $this->modelSupplierDocument();
            }

            $supplierDocument->title = $value['title'];
            $supplierDocument->supplier_id = $supplier->id;
            $supplierDocument->document_id = $value['document_id'];
            $supplierDocument->save();

            Document::whereObjectId($supplier->id)
                ->whereObjectType('supplier_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                ->whereGuid($value['document']['id'])
                ->update([
                    'object_type' => 'supplier_document',
                    'object_id' => $supplier->id,
                    'deleted_at' => null
                ]);

                $documentIds[] = $value['document']['id'];
            }

            $supplierDocumentIds[] = $supplierDocument->id;
        }

        Document::whereNotIn('object_id', $supplierDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('supplier_document')
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
