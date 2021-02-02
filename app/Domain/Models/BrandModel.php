<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandModel extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $table = 'models';

    protected $fillable = ['id', 'guid', 'author_id', 'brand_id', 'is_active', 'type', 'width', 'title', 'delivery_factors', 'length', 'height', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function clientBrandModels()
    {
        return $this->belongsToMany(BrandModel::class, 'client_brand_models', 'model_id', 'model_id');
    }

    public function manufacturerBrandModels()
    {
        return $this->belongsToMany(BrandModel::class, 'manufacturer_brand_models', 'model_id', 'model_id');
    }

    public function manufacturerLocationBrandModels()
    {
        return $this->belongsToMany(BrandModel::class, 'manufacturer_location_brand_models', 'model_id', 'model_id');
    }
}
