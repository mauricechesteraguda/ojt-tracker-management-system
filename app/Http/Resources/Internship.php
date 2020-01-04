<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Internship extends JsonResource
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
            'user'       => $this->user,
            'company'    => $this->company,
            'descriptions'    => $this->descriptions,
            'start_date'    => $this->start_date,
            'representative'    => $this->representative,
            'student_position' => $this->student_position,
            'is_approved' => $this->is_approved,
            'status' => $this->status,
            'comment' => $this->comment,
            'updated_by' => $this->updated_by,
        ];
    }
}
