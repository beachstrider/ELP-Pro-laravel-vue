<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealerContact extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'dealer_contact_locations');
    }
}