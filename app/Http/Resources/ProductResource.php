<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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

        'category' => [
            'id' => $this->category?->id,
            'name' => $this->category?->name,
        ],

        'name' => $this->name,

        'slug' => $this->slug,

        'description' => $this->description,

        'price' => $this->price,

        'stock' => $this->stock,

        'status' => $this->status,

        'image' => $this->image
            ? asset('storage/'.$this->image)
            : null,
    ];
    }
}
