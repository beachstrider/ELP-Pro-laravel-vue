<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

class DocumentResource extends CrudResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->guid,
            'download_url' => url(Storage::url($this->path)),
            'system_url' => url('storage'),
            'name2' => $this->dummy_name
        ];
    }
}
