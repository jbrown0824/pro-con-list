<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
			'guest' => $this->guest,
			'color' => $this->color,
			'profile_photo_url' => $this->profile_photo_url,
		];
    }
}
