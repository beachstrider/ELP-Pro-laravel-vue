<?php

namespace App\Http\Resources;

class BrandResource extends CrudResource
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
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
