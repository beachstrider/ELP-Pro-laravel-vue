<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'is_active' => $this->is_active,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'fax' => $this->fax,
            'identification_number' => $this->identification_number,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'dealers' => DealerResource::collection($this->whenLoaded('dealers')),
            'client_locations' => ClientLocationResource::collection($this->whenLoaded('clientLocations')),
            'client_contacts' => ClientContactResource::collection($this->whenLoaded('clientContacts')),
            'client_brands' => ClientBrandResource::collection($this->whenLoaded('clientBrands')),
            'client_documents' => UserDocumentResource::collection($this->whenLoaded('clientDocuments')),
            'main_location' => (new LocationResource($this->whenLoaded('mainLocation'))),
        ];

    }
}
