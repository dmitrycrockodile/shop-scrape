<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RetailersSeeder;
use Database\Seeders\ProductsSeeder;
use Database\Seeders\ProductRetailerRelationshipSeeder;
use Database\Seeders\ScrapedDataSeeder;
use Database\Seeders\CurrenciesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrenciesSeeder::class,
            RetailersSeeder::class,
            ProductsSeeder::class,
            ProductRetailerRelationshipSeeder::class,
            ScrapedDataSeeder::class
        ]);
    }
}