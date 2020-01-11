<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Report extends JsonResource
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
            'internship'       => $this->internship,
            'description'    => $this->description,
            'date' => $this->date,
            'hours' => $this->hours,
            'is_valid' => $this->is_valid,
            'is_deleted' => $this->is_deleted,
            'comment' => $this->comment,
            'updated_by' => $this->updated_by,
        
        ];
    }
}
