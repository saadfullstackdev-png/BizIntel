<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorRotasResource extends JsonResource
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
            "id" => $this->id,
            "date" => $this->date ?? null,
            "day" => date('l', strtotime($this->date)),
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "start_off" => $this->start_off,
            "end_off" => $this->end_off,
            "start_timestamp" => $this->start_timestamp,
            "end_timestamp" => $this->end_timestamp,
            "active" => $this->active,
            "resource_has_rota_id" => $this->resource_has_rota_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "resource_id" => $this->resource_id
        ];
    }
}
