<?php

namespace App\Domain\Services;

use App\Domain\Models\BrandModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ModelService extends BaseService
{
    /**
     * @var BrandModel
     */
    public $model = BrandModel::class;

    public $searchColumns = ['title', 'type', 'length', 'width', 'type', ['relationship' => 'brand', 'column' => 'title']];

    public $filterColumns = ['from_date', 'to_date', 'brands'];

    public $with = ['brand'];

    public $detailWith = ['brand'];

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
                } else if ($column == 'brands') {
                    $query->orWhereHas('brand', function ($qry) use ($value, $column) {
                        $qry->whereIn('brands.guid', $value);
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

        if($entity->clientBrandModels()->exists() || $entity->manufacturerBrandModels()->exists() || $entity->manufacturerLocationBrandModels()->exists()) {
            throw ValidationException::withMessages(['message' => __('validation.delete_dependency')]);
        }

        if ($entity)
            return $entity->delete();

        return false;
    }
}
