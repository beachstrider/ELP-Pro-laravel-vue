<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturerContactResource extends JsonResource
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
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
        ];
    }
}

