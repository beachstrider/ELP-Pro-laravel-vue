<?php

namespace App\Domain\Services;

use App\Domain\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class BrandService extends BaseService
{
    /**
     * @var Brand
     */
    public $model = Brand::class;

    public $searchColumns = ['title'];

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

        if($entity->models()->exists() || $entity->clientBrandModels()->exists() || $entity->manufacturerBrands()->exists() || $entity->manufacturerLocationBrands()->exists()) {
            throw ValidationException::withMessages(['message' => __('validation.delete_dependency')]);
        }

        if ($entity)
            return $entity->delete();

        return false;
    }
}
