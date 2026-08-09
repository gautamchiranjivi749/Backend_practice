<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,

            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
            ],

            'sku' => $this->sku,

            'color' => $this->color,

            'size' => $this->size,

            'price' => $this->price,

            'stock' => $this->stock,

            'status' => $this->status,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
