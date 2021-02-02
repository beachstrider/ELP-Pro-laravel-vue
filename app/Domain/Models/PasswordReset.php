<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = [
        'id', 'email', 'token', 'created_at', 'updated_at'
    ];
}
