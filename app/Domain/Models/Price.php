<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'route_id', 'brand_id', 'model_id', 'supplier_id', 'logistic_type_id', 'leading_factors', 'lead_time_pickup', 'lead_time_transport', 'full_loaded_price', 'single_loaded_price', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(BrandModel::class, 'model_id', 'id');
    }

    public function logisticType()
    {
        return $this->belongsTo(LogisticType::class, 'logistic_type_id', 'id');
    }

    public function priceDocuments()
    {
        return $this->hasMany(PriceDocument::class, 'price_id', 'id');
    }

}
