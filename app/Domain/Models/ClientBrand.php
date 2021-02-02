<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientBrand extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function models()
    {
        return $this->belongsToMany(BrandModel::class, 'client_brand_models', 'client_brand_id', 'model_id');
    }

}
