<?php

namespace App\Domain\Services;

use App\Domain\Models\PasswordReset;
use App\Domain\Models\User;
use App\Notifications\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;

class AuthService
{
    public function signIn(array $inputs)
    {
        $user = User::query()
            ->with(['profilePic'])
            ->whereNotNull('email_verified_at')
            ->where('email', $inputs['email'])
            ->first();

        if ($user && Hash::check($inputs['password'], $user->password)) {
            UserService::revokeToken($user);
            
            if ($user->to_suspended_at && $user->to_suspended_at > Carbon::now()) {
                throw ValidationException::withMessages(['message' => 'Invalid Credentials.']);
            }

            if (array_key_exists('remember_me', $inputs) && $inputs['remember_me']) {
                Passport::refreshTokensExpireIn(now()->addDay(30));
            }

            $user->update([
                'last_login_at' => now()
            ]);

            $tokenResult = $user->createToken($user->email);

            $user->tokenResult = $tokenResult;

            return $user;
        }

        throw ValidationException::withMessages(['message' =>  __('auth.suspend')]);
    }

    public function createResetPassword(array $inputs)
    {
        $user = User::where('email', $inputs['email'])->first();

        if (!$user)
            throw ValidationException::withMessages(['message' =>  __('auth.failed')]);

        $passwordReset = PasswordReset::query()->where('email', $user->email)->first();

        if (!$passwordReset) {
            $passwordReset = new PasswordReset();
            $passwordReset->email = $user->email;
        }

        $passwordReset->token = Str::random(60);
        $passwordReset->created_at = now();
        $passwordReset->save();

        $user->notify(
            new PasswordResetRequest($passwordReset->token)
        );

        return $user;
    }

    public function resetPassword(array $inputs)
    {
        $passwordReset = PasswordReset::where([
            'token' => $inputs['token'],
            'email' => $inputs['email']
        ])->first();

        if (!$passwordReset)
            throw ValidationException::withMessages(['message' => __('auth.failed')]);

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            throw ValidationException::withMessages(['message' => __('passwords.token')]);
        }

        $user = User::query()->where('email', $passwordReset->email)->first();

        if (!$user)
            throw ValidationException::withMessages(['message' => __('auth.failed')]);

        $user->password = bcrypt($inputs['password']);
        $user->save();

        PasswordReset::where([
            'token' => $inputs['token'],
            'email' => $inputs['email']
        ])->delete();

        return $user;
    }
}
