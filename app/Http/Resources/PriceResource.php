<?php

namespace App\Http\Resources;

class PriceResource extends CrudResource
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
            'leading_factors' => $this->leading_factors,
            'lead_time_pickup' => $this->lead_time_pickup,
            'lead_time_transport' => $this->lead_time_transport,
            'full_loaded_price' => $this->full_loaded_price,
            'single_loaded_price' => $this->single_loaded_price,
            'created_at' => $this->created_at,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'route' => new RouteResource($this->whenLoaded('route')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'model' => new ModelResource($this->whenLoaded('model')),
            'price_documents' => UserDocumentResource::collection($this->whenLoaded('priceDocuments')),
            'logistic_type' => new LogisticTypeResource($this->whenLoaded('logisticType'))
        ];
    }
}
