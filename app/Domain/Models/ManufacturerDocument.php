<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturerDocument extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id')->where('object_type', 'manufacturer_document');
    }
}
