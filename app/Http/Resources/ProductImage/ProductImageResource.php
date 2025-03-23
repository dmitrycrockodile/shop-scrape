<?php

namespace App\Http\Resources\ProductImage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
    */
    /**
     * @OA\Schema(
     *     schema="ProductImageResource",
     *     type="object",
     *     description="Product image resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the product image"),
     *     @OA\Property(property="file_url", type="string", example="http://example.com/image.jpg", description="URL of the image file"),
     *     @OA\Property(property="file_name", type="string", example="image.jpg", description="Name of the image file")
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name
        ];
    }
}
