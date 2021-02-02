<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->guid,
            'is_active' => $this->is_active,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'fax' => $this->fax,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'main_location' => (new LocationResource($this->whenLoaded('mainLocation'))),
            'manufacturer_contacts' => ManufacturerContactResource::collection($this->whenLoaded('manufacturerContacts')),
            'manufacturer_brands' => ManufacturerBrandResource::collection($this->whenLoaded('manufacturerBrands')),
            'manufacturer_locations' => ManufacturerLocationResource::collection($this->whenLoaded('manufacturerLocations')),
            'manufacturer_documents' => UserDocumentResource::collection($this->whenLoaded('manufacturerDocuments')),
        ];
    }
}

