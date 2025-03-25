<?php

namespace App\Http\Resources\ScrapedDataImage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapedDataImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="ScrapedDataImageResource",
     *     type="object",
     *     description="Image resource associated with scraped data",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the image entry"),
     *     @OA\Property(property="file_url", type="string", example="http://example.com/image1.jpg", description="URL of the image file"),
     *     @OA\Property(property="file_name", type="string", example="image1.jpg", description="Name of the image file"),
     *     @OA\Property(property="position", type="integer", example=1, description="Position of the image in the gallery")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name,
            'position' => $this->position
        ];
    }
}
