<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'is_active', 'title', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function models()
    {
        return $this->hasMany(BrandModel::class, 'brand_id', 'id');
    }

    public function clientBrandModels()
    {
        return $this->belongsToMany(Brand::class, 'client_brand_models', 'brand_id', 'brand_id');
    }

    public function manufacturerBrands()
    {
        return $this->hasMany(ManufacturerBrand::class, 'brand_id', 'id');
    }

    public function manufacturerLocationBrands()
    {
        return $this->belongsToMany(Brand::class, 'manufacturer_location_brands', 'brand_id', 'brand_id');
    }
}
