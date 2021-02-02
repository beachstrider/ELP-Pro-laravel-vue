<?php

namespace App\Domain\Services;

use App\Domain\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class LocationService extends BaseService
{
    /**
     * @var Location
     */
    public $model = Location::class;

    public $searchColumns = ['street', 'street_no', 'zip', 'city', 'country', 'code', 'from_opening_hours', 'to_opening_hours'];

    public $filterColumns = ['from_date', 'to_date', 'countries', 'code'];

    public $with = [];

    public $detailWith = [];

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
                }  else if ($column == 'countries') {
                    $query->orWhereHas('country', function ($qry) use ($value, $column) {
                        $qry->whereIn('countries.name', $value);
                    });
                } else if ($column == 'code') {
                    if (strpos($value, ', ') !== false) {
                        $array = explode(', ', $value);
                    } else if (strpos($value, ',') !== false) {
                        $array = explode(',', $value);
                    } else {
                        $array[0] = $value;
                    }
                    $query->whereIn('code', $array);
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

        if($entity->clientContactLocations()->exists() ||
            $entity->clients()->exists() ||
            $entity->manufacturers()->exists() ||
            $entity->suppliers()->exists() ||
            $entity->manufacturerLocations()->exists() ||
            $entity->manufacturerContactLocations()->exists()) {
            throw ValidationException::withMessages(['message' => __('validation.delete_dependency')]);
        }

        if ($entity)
            return $entity->delete();

        return false;
    }

    public function hasLocation($input, $id = null)
    {
        $exists = $this->model::query()
            ->where('street', $input['street'])
            ->where('street_no', $input['street_no'])
            ->where('zip', $input['zip'])
            ->where('country', $input['country'])
            ->where('city', $input['city']);

        if (!blank($id)) {
            $exists = $exists->where('guid', '!=', $id);
        }

        if ($exists->count() > 0) {
            return true;
        }

        return false;
    }
}
