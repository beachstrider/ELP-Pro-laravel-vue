<?php

namespace App\Http\Resources;

class RoleResource extends CrudResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $permissions = (($this->permissions) ? $this->permissions->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'permission' => $item->slug,
            ];
        }) : []);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $permissions,
        ];
    }
}
