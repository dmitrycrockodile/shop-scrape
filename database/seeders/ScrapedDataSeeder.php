<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use App\Models\ProductRetailer;
use App\Models\ScrapingSession;
use Illuminate\Support\Facades\DB;

class ScrapedDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productRetailers = ProductRetailer::all();

        foreach ($productRetailers as $productRetailer) {
            $scrapedDataBatch = [];
            $ratingBatch = [];
            $imageBatch = [];

            $scrapingSession = ScrapingSession::firstOrCreate([
                'retailer_id' => $productRetailer->retailer->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            for ($i = 0; $i < 3; $i++) {  
                $scrapedData = [
                    'product_retailer_id' => $productRetailer->id,
                    'scraping_session_id' => $scrapingSession->id,
                    'title' => $productRetailer->product->title,
                    'description' => $productRetailer->product->description,
                    'price' => fake()->randomFloat(2, 1, 10000),
                    'stock_count' => fake()->numberBetween(0, 500),
                    'created_at'  => now()->subDays($i),
                    'updated_at'  => now()->subDays($i),
                ];
                $scrapedDataBatch[] = $scrapedData;
            }

            ScrapedData::insert($scrapedDataBatch);

            $scrapedDataIds = ScrapedData::where('product_retailer_id', $productRetailer->id)
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
