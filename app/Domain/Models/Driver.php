<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'supplier_id', 'user_id', 'is_active', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id', 'id');
    }
}
