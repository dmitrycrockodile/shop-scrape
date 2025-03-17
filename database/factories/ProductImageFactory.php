<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'file_url' => $this->faker->imageUrl(400, 400, 'product'),
            'file_name' => $this->faker->sentence(5),
            'created_at' => now()->subDays(rand(0, 365)),
            'updated_at' => now()->subDays(rand(0, 365))
        ];
    }
}
