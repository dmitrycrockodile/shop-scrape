<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackSize>
 */
class PackSizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $packSizes = [
            ['name' => 'Small', 'weight' => '250g', 'amount' => 50],
            ['name' => 'Medium', 'weight' => '500g', 'amount' => 30],
            ['name' => 'Large', 'weight' => '1kg', 'amount' => 20],
            ['name' => 'Extra Large', 'weight' => '2kg', 'amount' => 10],
        ];

        $packSize = $this->faker->unique()->randomElement($packSizes);

        return [
            'name' => $packSize['name'],
            'weight' => $packSize['weight'],
            'amount' => $packSize['amount']
        ];
    }
}
