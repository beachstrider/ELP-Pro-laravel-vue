<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\BrandService;
use App\Domain\Services\ClientService;
use App\Domain\Services\ContactService;
use App\Domain\Services\DocumentService;
use App\Domain\Services\LocationService;
use App\Domain\Services\LocationTypeService;
use App\Domain\Services\ModelService;
use App\Domain\Services\DealerService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\ClientResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends CrudController
{
    /**
     * @var ClientService
     */
    public $service;

    public $resource = ClientResource::class;

    public function __construct(ClientService $service)
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
            'company_name' => ['required', 'max:191', new ValidateTag()],
            'main_location_id' => ['required', new ValidateTag()],
            'comment' => [new ValidateTag()],
            'phone' => ['required', 'max:191', new ValidateTag()],
            'email' => ['required', 'max:191', 'email', new ValidateTag()],
            'fax' => ['max:191'],
            "identification_number" => [
                'required',
                "alpha_dash",
                Rule::unique("clients", "identification_number")
                    ->ignore($id, "guid"),
            ],
            'client_dealers' => [],
            'client_locations' => [],
            'client_locations.*.location_id' => ['required'],
            'client_locations.*.location_type_id' => ['required'],
            'client_contacts' => [],
            'client_contacts.*.contact_id' => ['required'],
            'client_contacts.*.locations' => [],
            'client_brands' => [],
            'client_brands.*.brand_id' => ['required'],
            'client_brands.*.models' => ['required', 'array'],
            'client_documents' => [],
            'client_documents.*.document' => ['required', 'array'],
            'client_documents.*.title' => ['required', 'max:191'],
        ];

        $validated = $this->validate($request, $rules);

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $locationService = app()->make(LocationService::class);
        $locationTypeService = app()->make(LocationTypeService::class);
        $contactService = app()->make(ContactService::class);
        $documentService = app()->make(DocumentService::class);
        $brandService = app()->make(BrandService::class);
        $modelService = app()->make(ModelService::class);
        $validated['client_dealers'] = app()->make(DealerService::class)->model::query()->whereIn('guid', $validated['client_dealers'])->get()->pluck('id')->toArray();
        $validated['client_locations'] = $this->validateClientLocations($validated, $locationService, $locationTypeService);
        $validated['client_contacts'] = $this->validateClientContacts($validated, $contactService, $locationService);
        $validated['client_brands'] = $this->validateClientBrands($validated, $brandService, $modelService);
        $validated['client_documents'] = $this->validateClientDocuments($validated, $documentService);
        $validated['main_location_id'] = $locationService->model::query()->findOrFailByGuid($validated['main_location_id'])->id;
        return $validated;
    }

    private function validateClientContacts($validated, $contactService, $locationService)
    {
        $client_contacts = array_map(function ($item) use ($contactService, $locationService) {
            return array_merge($item, [
                'contact_id' => $contactService->model::query()->findOrFailByGuid($item['contact_id'])->id,
                'locations' => $locationService->model::query()->whereIn('guid', $item['locations'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['client_contacts']);

        return $client_contacts;
    }

    private function validateClientBrands($validated, $brandService, $modelService)
    {
        $client_brands = array_map(function ($item) use ($brandService, $modelService) {
            return array_merge($item, [
                'brand_id' => $brandService->model::query()->findOrFailByGuid($item['brand_id'])->id,
                'models' => $modelService->model::query()->whereIn('guid', $item['models'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['client_brands']);

        return $client_brands;
    }

    private function validateClientLocations($validated, $locationService, $locationTypeService)
    {
        $client_brands = array_map(function ($item) use ($locationService, $locationTypeService) {
            return array_merge($item, [
                'location_id' => $locationService->model::query()->findOrFailByGuid($item['location_id'])->id,
                'location_type_id' => $locationTypeService->model::query()->findOrFailByGuid($item['location_type_id'])->id,
            ]);
        }, $validated['client_locations']);

        return $client_brands;
    }

    private function validateClientDocuments($validated, $documentService)
    {
        $client_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['client_documents']);

        return $client_documents;
    }
}

