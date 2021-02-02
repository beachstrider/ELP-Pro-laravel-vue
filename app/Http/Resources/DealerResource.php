<?php

namespace App\Http\Resources;

class DealerResource extends CrudResource
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
            'dealer_id' => $this->dealer_id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'comment' => $this->comment,
            'is_active' => $this->is_active,
            'dealer_additional_locations' => DealerAdditionalLocationResource::collection($this->whenLoaded('dealerAdditionalLocations')),
            'dealer_contacts' => DealerContactResource::collection($this->whenLoaded('dealerContacts')),
            'dealer_brands' => DealerBrandResource::collection($this->whenLoaded('dealerBrands')),
            'dealer_documents' => UserDocumentResource::collection($this->whenLoaded('dealerDocuments')),
            'main_location' => (new LocationResource($this->whenLoaded('mainLocation'))),
            'created_at' => $this->created_at,
        ];
    }
}
