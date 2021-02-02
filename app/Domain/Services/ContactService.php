<?php

namespace App\Domain\Services;

use App\Domain\Models\Brand;
use App\Domain\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ContactService extends BaseService
{
    /**
     * @var Contact
     */
    public $model = Contact::class;

    public $searchColumns = ['name', 'email', 'phone', 'mobile', 'functions'];

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

        if ($entity->manufacturerContacts()->exists() || $entity->clientContacts()->exists()) {
            throw ValidationException::withMessages(['message' => __('validation.delete_dependency')]);
        }

        if ($entity)
            return $entity->delete();

        return false;
    }

    public function hasContact($input, $id = null)
    {
        $exists = $this->model::query()
            ->where('name', $input['name'])
            ->where('email', $input['email'])
            ->where('mobile', $input['mobile']);

        if (!blank($id)) {
            $exists = $exists->where('guid', '!=', $id);
        }

        if ($exists->count() > 0) {
            return true;
        }

        return false;
    }
}



