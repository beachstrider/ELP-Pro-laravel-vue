<?php

namespace App\Http\Resources;

class DealerBrandResource extends CrudResource
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
            'brand' => (new BrandResource($this->whenLoaded('brand'))),
            'models' => ModelResource::collection($this->whenLoaded('models')),
        ];
    }
}
