<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransportVehicleResource extends JsonResource
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
            'capacity' => $this->capacity,
            'title' => $this->title,
            'brand' => $this->brand,
            'year_of_production' => $this->year_of_production,
            'euro_norm' => $this->euro_norm,
            'type' => $this->type,
            'plate_number' => $this->plate_number,
            'created_at' => $this->created_at,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
        ];
    }
}
