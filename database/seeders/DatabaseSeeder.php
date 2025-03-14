<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Rating;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $retailers = Retailer::factory(10)->create();
        $products = Product::factory(100)->create();

        foreach ($products as $product) {
            ProductImage::factory(2)->create([
                'product_id' => $product->id
            ]);

            $assignedRetailers = $retailers->random(rand(1, 2));
            foreach ($assignedRetailers as $assignedRetailer) {
            
                $product->retailers()->attach($assignedRetailer, [
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        $products = Product::with('retailers')->get();

        foreach ($products as $product) {
            foreach ($product->retailers as $retailer) {
                $scrapedDataBatch = [];
                $ratingBatch = [];
                $imageBatch = [];

                for ($i = 0; $i < 3; $i++) {  
                    $scrapedData = [
                        'product_id'  => $product->id,
                        'retailer_id' => $retailer->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => fake()->randomFloat(2, 1, 10000),
                        'stock_count' => fake()->numberBetween(0, 500),
                        'created_at'  => now()->subDays($i),
                        'updated_at'  => now()->subDays($i),
                    ];
                    $scrapedDataBatch[] = $scrapedData;
                }

                ScrapedData::insert($scrapedDataBatch);

                $scrapedDataIds = ScrapedData::where('product_id', $product->id)
                    ->where('retailer_id', $retailer->id)
                    ->pluck('id');

                foreach ($scrapedDataIds as $scrapedDataId) {
                    $ratingBatch[] = [
                        'scraped_data_id' => $scrapedDataId,
                        'one_star'   => rand(0, 100),
                        'two_stars'  => rand(0, 100),
                        'three_stars'=> rand(0, 100),
                        'four_stars' => rand(0, 100),
                        'five_stars' => rand(0, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    for ($j = 0; $j < 2; $j++) {
                        $imageBatch[] = [
                            'scraped_data_id' => $scrapedDataId,
                            'file_url' => fake()->imageUrl(400, 400, 'product'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                Rating::insert($ratingBatch);
                ScrapedDataImage::insert($imageBatch);
            }
        }

        $this->updateProductRatings();
    }

    private function updateProductRatings() {
        DB::statement("
            UPDATE scraped_data
            JOIN (
                SELECT r.scraped_data_id,
                    SUM(r.one_star) * 1 +
                    SUM(r.two_stars) * 2 +
                    SUM(r.three_stars) * 3 +
                    SUM(r.four_stars) * 4 +
                    SUM(r.five_stars) * 5 AS total_score,
                    SUM(r.one_star + r.two_stars + r.three_stars + r.four_stars + r.five_stars) AS total_votes
                FROM ratings r
                GROUP BY r.scraped_data_id
            ) rating_data ON scraped_data.id = rating_data.scraped_data_id
            SET scraped_data.avg_rating = COALESCE(ROUND(rating_data.total_score / NULLIF(rating_data.total_votes, 0), 2), 0);
        ");
    }
}