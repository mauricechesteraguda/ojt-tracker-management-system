<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Company extends JsonResource
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
            'country'    => $this->country,
            'city'    => $this->city,
            'address'    => $this->address,
            'location_map' => $this->location_map,
            'updated_at' => $this->main_branch,
        ];
    }
}
