<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSubDuration extends JsonResource
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
            'subscripton_id'=> CustomerSub::collection($this->whenLoaded('subscripton_id')),
            'started_at'=> $this->started_at,
            'end_at'=> $this->end_at,
            'days_left'=> $this->days_left,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
