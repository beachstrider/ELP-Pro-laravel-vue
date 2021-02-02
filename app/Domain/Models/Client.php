<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'main_location_id', 'is_active', 'company_name', 'phone', 'email', 'fax', 'identification_number', 'comment', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function mainLocation()
    {
        return $this->belongsTo(Location::class, 'main_location_id', 'id');
    }

    public function dealers()
    {
        return $this->belongsToMany(Dealer::class, 'client_dealers', 'client_id', 'dealer_id');
    }

    public function clientContacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id', 'id');
    }

    public function clientBrands()
    {
        return $this->hasMany(ClientBrand::class, 'client_id', 'id');
    }

    public function clientLocations()
    {
        return $this->hasMany(ClientLocation::class, 'client_id', 'id');
    }

    public function clientDocuments()
    {
        return $this->hasMany(ClientDocument::class, 'client_id', 'id');
    }

}
