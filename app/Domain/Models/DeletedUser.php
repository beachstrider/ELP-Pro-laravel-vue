<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedUser extends Model
{
    protected $guarded = [];

    public static function customCreate($entity)
    {
        self::create([
            'user_id' => (isset($entity['id']) && !empty($entity['id']) ? $entity['id'] : null),
            'guid' => (isset($entity['guid']) && !empty($entity['guid']) ? $entity['guid'] : null),
            'name' => (isset($entity['name']) && !empty($entity['name']) ? $entity['name'] : null),
            'email' => (isset($entity['email']) && !empty($entity['email']) ? $entity['email'] : null),
            'phone' => (isset($entity['phone']) && !empty($entity['phone']) ? $entity['phone'] : null),
            'password' => (isset($entity['password']) && !empty($entity['password']) ? $entity['password'] : null),
            'remember_token' => (isset($entity['remember_token']) && !empty($entity['remember_token']) ? $entity['remember_token'] : null),
            'email_verified_at' => (isset($entity['email_verified_at']) && !empty($entity['email_verified_at']) ? $entity['email_verified_at'] : null),
            'last_login_at' => (isset($entity['last_login_at']) && !empty($entity['last_login_at']) ? $entity['last_login_at'] : null),
            'user_created_at' => (isset($entity['created_at']) && !empty($entity['created_at']) ? $entity['created_at'] : null),
            'user_updated_at' => (isset($entity['updated_at']) && !empty($entity['updated_at']) ? $entity['updated_at'] : null),
        ]);
    }
}
