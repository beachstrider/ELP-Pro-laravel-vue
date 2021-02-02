<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class DealerContactLocation extends Model
{
    public $timestamps = false;
	
    protected $fillable = ['id', 'dealer_id', 'dealer_contact_id', 'location_id'];    
}