<?php

namespace Tests\Unit\Resources;

use App\Models\Rating;
use App\Models\PackSize;
use App\Models\Product;
use App\Http\Resources\Rating\RatingResource;
use App\Models\Currency;
use App\Models\Retailer;
use Tests\TestCase;

class RatingResourceTest extends TestCase
{
    public function test_rating_resource_returns_expected_structure(): void
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();

        Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $rating = Rating::factory()->create([
            'one_star' => 10,
            'two_stars' => 20,
            'three_stars' => 30,
            'four_stars' => 40,
            'five_stars' => 50,
        ]);

        $resource = (new RatingResource($rating))->toArray(request());
        
        $this->assertSame($rating->one_star, $resource['one star']);
        $this->assertSame($rating->two_stars, $resource['two stars']);
        $this->assertSame($rating->three_stars, $resource['three stars']);
        $this->assertSame($rating->four_stars, $resource['four stars']);
        $this->assertSame($rating->five_stars, $resource['five stars']);
    }
}