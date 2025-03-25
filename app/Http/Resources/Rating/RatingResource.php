<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="RatingResource",
     *     type="object",
     *     description="Rating breakdown resource schema",
     *     @OA\Property(property="one star", type="integer", example=10, description="Number of one-star ratings"),
     *     @OA\Property(property="two stars", type="integer", example=20, description="Number of two-star ratings"),
     *     @OA\Property(property="three stars", type="integer", example=30, description="Number of three-star ratings"),
     *     @OA\Property(property="four stars", type="integer", example=40, description="Number of four-star ratings"),
     *     @OA\Property(property="five stars", type="integer", example=50, description="Number of five-star ratings")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'one star' => $this->one_star,
            'two stars'  => $this->two_stars,
            'three stars'  => $this->three_stars,
            'four stars'  => $this->four_stars,
            'five stars'  => $this->five_stars
        ];
    }
}
