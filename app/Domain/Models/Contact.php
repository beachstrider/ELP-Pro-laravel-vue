<?php

namespace App\Domain\Models;

use App\Helpers\HasGuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasGuidTrait, SoftDeletes;

    protected $fillable = ['id', 'guid', 'author_id', 'user_id', 'name', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'functions', 'created_at', 'updated_at', 'deleted_at',];

    public function author()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function clientContacts()
    {
        return $this->hasMany(ClientContact::class, 'contact_id', 'id');
    }

    public function manufacturerContacts()
    {
        return $this->hasMany(ManufacturerContact::class, 'contact_id', 'id');
    }

    public function locations()
    {
        return $this->hasMany(DealerContactLocation::class, 'dealer_contact_id', 'id');
    }
}

