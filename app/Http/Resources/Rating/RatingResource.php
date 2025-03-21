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