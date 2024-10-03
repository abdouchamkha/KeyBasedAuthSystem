<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseSession extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'license_id'=> License::collection($this->whenLoaded('license_id')),
            'uuid_value'=> $this->uuid_value,
            'ip'=> $this->ip,
            // 'duration'=> $this->duration,
            // 'type'=> $this->type,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at,
        ];
    }
}
