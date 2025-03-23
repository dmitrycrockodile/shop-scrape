<?php

namespace App\Http\Resources\ScrapedData;

use App\Http\Resources\Rating\RatingResource;
use App\Http\Resources\ScrapedDataImage\ScrapedDataImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapedDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="ScrapedDataResource",
     *     type="object",
     *     description="Scraped Data resource schema",
     *     @OA\Property(property="product-retailer id", type="integer", example=12, description="ID of the product-retailer relationship"),
     *     @OA\Property(property="scraping session id", type="integer", example=7, description="ID of the scraping session"),
     *     @OA\Property(property="title", type="string", example="Product Title", description="Title of the scraped product"),
     *     @OA\Property(property="description", type="string", example="A detailed product description.", description="Description of the scraped product"),
     *     @OA\Property(property="price", type="number", format="float", example=29.99, description="Price of the product"),
     *     @OA\Property(property="stock count", type="integer", example=150, description="Stock count of the product"),
     *     @OA\Property(property="average rating", type="number", format="float", example=4.8, description="Average rating of the product"),
     *     @OA\Property(
     *         property="images",
     *         type="array",
     *         description="List of images associated with the product",
     *         @OA\Items(ref="#/components/schemas/ScrapedDataImageResource")
     *     ),
     *     @OA\Property(
     *         property="rating",
     *         type="array",
     *         description="Ratings breakdown by stars",
     *         @OA\Items(ref="#/components/schemas/RatingResource")
     *     )
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'product-retailer id' => $this->product_retailer_id, 
            'scraping session id' => $this->scraping_session_id,
            'title' => $this->title, 
            'description' => $this->description, 
            'price' => $this->price, 
            'stock count' => $this->stock_count, 
            'average rating' => $this->avg_rating,
            'images' => ScrapedDataImageResource::collection($this->images),
            'rating' => RatingResource::collection($this->ratings)
        ];
    }
}