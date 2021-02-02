<?php

namespace App\Http\Controllers\Api;

use App\Domain\Models\SupplierType;
use App\Domain\Services\ContactService;
use App\Domain\Services\DocumentService;
use App\Domain\Services\LocationService;
use App\Domain\Services\LocationTypeService;
use App\Domain\Services\SupplierService;
use App\Domain\Services\LogisticTypeService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\SupplierResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends CrudController
{
    /**
     * @var SupplierService
     */
    public $service;

    public $resource = SupplierResource::class;

    public function __construct(SupplierService $service)
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
            'name' => ['required', 'max:191',new ValidateTag()],
            'is_active' => [],
            'password' => ((!$id) ? ['required', 'min:8', 'max:191'] : ["nullable", "min:8", "max:191"]),
            'main_location_id' => ['required', new ValidateTag()],
            'comment' => [new ValidateTag()],
            'phone' => ['required', 'max:191', new ValidateTag()],
            "email" => [
                'required',
                'email',
                "email:rfc,dns",
                new ValidateTag()
            ],
            'fax' => ['required', 'max:191', new ValidateTag()],
            "identification_number" => [
                'required',
                "alpha_dash",
                Rule::unique("suppliers", "identification_number")
                    ->ignore($id, "guid"),
            ],
            'supplier_user_types' => ['required', 'array'],
            'supplier_logistic_types' => array_merge(['array'], (in_array(1, $request->input('supplier_user_types', [])) ? ['required'] : []) ),
            'supplier_locations' => [],
            'supplier_locations.*.location_id' => ['required'],
            'supplier_locations.*.location_type_id' => ['required'],
            'supplier_contacts' => [],
            'supplier_contacts.*.contact_id' => ['required'],
            'supplier_contacts.*.locations' => ['required', 'array'],
            'supplier_documents' => [],
            'supplier_documents.*.document' => ['required', 'array'],
            'supplier_documents.*.title' => ['required', 'max:191'],
        ];

        $validated = $this->validate($request, $rules);

        $user_id = null;
        if (!$id) { // create
            $validated['author_id'] = auth('api')->id();
        } else {
            $user = $this->service->getDetailByGuid($id);
            $user_id = $user->user_id;
        }

        $rules["email"] = [
            'required',
            'email:rfc,dns',
            Rule::unique("users", "email")
                ->ignore($user_id, "id"),
            new ValidateTag()
        ];

        $validated = $this->validate($request, $rules);

        $locationService = app()->make(LocationService::class);
        $locationTypeService = app()->make(LocationTypeService::class);
        $contactService = app()->make(ContactService::class);
        $documentService = app()->make(DocumentService::class);
        $validated['main_location_id'] = $locationService->model::query()->findOrFailByGuid($validated['main_location_id'])->id;
        $validated['supplier_logistic_types'] = (app()->make(LogisticTypeService::class))->model::query()->whereIn('guid', $validated['supplier_logistic_types'])->get()->pluck('id')->toArray();
        $validated['supplier_user_types'] = SupplierType::query()->whereIn('id', $validated['supplier_user_types'])->get()->pluck('id')->toArray();
        $validated['supplier_contacts'] = $this->validateSupplierContacts($validated, $contactService, $locationService);
        $validated['supplier_locations'] = $this->validateSupplierLocations($validated, $locationService, $locationTypeService);
        $validated['supplier_documents'] = $this->validateSupplierDocuments($validated, $documentService);
        $validated['first_name'] = $validated['name'];
        return $validated;
    }

    private function validateSupplierContacts($validated, $contactService, $locationService)
    {
        $supplier_contacts = array_map(function ($item) use ($contactService, $locationService) {
            return array_merge($item, [
                'contact_id' => $contactService->model::query()->findOrFailByGuid($item['contact_id'])->id,
                'locations' => $locationService->model::query()->whereIn('guid', $item['locations'])->get()->pluck('id')->toArray(),
            ]);
        }, $validated['supplier_contacts']);

        return $supplier_contacts;
    }

    private function validateSupplierLocations($validated, $locationService, $locationTypeService)
    {
        $supplier_locations = array_map(function ($item) use ($locationService, $locationTypeService) {
            return array_merge($item, [
                'location_id' => $locationService->model::query()->findOrFailByGuid($item['location_id'])->id,
                'location_type_id' => $locationTypeService->model::query()->findOrFailByGuid($item['location_type_id'])->id,
            ]);
        }, $validated['supplier_locations']);

        return $supplier_locations;
    }

    private function validateSupplierDocuments($validated, $documentService)
    {
        $supplier_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['supplier_documents']);

        return $supplier_documents;
    }
}


