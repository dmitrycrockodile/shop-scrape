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