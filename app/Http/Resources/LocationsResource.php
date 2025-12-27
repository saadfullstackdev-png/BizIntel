<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationsResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'fdoPhone' => $this->fdo_phone,
            'imageSrc' => $this->image_src,
            'address' => $this->address,
            'ntn' => $this->ntn,
            'stn' => $this->stn,
        ];
    }
}
