<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'main_location_id', 'is_active', 'name', 'first_name', 'last_name', 'phone', 'email', 'fax', 'comment', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function mainLocation()
    {
        return $this->belongsTo(Location::class, 'main_location_id', 'id');
    }

    public function manufacturerContacts()
    {
        return $this->hasMany(ManufacturerContact::class, 'manufacturer_id', 'id');
    }

    public function manufacturerBrands()
    {
        return $this->hasMany(ManufacturerBrand::class, 'manufacturer_id', 'id');
    }

    public function manufacturerLocations()
    {
        return $this->hasMany(ManufacturerLocation::class, 'manufacturer_id', 'id');
    }

    public function manufacturerDocuments()
    {
        return $this->hasMany(ManufacturerDocument::class, 'manufacturer_id', 'id');
    }
}
