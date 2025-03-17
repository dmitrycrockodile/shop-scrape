<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\PackSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'manufacturer_part_number' => $this->faker->unique()->bothify('???-#####'),
            'pack_size_id' => PackSize::inRandomOrder()->first()->id,
            'created_at' => now()->subDays(rand(0, 365)),
            'updated_at' => now()->subDays(rand(0, 365))
        ];
    }
}
