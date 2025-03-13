<?php

namespace Database\Factories;

use App\Models\ScrapedData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scraped_data_id' => ScrapedData::factory(), 
            'one_star' => $this->faker->numberBetween(0, 100), 
            'two_stars' => $this->faker->numberBetween(0, 100),  
            'three_stars' => $this->faker->numberBetween(0, 100), 
            'four_stars' => $this->faker->numberBetween(0, 100), 
            'five_stars' => $this->faker->numberBetween(0, 100),
            'created_at' => now()->subDays(rand(0, 365)),
            'updated_at' => now()->subDays(rand(0, 365))
        ];
    }
}
