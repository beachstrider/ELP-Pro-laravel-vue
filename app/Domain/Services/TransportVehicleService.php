<?php

namespace App\Domain\Services;

use App\Domain\Models\TransportVehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TransportVehicleService extends BaseService
{
    /**
     * @var TransportVehicle
     */
    public $model = TransportVehicle::class;

    public $searchColumns = ['capacity', 'year_of_production', 'title', 'type', 'plate_number', 'brand', 'euro_norm'];

    public $filterColumns = ['from_date', 'to_date', 'suppliers', 'types'];

    public $with = ['supplier'];

    public $detailWith = ['supplier'];

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
                } else if ($column == 'types') {
                    $query->whereIn('type', $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    public function hasLocation($input, $id = null)
    {
        $exists = $this->model::query()
            ->where('supplier_id', $input['supplier_id'])
            ->where('capacity', $input['capacity'])
            ->where('title', $input['title'])
            ->where('type', $input['type'])
            ->where('plate_number', $input['plate_number']);

        if (!blank($id)) {
            $exists = $exists->where('guid', '!=', $id);
        }

        if ($exists->count() > 0) {
            return true;
        }

        return false;
    }
}
