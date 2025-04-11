<?php

namespace Database\Factories;

use App\Models\Retailer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScrapingSession>
 */
class ScrapingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'retailer_id' => Retailer::factory(),
            'status' => 'Success',
        ];
    }
}
