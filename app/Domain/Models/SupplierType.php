<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierType extends Model
{
    protected $fillable = ['id', 'title', 'slug', 'created_at', 'updated_at'];
}
