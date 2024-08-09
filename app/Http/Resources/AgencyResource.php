<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'name' => $this->name,
            'agency_id' => $this->uuid,
            'profile_name' => $this->profile->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
