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
    /**
     * @OA\Schema(
     *     schema="ProductResource",
     *     type="object",
     *     description="Product resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the product"),
     *     @OA\Property(property="title", type="string", example="Laptop", description="Title of the product"),
     *     @OA\Property(property="description", type="string", example="High-performance laptop", description="Description of the product"),
     *     @OA\Property(property="manufacturer_part_number", type="string", example="LAP12345", description="Manufacturer part number"),
     *     @OA\Property(
     *         property="pack_size",
     *         ref="#/components/schemas/PackSizeResource",
     *         description="Associated pack size details"
     *     ),
     *     @OA\Property(
     *         property="images",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/ProductImageResource"),
     *         description="List of product images"
     *     )
     * )
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
