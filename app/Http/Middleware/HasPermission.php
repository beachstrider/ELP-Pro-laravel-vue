<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException;

class HasPermission
{
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request    $request
     * @param \Closure   $next
     * @param int|string $permission
     *
     * @throws \jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $permissions = explode('|', $permission);
        if ($this->auth->check() && $this->auth->user()->hasOnePermission($permissions)) {
            return $next($request);
        }

        throw new PermissionDeniedException($permission);
    }

}
