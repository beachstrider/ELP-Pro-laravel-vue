<?php

namespace App\Http\Controllers\Api;

use App\Domain\Models\Brand;
use App\Domain\Services\BrandService;
use App\Domain\Services\ModelService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\ModelResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModelController extends CrudController
{
    /**
     * @var ModelService
     */
    public $service;

    /**
     * @var BrandService
     */
    public $brandService;

    public $resource = ModelResource::class;

    public function __construct(ModelService $service, BrandService $brandService)
    {
        $this->service = $service;

        $this->brandService = $brandService;
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
            'title' => ['required',
                        Rule::unique("models")
                            ->ignore($id, "guid")
                            ->whereNull('deleted_at'),
                        new ValidateTag(), 'max:191'],
            'type' => ['required'],
            'width' => ['required', 'numeric', 'min:0'],
            'height' => ['required', 'numeric', 'min:0'],
            'delivery_factors' => ['required'],
            'length' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required'],
            'brand_id' => ['required'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $customMessages = [
            'title.required' => __('validation.required_model')
        ];

        $validated = $this->validate($request, $rules, $customMessages);
        $validated['brand_id'] = $this->brandService->model::query()->findOrFailByGuid($validated['brand_id'])->id;

        return $validated;
    }
}
