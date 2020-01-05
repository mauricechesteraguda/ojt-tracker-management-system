<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequirementCategory extends JsonResource
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
            'file'    => $this->file,
            'is_deleted'    => $this->is_deleted,
            // 'requirements'    => $this->requirements,
            'updated_by' => $this->updated_by,
        ];
    }
}
