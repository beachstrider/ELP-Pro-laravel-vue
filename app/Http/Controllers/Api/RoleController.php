<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\RoleService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends CrudController
{
    /**
     * @var RoleService
     */
    public $service;

    public $resource = RoleResource::class;

    public function __construct(RoleService $roleService)
    {
        $this->service = $roleService;
    }

    /**
     * @param Request $request
     * @param null $id
     * @param boolean $id_required
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request, $id = null)
    : array
    {
        $rules = [
            "name" => [
                "required",
                "alpha_dash",
                Rule::unique("roles")
                    ->ignore($id, "id")
                    ->whereNull('deleted_at'),
            ],
            "permissions" => "",
        ];

        if (!$id) {

        }

        $validated = $this->validate($request, $rules);

        return $validated;
    }

    public function getPermissions()
    {
        return response()->json([
            'data' => array_map(function ($item) {
                return [
                    'label' => $item['label'],
                    'permissions' => $item['permissions'],
                ];

            }, config('permission_collections'))
        ]);
    }
}
