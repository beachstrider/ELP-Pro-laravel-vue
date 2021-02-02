<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\DealerService;
use App\Domain\Services\LocationService;
use App\Domain\Services\ContactService;
use App\Domain\Services\BrandService;
use App\Domain\Services\ModelService;
use App\Domain\Services\DocumentService;
use App\Domain\Services\LocationTypeService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\DealerResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DealerController extends CrudController
{
    /**
     * @var DealerService
     */
    public $service;

    public $resource = DealerResource::class;

    public function __construct(DealerService $service)
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
            "dealer_id" => [
                "required",
                "alpha_dash",
                Rule::unique("dealers")
                    ->ignore($id, "guid"),
            ],
            "name" => ["required", "max:191", new ValidateTag()],
            "phone" => ["required", "max:191", new ValidateTag()],
            "fax" => ["required", "max:191", new ValidateTag()],
            "email" => [
                "required",
                "email:rfc,dns",
                Rule::unique("dealers")
                    ->ignore($id, "guid"),
            ],
            "is_active" => ["required"],
            'main_location_id' => ['required', new ValidateTag()],
            'dealer_additional_locations' => ['required'],
            'dealer_contacts' => [],
            'dealer_contacts.*.contact_id' => ['required'],
            'dealer_contacts.*.locations' => ['array'],
            'dealer_brands' => [],
            'dealer_brands.*.brand_id' => ['required'],
            'dealer_brands.*.models' => ['required', 'array'],
            "comment" => [],
            'dealer_documents' => [],
            'dealer_documents.*.document' => ['required', 'array'],
            'dealer_documents.*.title' => ['required', 'max:191'],
        ];

        if (!$id) { // create
            $validated["author_id"] = auth('api')->id();
        }

        $validated = $this->validate($request, $rules);

        $names = explode(" ", $validated["name"]);
        if (count($names) > 0) {
            $validated["first_name"] = $names[0];
            unset($names[0]);
            $validated["last_name"] = implode(" ", $names);
            $validated["name"] = $validated["first_name"] . " " . $validated["last_name"];
        }

        $locationService = app()->make(LocationService::class);
        $locationTypeService = app()->make(LocationTypeService::class);
        $contactService = app()->make(ContactService::class);
        $brandService = app()->make(BrandService::class);
        $modelService = app()->make(ModelService::class);
        $documentService = app()->make(DocumentService::class);
        $validated['dealer_contacts'] = $this->validateDealerContacts($validated, $contactService, $locationService, $locationTypeService);
        $validated['dealer_brands'] = $this->validateDealerBrands($validated, $brandService, $modelService);
        $validated['main_location_id'] = $locationService->model::query()->findOrFailByGuid($validated['main_location_id'])->id;
        $validated['dealer_additional_locations'] = $this->validateDealerAdditionalLocations($validated, $locationTypeService, $locationService);
        $validated['dealer_documents'] = $this->validateDealerDocuments($validated, $documentService);

        return $validated;
    }

    private function validateDealerContacts($validated, $contactService, $locationService, $locationTypeService)
    {
        $dealer_contacts = array_map(function ($item) use ($contactService, $locationService, $locationTypeService) {
            return array_merge($item, [
                'contact_id' => $contactService->model::query()->findOrFailByGuid($item['contact_id'])->id,
                'locations' => $locationService->model::query()->whereIn('guid', $item['locations'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['dealer_contacts']);

        return $dealer_contacts;
    }

    private function validateDealerBrands($validated, $brandService, $modelService)
    {
        $dealer_brands = array_map(function ($item) use ($brandService, $modelService) {
            return array_merge($item, [
                'brand_id' => $brandService->model::query()->findOrFailByGuid($item['brand_id'])->id,
                'models' => $modelService->model::query()->whereIn('guid', $item['models'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['dealer_brands']);

        return $dealer_brands;
    }

    private function validateDealerAdditionalLocations($validated, $locationTypeService, $locationService)
    {
        $dealer_additional_locations = array_map(function ($item) use ($locationTypeService, $locationService) {
            return array_merge($item, [
                'location_type_id' => $locationTypeService->model::query()->findOrFailByGuid($item['location_type_id'])->id,
                'location_id' => $locationService->model::query()->findOrFailByGuid($item['location_id'])->id,
            ]);
        }, $validated['dealer_additional_locations']);

        return $dealer_additional_locations;
    }

    private function validateDealerDocuments($validated, $documentService)
    {
        $dealer_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['dealer_documents']);

        return $dealer_documents;
    }
}
