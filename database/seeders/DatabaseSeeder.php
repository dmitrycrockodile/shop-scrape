<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // /// Створення ритейлерів
        // $retailers = Retailer::factory(10)->create();

        // // Створення продуктів
        // $products = Product::factory(1000)->create();

        // foreach ($products as $product) {
        //     $product->retailers()->attach($retailers->random(rand(1, 5))->pluck('id'));
        // }

        // // Створення скрапінг-даних за 1 рік
        // foreach ($products as $product) {
        //     foreach ($product->retailers as $retailer) {
        //         for ($i = 0; $i < 365; $i++) {
        //             $scrapedData = ScrapedData::factory()->create([
        //                 'product_id' => $product->id,
        //                 'retailer_id' => $retailer->id,
        //                 'created_at' => Carbon::now()->subDays($i),
        //             ]);

        //             Rating::factory()->create(['scraped_data_id' => $scrapedData->id]);
        //         }
        //     }
        // }
    }
}
