<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\LocationService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\LocationResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LocationController extends CrudController
{
    /**
     * @var LocationService
     */
    public $service;

    public $resource = LocationResource::class;

    public function __construct(LocationService $service)
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
            'street' => ['required', 'max:191', new ValidateTag()],
            'street_no' => ['required', 'max:191', new ValidateTag()],
            'zip' => ['required', 'max:191', new ValidateTag()],
            'city' => ['required', 'max:191', new ValidateTag()],
            'country' => ['required', 'max:191', new ValidateTag()],
            'code' => ['required', 'max:100', 'regex:/^[a-zA-Z0-9-_]+$/',
                Rule::unique("locations")
                    ->ignore($id, "guid")
                    ->whereNull('deleted_at'),
                new ValidateTag()],
            'from_opening_hours' => ['required', 'numeric', 'min:0', new ValidateTag()],
            'to_opening_hours' => ['required', 'numeric', 'min:0', new ValidateTag()],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();

            if (!auth()->user()->hasRole('superadmin')) {
                $validated['user_id'] = auth()->id();
            }
        }

        $validated = $this->validate($request, $rules);

        if($this->service->hasLocation($validated, $id)) {
            throw ValidationException::withMessages(['country' => __('validation.duplicate')]);
        }

        return $validated;
    }
}
