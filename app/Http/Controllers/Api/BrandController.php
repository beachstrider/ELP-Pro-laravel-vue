<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\BrandService;
use App\Domain\Services\ModelService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\BrandResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends CrudController
{
    /**
     * @var BrandService
     */
    public $service;

    public $modelService;

    public $resource = BrandResource::class;

    public function __construct(BrandService $service,ModelService $modelService)
    {
        $this->service = $service;

        $this->modelService = $modelService;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request, $id = null) : array
    {
        $rules = [
            'title' => ['required', 'max:191',
                            Rule::unique("brands")
                            ->ignore($id, "guid")
                            ->whereNull('deleted_at'),
                            new ValidateTag()],
            'is_active' => ['required'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $validated = $this->validate($request, $rules);

        return $validated;
    }
}
