<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    private const CURRENCY_COUNT = 9;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::factory(self::CURRENCY_COUNT)->create();
    }
}
