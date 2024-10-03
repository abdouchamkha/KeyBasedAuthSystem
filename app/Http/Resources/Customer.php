<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Application;
use App\Http\Resources\User;

class Customer extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'app' => new Application($this->whenLoaded('app')),
            'owner' => new User($this->whenLoaded('owner')),
            'customer' => new User($this->whenLoaded('customer')),
            'customer_type' => $this->customer_type,
            'invite_status' => $this->invite_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
