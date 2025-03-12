<?php

namespace Database\Seeders;

use App\Models\Image;
use Illuminate\Database\Seeder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\Rating;
use App\Models\ScrapedData;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $retailers = Retailer::factory(10)->create();
        $products = Product::factory(1000)->create();

        foreach ($products as $product) {
            Image::factory(2)->create([
                'imageable_id'   => $product->id,
                'imageable_type' => Product::class,
            ]);

            $assignedRetailers = $retailers->random(rand(1, 2));
            foreach ($assignedRetailers as $assignedRetailer) {
            
                $product->retailers()->attach($assignedRetailer);
            }
        }

        $products = Product::with('retailers')->get();

        foreach ($products as $product) {
            foreach ($product->retailers as $retailer) {
                for ($i = 0; $i < 365; $i++) {  
                    $scrapedData = ScrapedData::factory()->create([
                        'product_id'  => $product->id,
                        'retailer_id' => $retailer->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'created_at'  => now()->subDays($i),
                        'updated_at'  => now()->subDays($i),
                    ]);
                
                    Rating::factory()->for($scrapedData)->create();
                    Image::factory(2)->create([
                        'imageable_id'   => $scrapedData->id,
                        'imageable_type' => ScrapedData::class,
                    ]);
                }
            }
        }
    }
}