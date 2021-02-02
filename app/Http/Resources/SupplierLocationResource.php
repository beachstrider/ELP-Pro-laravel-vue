<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierLocationResource extends JsonResource
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
        ];
    }
}
