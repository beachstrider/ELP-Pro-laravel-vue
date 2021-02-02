<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\DriverService;
use App\Domain\Services\SupplierService;
use App\Domain\Services\DocumentService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\DriverResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends CrudController
{
    /**
     * @var DriverService
     */
    public $service;

    public $supplierService;

    public $resource = DriverResource::class;

    public function __construct(DriverService $service, SupplierService $supplierService)
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
            "supplier_id" => ["required"],
            "name" => ["required", "max:191", new ValidateTag()],
            "phone" => ["required", "max:191", new ValidateTag()],
            "is_active" => ["required"],
            "password" => [],
            'driver_documents' => [],
            'driver_documents.*.document' => ['required', 'array'],
            'driver_documents.*.title' => ['required', 'max:191'],
        ];

        $user_id = null;
        if (!$id) { // create
            $validated["author_id"] = auth('api')->id();
            $validated["password"] = "required|min:8|max:255";
        } else {
            $entity = $this->service->model::query()->findOrFailByGuid($id);
            $user_id = $entity->user_id;
        }

        $rules["email"] = [
            'email:rfc,dns',
            Rule::unique("users", "email")
                ->ignore($user_id, "id"),
            new ValidateTag()
        ];

        $validated = $this->validate($request, $rules);

        $documentService = app()->make(DocumentService::class);
        $validated['first_name'] = $validated['name'];
        $validated["supplier_id"] = $this->supplierService->model::query()->findOrFailByGuid($validated["supplier_id"])->id;
        $validated['driver_documents'] = $this->validateDriverDocuments($validated, $documentService);
        $validated["is_active"] = (int)($validated["is_active"] == 'true');

        return $validated;
    }

    private function validateDriverDocuments($validated, $documentService)
    {
        $driver_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['driver_documents']);

        return $driver_documents;
    }
}
