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
            ['name' => 'Small', 'weight' => '250', 'weight_unit' => 'g', 'amount' => 50],
            ['name' => 'Medium', 'weight' => '500', 'weight_unit' => 'g', 'amount' => 30],
            ['name' => 'Large', 'weight' => '1', 'weight_unit' => 'kg', 'amount' => 20],
            ['name' => 'Extra Large', 'weight' => '2', 'weight_unit' => 'l', 'amount' => 10],
        ];

        $packSize = $this->faker->unique()->randomElement($packSizes);

        return [
            'name' => $packSize['name'],
            'weight' => $packSize['weight'],
            'amount' => $packSize['amount']
        ];
    }
}
