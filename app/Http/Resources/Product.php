<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            // 'app_id'=> new Application($this->whenLoaded('app')),
            // 'user_id'=> new User($this->whenLoaded('user')),
            'name'=> $this->name,
            'status'=> $this->status,
            'product_status'=> $this->product_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
