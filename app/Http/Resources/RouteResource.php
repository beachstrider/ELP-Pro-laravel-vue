<?php

namespace App\Http\Resources;

class RouteResource extends CrudResource
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
            'name' => $this->name,
            'from_location' => $this->from_location,
            'to_location' => $this->to_location,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
