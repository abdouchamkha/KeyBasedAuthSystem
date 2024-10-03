<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSub extends JsonResource
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
            'customer_id'=> Customer::collection($this->whenLoaded('customer_id')),
            'app_id'=> Application::collection($this->whenLoaded('app_id')),
            'product_id'=> Product::collection($this->whenLoaded('product_id')),
            'subscription_type'=> $this->subscription_type,
            'permissions'=> $this->permissions,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
