<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\SupplierService;
use App\Domain\Services\TransportVehicleService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\TransportVehicleResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransportVehicleController extends CrudController
{
    /**
     * @var TransportVehicleService
     */
    public $service;

    public $supplierService;

    public $resource = TransportVehicleResource::class;

    public function __construct(TransportVehicleService $service, SupplierService $supplierService)
    {
        $this->service = $service;

        $this->supplierService = $supplierService;
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
            'supplier_id' => ['required'],
            'capacity' => ['required', 'numeric', 'min:0', new ValidateTag()],
            'title' => ['required', 'max:191',new ValidateTag()],
            'type' => ['required', 'in:air,land,rail,road,water,other means', new ValidateTag()],
            'plate_number' => ['required', 'numeric', new ValidateTag()],
            'brand' => ['required', 'max:194', new ValidateTag()],
            'euro_norm' => ['required', 'max:194', new ValidateTag()],
            'year_of_production' => ['required', 'numeric', new ValidateTag()],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $validated = $this->validate($request, $rules);
        $validated['supplier_id'] = $this->supplierService->model::query()->findOrFailByGuid($validated['supplier_id'])->id;

        if($this->service->hasLocation($validated, $id)) {
            throw ValidationException::withMessages(['supplier_id' => __('validation.duplicate')]);
        }

        return $validated;
    }
}
