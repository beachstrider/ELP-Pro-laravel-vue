<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationType extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'is_active', 'title', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function clientLocations()
    {
        return $this->hasMany(ClientLocation::class, 'location_type_id', 'id');
    }

    public function supplierLocations()
    {
        return $this->hasMany(ClientLocation::class, 'location_type_id', 'id');
    }

    public function manufacturerLocations()
    {
        return $this->hasMany(ManufacturerLocation::class, 'location_type_id', 'id');
    }
}
