<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'fax' => $this->fax,
            'identification_number' => $this->identification_number,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'main_location' => (new LocationResource($this->whenLoaded('mainLocation'))),
            'supplier_contacts' => SupplierContactResource::collection($this->whenLoaded('supplierContacts')),
            'supplier_contracts' => ContractResource::collection($this->whenLoaded('supplierContracts')),
            'supplier_locations' => SupplierLocationResource::collection($this->whenLoaded('supplierLocations')),
            'supplier_documents' => UserDocumentResource::collection($this->whenLoaded('supplierDocuments')),
            'supplier_logistic_types' => LogisticTypeResource::collection($this->whenLoaded('supplierLogisticTypes')),
            'supplier_user_types' => SupplierTypeResource::collection($this->whenLoaded('supplierUserTypes')),
        ];
    }
}
