<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\AuthService;
use App\Domain\Services\UserService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Rules\ValidatePhoneNumber;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends CrudController
{
    /**
     * @var UserService
     */
    public $service;

    /**
     * @var AuthService
     */
    public $authService;

    public $resource = UserResource::class;

    public function __construct(UserService $userService)
    {
        $this->service = $userService;

        $this->authService = new AuthService;
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
            "first_name" => "required|max:191",
            "last_name" => "max:191",
            "phone" => "required|max:191",
            "role" => "",
            "status" => "",
            "profile" => "",
            "password" => "nullable|confirmed|min:8|max:255",
            "password_confirmation" => "nullable",
            "email" => [
                'required',
                "email:rfc,dns",
                Rule::unique("users", "email")
                    ->ignore($id, "guid"),
                new ValidateTag()
            ],
            "from_suspended_at" => "nullable|date|after_or_equal:today",
            "to_suspended_at" => "nullable|date|after:from_date",
        ];

        if (!$id) {
            $rules["password"] = "required|confirmed|min:8|max:255|same:password_confirmation";
            $rules["password_confirmation"] = "required";
            $rules["role"] = "required";
        }

        $validated = $this->validate($request, $rules);

        $validated['name'] = $validated['first_name'] .' '. $validated['last_name'];

        return $validated;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postProfileByGuid(Request $request)
    {
        $id = auth('api')->user()->guid;

        $inputs = $this->validate($request, [
            "profile_pic" => "",
            'first_name' => ['required', 'max:191', new ValidateTag()],
            'last_name' => ['max:191', new ValidateTag()],
            'phone' => ['required', 'max:191', new ValidateTag()],
            'password' => ['nullable', 'min:8', 'confirmed', new ValidateTag()],
            'email' => [
                'required', "email:rfc,dns", 'max:191',
                Rule::unique("users")
                    ->ignore($id, "guid"),
                new ValidateTag()
            ],
        ]);

        $inputs['name'] = $inputs['first_name'] .' '. $inputs['last_name'];
        $this->service->updateProfile($id, $inputs);
        $entity = $this->service->getDetailByGuid($id);
        $entity->load('profilePic');

        UserService::revokeToken($entity);
        $tokenResult = $entity->createToken($entity->email);
        $entity->tokenResult = $tokenResult;
        return new AuthResource($entity);
    }

    public function postDeleteByGuid(Request $request)
    {
        $inputs = $request->all();
        $inputs["creator_id"] = auth()->guard("api")->id();
        return response()->json(["success" => $this->service->deleteByGuid($request->input("id"), $inputs)]);
    }

    public function postSuspendUser(Request $request)
    {
        $validated = $this->validate($request, [
            "id" => "required",
            "from_suspended_at" => "required|date|after_or_equal:today",
            "to_suspended_at" => "required|date|after:from_date",
        ]);

        $validated["creator_id"] = auth()->guard("api")->id();

        return response()->json(["success" => $this->service->suspendUser($validated['id'], $validated)]);
    }


    public function postActivateUser(Request $request)
    {
        $validated = $this->validate($request, [
            "id" => "required",
        ]);

        $validated["creator_id"] = auth()->guard("api")->id();

        return response()->json(["success" => $this->service->activateUser($validated['id'], $validated)]);
    }

}
