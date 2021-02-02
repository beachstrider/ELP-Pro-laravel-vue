<?php

namespace App\Http\Resources;

class LocationResource extends CrudResource
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
            'street' => $this->street,
            'street_no' => $this->street_no,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'code' => $this->code,
            'from_opening_hours' => $this->from_opening_hours,
            'to_opening_hours' => $this->to_opening_hours,
            'created_at' => $this->created_at,
        ];
    }
}
