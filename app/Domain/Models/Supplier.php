<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'user_id', 'main_location_id', 'is_active', 'name', 'first_name', 'last_name', 'phone', 'email', 'fax', 'identification_number', 'comment', 'created_at', 'updated_at', 'deleted_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mainLocation()
    {
        return $this->belongsTo(Location::class, 'main_location_id', 'id');
    }

    public function supplierContacts()
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id', 'id');
    }

    public function supplierLocations()
    {
        return $this->hasMany(SupplierLocation::class, 'supplier_id', 'id');
    }

    public function supplierDocuments()
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_id', 'id');
    }

    public function supplierLogisticTypes()
    {
        return $this->belongsToMany(LogisticType::class, 'supplier_logistic_types');
    }

    public function supplierContracts()
    {
        return $this->hasMany(Contract::class, 'supplier_id', 'id');
    }

    public function supplierUserTypes()
    {
        return $this->belongsToMany(SupplierType::class, 'supplier_user_types');
    }
}
