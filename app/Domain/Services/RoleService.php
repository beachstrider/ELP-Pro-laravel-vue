<?php

namespace App\Domain\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class RoleService extends BaseService
{
    /**
     * @var Role
     */
    public $model = Role::class;

    public $searchColumns = ['name'];

    public $filterColumns = ['name', 'from_date', 'to_date'];

    public $with = ['permissions'];

    protected $primaryKey = 'id';

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
        $role = Role::query()
            ->create([
                'slug' => $inputs['name'],
                'name' => $inputs['name'],
            ]);

        $role->description = $inputs['name'];
        $role->save();
        $permissions = Permission::whereIn('id', $inputs['permissions'])->get()->pluck('id')->toArray();
        $role->syncPermissions($permissions);

        DB::commit();

        return $role;
    }

    public function update($id, array $inputs)
    {
        $this->model::query()->where($this->primaryKey, $id)
            ->whereNotIn('slug', ['superadmin', 'buyer', 'seller'])
            ->update([
                'name' => $inputs['name'],
                'slug' => $inputs['name'],
                'description' => $inputs['name'],
            ]);

        $role = Role::findOrFail($id);
        $permissions = Permission::whereIn('id', $inputs['permissions'])->get()->pluck('id')->toArray();
        $role->syncPermissions($permissions);
        return $this->getDetail($id, $inputs);
    }

    public function delete($id, array $input = [])
    {
        $entity = $this->model::query()
            ->whereNotIn('name', [
                'SuperAdmin',
            ])
            ->where($this->primaryKey, $id)
            ->firstOrFail();

        if ($entity->users()->count() > 0)
            throw new \Exception('Already In Use!');

        if ($entity)
            return $entity->delete();

        return false;
    }
}
