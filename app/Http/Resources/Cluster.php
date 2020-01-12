<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Cluster extends JsonResource
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
            'year'       => $this->year,
            'is_deleted'    => $this->is_deleted,
            'updated_by'    => $this->updated_by,
        
        ];
    }
}
