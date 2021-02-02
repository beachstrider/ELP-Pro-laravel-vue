<?php

namespace App\Domain\Services;

use App\Domain\Models\DeletedUser;
use App\Domain\Models\Document;
use App\Domain\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{
    /**
     * @var User
     */
    public $model = User::class;

    public $searchColumns = ['name', 'email', 'phone', 'email', ['relationship' => 'roles', 'column' => 'name']];

    public $filterColumns = ['from_date', 'to_date', 'roles'];

    public $with = ['roles', 'profilePic'];

    public $detailWith = ['profilePic'];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    public function getList(array $input = [])
    {
        $query = $this->model::query();

        $query = $this->listQuery($query, $input);

        return $this->resultQuery($query, $input);
    }

    protected function filterQuery(Builder $query, $input = [])
    {
        foreach (Arr::get($input, 'filters', []) as $column => $value) {
            if (($value && in_array($column, $this->filterColumns)) || ($column === 'status' && $value >= 0)) {
                if ($column == 'roles') {
                    $query->orWhereHas('roles', function ($qry) use ($value, $column) {
                        $qry->whereIn('roles.id', $value);
                    });
                } else if ($column === 'status') {
                    if ($value) {
                        $query->whereNotNull('email_verified_at');
                    } else {
                        $query->whereNull('email_verified_at');
                    }
                } else if ((in_array($column, ['from_date', 'to_date']))) {
                    $query->where(function ($q) use ($column, $value) {
                        if ($column === 'from_date' && !empty($value)) {
                            $q->whereDate('created_at', '>=', $value);
                        }

                        if ($column === 'to_date' && !empty($value)) {
                            $q->whereDate('created_at', '<=', $value);
                        }
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
        $user = User::query()
            ->create(
                Arr::except($inputs, ['role']),
            );
        $user->name = $inputs['first_name'] .' '. $inputs['last_name'];
        $user->password = bcrypt($inputs['password']);
        $user->email_verified_at = ($inputs['status'] ? now() : null);
        $user->save();
        $user->syncRoles([$inputs['role']]);

        if (isset($inputs['profile']) && !empty($inputs['profile'])) {
            Document::withTrashed()->whereGuid($inputs['profile'])
                ->update([
                    'object_type' => 'profile_pic',
                    'object_id' => $user->id,
                    'deleted_at' => null
                ]);
        }

        DB::commit();
        return $user;
    }

    public function updateByGuid($guid, array $inputs)
    {
        DB::beginTransaction();
        $user = User::query()->with('roles')->findOrFailByGuid($guid);

        if (!empty($inputs['password'])) {
            $inputs['password'] = bcrypt($inputs['password']);
            $user->password = $inputs['password'];
            self::revokeToken($user);
        } else {
            unset($inputs['password']);
        }

        if (isset($inputs['status'])) {
            $user->email_verified_at = ($inputs['status'] ? (($user->email_verified_at) ? $user->email_verified_at : now()) : null);
            $user->save();

            if (!$inputs['status'])
                self::revokeToken($user);
        }

        $user->first_name = $inputs['first_name'];
        $user->last_name = $inputs['last_name'];
        $user->name = $inputs['first_name'] .' '. $inputs['last_name'];
        $user->phone = $inputs['phone'];
        $user->email = $inputs['email'];

        $user->save();

        $inputs['user_id'] = $user->id;
//        $user->syncRoles([$inputs['role']]);

        Document::whereObjectId($user->id)->whereObjectType('profile_pic')->delete();
        if (isset($inputs['profile']) && !empty($inputs['profile'])) {
            Document::withTrashed()->whereGuid($inputs['profile'])
            ->update([
                'object_type' => 'profile_pic',
                'object_id' => $user->id,
                'deleted_at' => null
            ]);
        }

        DB::commit();
        return $user;
    }

    public function deleteByGuid($id, array $inputs = [])
    {
        $entity = $this->model::query()->where('guid', $id)->first();

        if ($entity->id == $inputs['creator_id']) {
            throw new \Exception('You can not delete your own data.');
        }

        self::revokeToken($entity);
        DeletedUser::customCreate($entity);

        if ($entity)
            return $entity->delete();

        return false;
    }

    public static function revokeToken($user)
    {
        foreach ($user->tokens as $token) {
            $token->revoke();
        }
    }

    public function getDetailByGuid($id, array $input = [])
    {
        $query = $this->model::query()->with(['roles']);

        foreach ($input as $column => $value) {
            $query->where($column, $value);
        }

        return $query->findOrFailByGuid($id);
    }

    public function updateProfile($id, array $inputs = [])
    {
        DB::beginTransaction();
        $user = $this->model::query()->findOrFailByGuid($id);

        if (!empty($inputs['password'])) {
            $inputs['password'] = bcrypt($inputs['password']);

            $user->password = $inputs['password'];
            self::revokeToken($user);
        } else {
            unset($inputs['password']);
        }

        $user->first_name = $inputs['first_name'];
        $user->last_name = $inputs['last_name'];
        $user->phone = $inputs['phone'];
        $user->email = $inputs['email'];
        $user->save();

        $inputs['user_id'] = $user->id;
        Document::whereObjectId($user->id)->whereObjectType('profile_pic')->delete();

        if (isset($inputs['profile_pic']) && !empty($inputs['profile_pic'])) {
            Document::withTrashed()->whereGuid($inputs['profile_pic'])->update([
                'object_type' => 'profile_pic',
                'object_id' => $user->id,
                'deleted_at' => null
            ]);
        }

        DB::commit();

        return $user;
    }

    public function suspendUser($id, array $inputs = [])
    {
        $entity = $this->model::query()->where('guid', $id)->first();

        if ($entity->id == $inputs['creator_id']) {
            throw new \Exception('You can not update your own data.');
        }

        DB::beginTransaction();
        $user = $this->model::with('roles')->findOrFailByGuid($id);
        $user->from_suspended_at = $inputs['from_suspended_at'];
        $user->to_suspended_at = $inputs['to_suspended_at'];
        $user->save();
        self::revokeToken($user);

        DB::commit();
    }


    public function activateUser($id, array $inputs = [])
    {
        $entity = $this->model::query()->where('guid', $id)->first();

        if ($entity->id == $inputs['creator_id']) {
            throw new \Exception('You can not update your own data.');
        }

        DB::beginTransaction();
        $user = $this->model::with('roles')->findOrFailByGuid($id);
        $user->from_suspended_at = null;
        $user->to_suspended_at = null;
        $user->save();
        self::revokeToken($user);

        DB::commit();
    }

}
