<?php

namespace App\Http\Resources;

use App\Domain\Services\BrandService;

class ModelResource extends CrudResource
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
            'title' => $this->title,
            'type' => $this->type,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'delivery_factors' => $this->delivery_factors,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'brand' => new BrandResource($this->whenLoaded('brand')),
        ];
    }
}
