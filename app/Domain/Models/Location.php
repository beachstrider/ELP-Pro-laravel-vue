<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'user_id', 'location_type_id', 'street', 'street_no', 'zip', 'city', 'country', 'code', 'from_opening_hours', 'to_opening_hours', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class, 'location_type_id', 'id');
    }

    public function clientContactLocations()
    {
        return $this->belongsToMany(Location::class, 'client_contact_locations', 'location_id', 'location_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'main_location_id', 'id');
    }

    public function manufacturers()
    {
        return $this->hasMany(Manufacturer::class, 'main_location_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'main_location_id', 'id');
    }

    public function manufacturerLocations()
    {
        return $this->hasMany(ManufacturerLocation::class, 'location_id', 'id');
    }

    public function manufacturerContactLocations()
    {
        return $this->belongsToMany(Location::class, 'manufacturer_contact_locations', 'location_id', 'location_id');
    }
}
