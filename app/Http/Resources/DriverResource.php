<?php

namespace App\Http\Resources;

use App\Domain\Services\SupplierService;

class DriverResource extends CrudResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->guid,
            'is_active' => $this->is_active,
            'user' => new UserResource($this->whenLoaded('user')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'driver_documents' => UserDocumentResource::collection($this->whenLoaded('driverDocuments')),
            'created_at' => $this->created_at,
        ];
    }
}
