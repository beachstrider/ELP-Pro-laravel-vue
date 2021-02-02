<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\RouteService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\RouteResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends CrudController
{
    /**
     * @var RouteService
     */
    public $service;

    public $resource = RouteResource::class;

    public function __construct(RouteService $service)
    {
        $this->service = $service;
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
            'name' => ['required',
                            'max:191',
                            Rule::unique("routes")
                                ->ignore($id, "guid")
                                ->whereNull('deleted_at'),
                            new ValidateTag()],
            'from_location' => ['required', new ValidateTag()],
            'to_location' => ['required', new ValidateTag()],
            'description' => [new ValidateTag()],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();
        }

        $validated = $this->validate($request, $rules);

        return $validated;
    }
}
