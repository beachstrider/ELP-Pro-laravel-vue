<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\LocationTypeService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\LocationTypeResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationTypeController extends CrudController
{
    /**
     * @var LocationTypeService
     */
    public $service;

    public $resource = LocationTypeResource::class;

    public function __construct(LocationTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request, $id = null)
    : array
    {
        $rules = [
            'title' => ['required', 'max:191',
                                Rule::unique("location_types")
                                ->ignore($id, "guid")
                                ->whereNull('deleted_at'),
                            new ValidateTag()],
            'is_active' => ['required'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $validated = $this->validate($request, $rules);

        return $validated;
    }
}
