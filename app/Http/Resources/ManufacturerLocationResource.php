<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturerLocationResource extends JsonResource
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
            'id' => $this->id,
            'token' => $this->id,
            'location_type' => (new LocationTypeResource($this->whenLoaded('locationType'))),
            'location' => (new LocationResource($this->whenLoaded('location'))),
            'supplier' => (new SupplierResource($this->whenLoaded('supplierReleaseAgent'))),
            'suppliers' => SupplierResource::collection($this->whenLoaded('supplierTransportations')),
            'brands' => BrandResource::collection($this->whenLoaded('brands')),
            'models' => ModelResource::collection($this->whenLoaded('brandModels')),
        ];
    }
}

