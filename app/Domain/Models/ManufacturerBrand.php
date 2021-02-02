<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturerBrand extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function models()
    {
        return $this->belongsToMany(BrandModel::class, 'manufacturer_brand_models', 'manufacturer_brand_id', 'model_id');
    }
}
