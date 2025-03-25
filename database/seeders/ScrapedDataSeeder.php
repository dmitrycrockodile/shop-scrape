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
    private const DATA_SCRAPE_DAYS = 365;
    private const SCRAPED_IMAGES_COUNT = 2;
    private const BATCH_SIZE = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productRetailers = ProductRetailer::all();
        $productRetailers->each(function ($productRetailer) {
            $this->seedScrapedData($productRetailer);
        });

        $this->updateProductRatings();
    }

    /**
     * Seed scraped data
     * 
     * @param ProductRetailer $productRetailer The retailer-product relationship 
     * for which scraped data will be generated.
     * 
     * @return void
     */
    private function seedScrapedData(ProductRetailer $productRetailer): void
    {
        $scrapedDataBatch = [];
        $scrapingSession = $this->getScrapingSession($productRetailer);

        for ($i = 0; $i < self::DATA_SCRAPE_DAYS; $i++) {
            $scrapedDataBatch[] = $this->createScrapedDataArray($productRetailer, $scrapingSession, $i);

            if (count($scrapedDataBatch) >= self::BATCH_SIZE) {
                ScrapedData::insert($scrapedDataBatch);
                $scrapedDataBatch = [];
            }
        }

        if (count($scrapedDataBatch) > 0) {
            ScrapedData::insert($scrapedDataBatch);
        }

        $scrapedDataIds = ScrapedData::where('product_retailer_id', $productRetailer->id)->pluck('id');
        $this->seedRatingsAndImages($scrapedDataIds);

        $scrapingSession->update(['status' => 'success']);
    }

    /**
     * Generate and batch-insert ratings and images for a list of scraped data records.
     *
     * @param array $scrapedDataIds A list of scraped data IDs for which ratings 
     * and images will be generated.
     * 
     * @return void
     */
    private function seedRatingsAndImages($scrapedDataIds): void
    {
        $ratingBatch = [];
        $imageBatch = [];

        foreach ($scrapedDataIds as $scrapedDataId) {
            $ratingBatch[] = $this->createRatingArray($scrapedDataId);

            for ($j = 0; $j < self::SCRAPED_IMAGES_COUNT; $j++) {
                $imageBatch[] = $this->createImageArray($scrapedDataId, $j);
            }

            if (count($ratingBatch) >= self::BATCH_SIZE) {
                Rating::insert($ratingBatch);
                $ratingBatch = [];
            }

            if (count($imageBatch) >= self::BATCH_SIZE) {
                ScrapedDataImage::insert($imageBatch);
                $imageBatch = [];
            }
        }

        if (count($ratingBatch) > 0) {
            Rating::insert($ratingBatch);
        }
        if (count($imageBatch) > 0) {
            ScrapedDataImage::insert($imageBatch);
        }
    }

    /**
     * Retrieve or create a scraping session for a given product retailer.
     *
     * @param ProductRetailer $productRetailer The ProductRetailer instance whose 
     * scraping session is being retrieved or created.
     * 
     * @return ScrapingSession The existing or newly created ScrapingSession instance.
     */
    private function getScrapingSession(ProductRetailer $productRetailer): ScrapingSession
    {
        return ScrapingSession::firstOrCreate([
            'retailer_id' => $productRetailer->retailer->id
        ], [
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Generate an array for a single scraped data record for insertion.
     *
     * @param ProductRetailer $productRetailer The ProductRetailer instance 
     * related to the scraped data.
     * @param ScrapingSession $scrapingSession The associated ScrapingSession instance.
     * @param int $daysAgo The number of days in the past 
     * for setting the created and updated timestamps.
     * 
     * @return array The formatted array ready for insertion into the scraped_data table.
     */
    private function createScrapedDataArray(ProductRetailer $productRetailer, ScrapingSession $scrapingSession, int $days): array
    {
        return [
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'title' => $productRetailer->product->title,
            'description' => $productRetailer->product->description,
            'price' => fake()->randomFloat(2, 1, 10000),
            'stock_count' => fake()->numberBetween(0, 500),
            'created_at'  => now()->subDays($days),
            'updated_at'  => now()->subDays($days),
        ];
    }

    /**
     * Generate an array representing a single image record for insertion.
     * 
     * @param int $scrapedDataId The ID of the scraped data record to which the image belongs.
     * @param int $position The position of the image relative to other images 
     * for the same scraped data record.
     * 
     * @return array The formatted array ready for insertion into the scraped_data_images table.
     */
    private function createImageArray(int $scrapedDataId, int $position): array
    {
        return [
            'scraped_data_id' => $scrapedDataId,
            'file_url' => fake()->imageUrl(400, 400, 'product'),
            'file_name' => fake()->sentence(5),
            'position' => $position + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate an array representing a single rating record for insertion.
     *
     * @param int $scrapedDataId The ID of the scraped data record to which 
     * the rating belongs.
     * 
     * @return array The formatted array ready for insertion into the ratings table.
     */
    private function createRatingArray(int $scrapedDataId): array
    {
        return [
            'scraped_data_id' => $scrapedDataId,
            'one_star'   => rand(0, 100),
            'two_stars'  => rand(0, 100),
            'three_stars' => rand(0, 100),
            'four_stars' => rand(0, 100),
            'five_stars' => rand(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Update the average rating for each scraped data record based on its associated ratings.
     * 
     * @return void
     */
    private function updateProductRatings(): void
    {
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
