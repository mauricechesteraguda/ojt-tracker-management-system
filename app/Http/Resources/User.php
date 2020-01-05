<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
        'id'         => $this->id,
        'name'       => $this->name,
        'first_name'       => $this->first_name,
        'last_name'       => $this->last_name,
        'email'    => $this->email,
        'role'    => $this->role,
        'sr_code'    => $this->sr_code,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
}
