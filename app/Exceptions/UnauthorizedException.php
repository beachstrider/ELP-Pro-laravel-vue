<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private $requiredRoles = [];

    private $requiredPermissions = [];

    public static function notLoggedIn()
    {
        return new static(403, 'User is not logged in.', null, []);
    }

    public static function forPermissions($permissions)
    {
        $message = 'User does not have the right permissions.';

        $exception = new static(403, $message, null, []);

        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    public function getRequiredRoles()
    {
        return $this->requiredRoles;
    }

    public function getRequiredPermissions()
    {
        return $this->requiredPermissions;
    }
}
