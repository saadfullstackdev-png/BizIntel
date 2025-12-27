<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'cnic' => $this->cnic,
            'dob' => $this->dob,
            'address' => $this->address,
            'phone' => $this->phone,
            'image_src' => '/patient_image/'.$this->image_src,
            'gender' => $this->user_gender,
            'lead_source_id' => $this->lead_source_id
        ];
    }
}
