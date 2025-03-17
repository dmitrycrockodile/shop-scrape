<?php

namespace Database\Seeders;

use App\Models\PackSize;
use Illuminate\Database\Seeder;

class PackSizesSeeder extends Seeder
{
    private const PACK_SIZES_COUNT = 4;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PackSize::factory(self::PACK_SIZES_COUNT)->create();
    }
}
