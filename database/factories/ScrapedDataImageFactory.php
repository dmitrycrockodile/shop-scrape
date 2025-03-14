<?php

namespace Database\Factories;

use App\Models\ScrapedData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScrapedDataImage>
 */
class ScrapedDataImageFactory extends Factory
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
            'file_url' => $this->faker->imageUrl(400, 400, 'product'),
            'created_at' => now()->subDays(rand(0, 365)),
            'updated_at' => now()->subDays(rand(0, 365))
        ];
    }
}
