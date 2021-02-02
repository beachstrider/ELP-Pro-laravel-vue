<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use jeremykenedy\LaravelRoles\Models\Role;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens, HasGuidTrait, HasRoleAndPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'guid', 'country_residence_id', 'billing_country_id', 'name', 'username', 'first_name', 'last_name', 'email', 'phone', 'password', 'remember_token', 'billing_city', 'billing_region', 'billing_zip', 'billing_address_line_1', 'billing_address_line_2', 'email_verified_at', 'last_login_at', 'from_suspended_at', 'to_suspended_at', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profilePic()
    {
        return $this->belongsTo(Document::class, 'id', 'object_id')->where('object_type', 'profile_pic');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
