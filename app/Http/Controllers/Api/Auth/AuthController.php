<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Services\AuthService;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public $service;

    public function __construct(AuthService $authService)
    {
        $this->service = $authService;
    }

    /**
     * @param Request $request
     * @return AuthResource|\Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function postSignIn(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required',
            'remember_me' => 'boolean'
        ]);

        try {
            $userWithToken = $this->service->signIn($request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'email' =>  $e->errors()['message']
                ]
            ], 422);
        }

        return new AuthResource($userWithToken);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * @param Request $request
     * @return AuthResource
     */
    public function getUser(Request $request)
    {
        $user = auth('api')->user();

        return new AuthResource($user);
    }

    /**
     * Create token password reset
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function postForgotPassword(Request $request)
    {
        $validated = $this->validate($request, [
            'email' => 'required|string|email',
        ]);

        try {
            $this->service->createResetPassword($validated);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'email' => [$e->getMessage()]
                ]
            ], 422);
        }

        return response()->json([
            'message' => __('password.sent')
        ]);
    }

    /**
     * Reset
     *
     * @param Request $request
     * @return AuthResource|\Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function postResetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'min:5', 'exists:users'],
            'password' => ['required', 'min:6', 'confirmed'],
            'token' => ['required', 'string'],
        ]);

        try {
            $this->service->resetPassword($request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'email' => [$e->getMessage()]
                ]
            ], 422);
        }

        return response()->json([
            'message' => __('password.reset')
        ]);
    }
}
