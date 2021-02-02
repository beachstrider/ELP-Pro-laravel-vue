<?php

namespace App\Domain\Services;

use App\Domain\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class RouteService extends BaseService
{
    /**
     * @var Route
     */
    public $model = Route::class;

    public $searchColumns = ['name'];

    public $filterColumns = ['from_date', 'to_date'];

    public $with = [];

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
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    public function deleteByGuid($id, array $input = [])
    {
        $entity = $this->model::query()->where('guid', $id)->first();

        if($entity->prices()->exists()) {
            throw ValidationException::withMessages(['message' => __('validation.delete_dependency')]);
        }

        if ($entity)
            return $entity->delete();

        return false;
    }
}
