<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RetailersSeeder;
use Database\Seeders\ProductsSeeder;
use Database\Seeders\ProductRetailerRelationshipSeeder;
use Database\Seeders\ScrapedDataSeeder;
use Database\Seeders\CurrenciesSeeder;
use Database\Seeders\PackSizesSeeder;
use Database\Seeders\UserProductRelationshipSeeder;
use Database\Seeders\UserPackSizeRelationshipSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperUserSeeder::class,
            CurrenciesSeeder::class,
            PackSizesSeeder::class,
            RetailersSeeder::class,
            ProductsSeeder::class,
            ProductRetailerRelationshipSeeder::class,
            UserProductRelationshipSeeder::class,
            UserPackSizeRelationshipSeeder::class,
            ScrapedDataSeeder::class
        ]);
    }
}
