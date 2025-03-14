<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScrapedData>
 */
class ScrapedDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_retailer_id' => ProductRetailer::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 1, 10000),
            'stock_count' => $this->faker->numberBetween(0, 500),
            'avg_rating' => $this->faker->randomFloat(1, 1, 5),
            'created_at' => now()->subDays(rand(0, 365)),
            'updated_at' => now()->subDays(rand(0, 365))
        ];
    }
}
