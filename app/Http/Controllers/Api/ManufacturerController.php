<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\BrandService;
use App\Domain\Services\ContactService;
use App\Domain\Services\LocationService;
use App\Domain\Services\DocumentService;
use App\Domain\Services\LocationTypeService;
use App\Domain\Services\ManufacturerService;
use App\Domain\Services\ModelService;
use App\Domain\Services\SupplierService;
use App\Domain\Services\UserService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\ManufacturerResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;

class ManufacturerController extends CrudController
{
    /**
     * @var ManufacturerService
     */
    public $service;

    public $resource = ManufacturerResource::class;

    public function __construct(ManufacturerService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request, $id = null)
    : array
    {
        $rules = [
            'is_active' => [],
            'name' => ['required', 'max:191', new ValidateTag()],
            'main_location_id' => ['required', new ValidateTag()],
            'comment' => [new ValidateTag()],
            'phone' => ['required', 'max:191', new ValidateTag()],
            'email' => ['required', 'max:191', 'email', new ValidateTag()],
            'fax' => ['required', 'max:191', new ValidateTag()],
            'manufacturer_brands' => [],
            'manufacturer_brands.*.brand_id' => ['required'],
            'manufacturer_brands.*.models' => ['required', 'array'],
            'manufacturer_contacts' => [],
            'manufacturer_contacts.*.contact_id' => ['required'],
            'manufacturer_contacts.*.locations' => [],

            'manufacturer_locations' => ['array'],
            'manufacturer_locations.*.location_type_id' => ['required'],
            'manufacturer_locations.*.location_id' => ['required'],
            'manufacturer_locations.*.supplier_id' => ['required'],
            'manufacturer_locations.*.suppliers' => ['array'],
            'manufacturer_locations.*.brands' => ['array'],
            'manufacturer_locations.*.models' => ['array'],
            'manufacturer_documents' => [],
            'manufacturer_documents.*.document' => ['required', 'array'],
            'manufacturer_documents.*.title' => ['required', 'max:191'],
        ];

        $validated = $this->validate($request, $rules);

        if (!$id) { // create
            $validated['author_id'] = auth('api')->id();
        }

        $locationService = app()->make(LocationService::class);
        $locationTypeService = app()->make(LocationTypeService::class);
        $contactService = app()->make(ContactService::class);
        $brandService = app()->make(BrandService::class);
        $modelService = app()->make(ModelService::class);
        $supplierService = app()->make(SupplierService::class);
        $documentService = app()->make(DocumentService::class);
        $validated['manufacturer_contacts'] = $this->validateManufacturerContacts($validated, $contactService, $locationService);
        $validated['manufacturer_brands'] = $this->validateManufacturerBrands($validated, $brandService, $modelService);
        $validated['manufacturer_documents'] = $this->validateManufacturerDocuments($validated, $documentService);
        $validated['main_location_id'] = $locationService->model::query()->findOrFailByGuid($validated['main_location_id'])->id;
        $validated['manufacturer_locations'] = $this->validateManufacturerLocations($validated, $locationService, $supplierService, $brandService, $modelService, $locationTypeService);
        $validated['first_name'] = $validated['name'];
        return $validated;
    }

    private function validateManufacturerContacts($validated, $contactService, $locationService)
    {
        $manufacturer_contacts = array_map(function ($item) use ($contactService, $locationService) {
            return array_merge($item, [
                'contact_id' => $contactService->model::query()->findOrFailByGuid($item['contact_id'])->id,
                'locations' => $locationService->model::query()->whereIn('guid', $item['locations'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['manufacturer_contacts']);

        return $manufacturer_contacts;
    }

    private function validateManufacturerBrands($validated, $brandService, $modelService)
    {
        $manufacturer_brands = array_map(function ($item) use ($brandService, $modelService) {
            return array_merge($item, [
                'brand_id' => $brandService->model::query()->findOrFailByGuid($item['brand_id'])->id,
                'models' => $modelService->model::query()->whereIn('guid', $item['models'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['manufacturer_brands']);

        return $manufacturer_brands;
    }

    private function validateManufacturerLocations($validated, $locationService, $supplierService, $brandService, $modelService, $locationTypeService)
    {
        $manufacturer_locations = array_map(function ($item) use ($locationService, $supplierService, $brandService, $modelService, $locationTypeService) {
            return array_merge($item, [
                'location_id' => $locationService->model::query()->findOrFailByGuid($item['location_id'])->id,
                'location_type_id' => $locationTypeService->model::query()->findOrFailByGuid($item['location_type_id'])->id,
                'supplier_id' => $supplierService->model::query()->findOrFailByGuid($item['supplier_id'])->id, // Supplier
                'suppliers' => $supplierService->model::query()->whereIn('guid', $item['suppliers'])->get()->pluck('id')->toArray(),
                'brands' => $brandService->model::query()->whereIn('guid', $item['brands'])->get()->pluck('id')->toArray(),
                'models' => $modelService->model::query()->whereIn('guid', $item['models'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['manufacturer_locations']);

        return $manufacturer_locations;
    }

    private function validateManufacturerDocuments($validated, $documentService)
    {
        $manufacturer_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['manufacturer_documents']);

        return $manufacturer_documents;
    }

}


