<?php

namespace App\Http\Resources;

use Carbon\Carbon;

class UserResource extends CrudResource
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
            'status' => ($this->email_verified_at ? 1 : 0),
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'last_login_at' => $this->last_login_at,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'from_suspended_at' => $this->from_suspended_at,
            'to_suspended_at' => $this->to_suspended_at,
            'profile_pic' => new DocumentResource($this->profilePic),
            'is_suspended' => ($this->from_suspended_at && $this->to_suspended_at) ? (
                (now()->greaterThanOrEqualTo(Carbon::parse($this->from_suspended_at))) && (now()->lessThanOrEqualTo(Carbon::parse($this->to_suspended_at)))
            ) : false,
        ];
    }
}
