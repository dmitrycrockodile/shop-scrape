<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\PackSize\PackSizeResource;
use App\Http\Resources\ProductImage\ProductImageResource;
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'manufacturer_part_number' => $this->manufacturer_part_number,
            'pack_size' => new PackSizeResource($this->packSize),
            'images' => ProductImageResource::collection($this->images),
        ];
    }
}
