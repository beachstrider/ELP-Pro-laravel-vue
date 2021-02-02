<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientLocation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function Location()
    {
        return $this->belongsTo(Location::class);
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }
}

