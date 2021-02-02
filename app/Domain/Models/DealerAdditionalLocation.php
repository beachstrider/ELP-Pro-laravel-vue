<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealerAdditionalLocation extends Model
{
    use SoftDeletes;

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }

    public function location()
    {
    	return $this->belongsTo(Location::class);
    }
}