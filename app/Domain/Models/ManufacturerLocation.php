<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturerLocation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function supplierReleaseAgent()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function supplierTransportations()
    {
        return $this->belongsToMany(Supplier::class, 'manufacturer_location_suppliers', 'manufacturer_location_id', 'supplier_id');
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'manufacturer_location_brands', 'manufacturer_location_id', 'brand_id');
    }

    public function brandModels()
    {
        return $this->belongsToMany(BrandModel::class, 'manufacturer_location_brand_models', 'manufacturer_location_id', 'model_id');
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }
}

