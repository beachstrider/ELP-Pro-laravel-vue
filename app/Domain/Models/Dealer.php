<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'is_active', 'author_id', 'main_location_id', 'dealer_id', 'name', 'first_name', 'last_name', 'phone', 'fax', 'email', 'comment', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function mainLocation()
    {
        return $this->belongsTo(Location::class, 'main_location_id', 'id');
    }

    public function dealerContacts()
    {
        return $this->hasMany(DealerContact::class, 'dealer_id', 'id');
    }

    public function dealerBrands()
    {
        return $this->hasMany(DealerBrand::class, 'dealer_id', 'id');
    }

    public function dealerAdditionalLocations()
    {
        return $this->hasMany(DealerAdditionalLocation::class, 'dealer_id', 'id');
    }

    public function dealerDocuments()
    {
        return $this->hasMany(DealerDocument::class, 'dealer_id', 'id');
    }
}
