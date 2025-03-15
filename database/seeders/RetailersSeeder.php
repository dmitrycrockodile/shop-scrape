<?php

namespace Database\Seeders;

use App\Models\Retailer;
use Illuminate\Database\Seeder;

class RetailersSeeder extends Seeder
{
    private const RETAILERS_COUNT = 10;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Retailer::factory(self::RETAILERS_COUNT)->create();
    }
}
