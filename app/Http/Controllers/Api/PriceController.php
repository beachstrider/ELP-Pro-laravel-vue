<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\BrandService;
use App\Domain\Services\ModelService;
use App\Domain\Services\PriceService;
use App\Domain\Services\RouteService;
use App\Domain\Services\DocumentService;
use App\Domain\Services\LogisticTypeService;
use App\Domain\Services\SupplierService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\PriceResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;

class PriceController extends CrudController
{
    /**
     * @var PriceService
     */
    public $service;

    public $resource = PriceResource::class;

    public function __construct(PriceService $service)
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
            'supplier_id' => ['required'],
            'route_id' => ['required'],
            'brand_id' => ['required'],
            'model_id' => ['required'],
            'logistic_type_id' => ['required'],
            'leading_factors' => ['required', 'numeric', new ValidateTag()],
            'lead_time_pickup' => ['required', 'numeric', new ValidateTag()],
            'lead_time_transport' => ['required', 'numeric', new ValidateTag()],
            'full_loaded_price' => ['required', 'numeric', new ValidateTag()],
            'single_loaded_price' => ['required', 'numeric', new ValidateTag()],
            'price_documents' => [],
            'price_documents.*.document' => ['required', 'array'],
            'price_documents.*.title' => ['required', 'max:191'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();

            if (!auth()->user()->hasRole('superadmin')) {
                $validated['user_id'] = auth()->id();
            }
        }

        $validated = $this->validate($request, $rules);

        $documentService = app()->make(DocumentService::class);
        $validated['supplier_id'] = app()->make(SupplierService::class)->model::query()->findOrFailByGuid($validated['supplier_id'])->id;
        $validated['route_id'] = app()->make(RouteService::class)->model::query()->findOrFailByGuid($validated['route_id'])->id;
        $validated['brand_id'] = app()->make(BrandService::class)->model::query()->findOrFailByGuid($validated['brand_id'])->id;
        $validated['model_id'] = app()->make(ModelService::class)->model::query()->findOrFailByGuid($validated['model_id'])->id;
        $validated['price_documents'] = $this->validatePriceDocuments($validated, $documentService);
        $validated['logistic_type_id'] = app()->make(LogisticTypeService::class)->model::query()->findOrFailByGuid($validated['logistic_type_id'])->id;
        return $validated;
    }

    private function validatePriceDocuments($validated, $documentService)
    {
        $price_documents = array_map(function ($item) use ($documentService) {
            return array_merge($item, [
                'document_id' => $documentService->model::query()->findOrFailByGuid($item['document']['id'])->id,
            ]);
        }, $validated['price_documents']);

        return $price_documents;
    }
}
