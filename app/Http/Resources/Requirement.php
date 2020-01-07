<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Requirement extends JsonResource
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
            'requirement_category'       => $this->requirement_category,
            'internship'    => $this->internship,
            'is_deleted'    => $this->is_deleted,
            'is_approved'    => $this->is_approved,
            'updated_by' => $this->updated_by,
        ];
    }
}
