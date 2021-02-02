<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportVehicle extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'supplier_id', 'capacity', 'title', 'type', 'plate_number', 'year_of_production', 'brand', 'euro_norm', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

}
