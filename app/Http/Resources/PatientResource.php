<?php

namespace App\Http\Resources;

use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         
        return  [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'agency' => AgencyResource::make($this->whenLoaded('agency')),
            'phone_number' => $this->phone,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'profile' => $this->profile->auth_user
        ];
    }
}
