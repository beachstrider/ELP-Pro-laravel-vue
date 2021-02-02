<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\SupplierService;
use App\Domain\Services\ContractService;
use App\Domain\Services\DocumentService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\ContractResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;

class ContractController extends CrudController
{
    /**
     * @var ContractService
     */
    public $service;

    public $supplierService;

    public $resource = ContractResource::class;

    public function __construct(ContractService $service, SupplierService $supplierService)
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
            'title' => ['required', 'max:191', new ValidateTag()],
            'duration' => ['required', new ValidateTag()],
            'description' => [new ValidateTag()],
            'start_date' => ['required', 'date', 'date_format:Y-m-d', new ValidateTag()],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after:start_date', new ValidateTag()],
            'contract_documents' => [],
            'contract_documents.*.document' => ['required', 'array'],
            'contract_documents.*.title' => ['required', 'max:191'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $validated = $this->validate($request, $rules);

        $documentService = app()->make(DocumentService::class);
        $validated['supplier_id'] = $this->supplierService->model::query()->findOrFailByGuid($validated['supplier_id'])->id;
        $validated['contract_documents'] = $this->validateContractDocuments($validated, $documentService);

        return $validated;
    }

    private function validateContractDocuments($validated, $documentService)
    {
        $contract_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['contract_documents']);

        return $contract_documents;
    }
}
