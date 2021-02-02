<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierLocation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }
}

