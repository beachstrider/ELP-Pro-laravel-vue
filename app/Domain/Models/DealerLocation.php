<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class DealerLocation extends Model
{
	public $timestamps = false;
	
    protected $fillable = ['dealer_id', 'location_id'];  

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
}