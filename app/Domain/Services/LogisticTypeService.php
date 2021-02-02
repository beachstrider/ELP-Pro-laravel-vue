<?php

namespace App\Domain\Services;

use App\Domain\Models\LogisticType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class LogisticTypeService extends BaseService
{
    /**
     * @var LogisticType
     */
    public $model = LogisticType::class;

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
}
