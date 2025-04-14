<?php

namespace Tests\Unit\Services;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use App\Models\ScrapingSession;
use App\Models\User;
use App\Service\ScrapedDataService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScrapedDataServiceTest extends TestCase
{
    protected ScrapedDataService $scrapedDataService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scrapedDataService = new ScrapedDataService();
    }

    public function test_store_returns_error_if_product_retailer_not_found()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $data = [
            'mpn' => 'mpn-111',
            'product_retailer_id' => 333,
            'price' => 100,
            'title' => 'Test Scraped Data',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4,
            'images' => [
                ['url' => 'fake-image.jpg', 'name' => 'Image 1', 'position' => 1],
            ],
            'ratings' => [
                'one_star' => 1,
                'two_stars' => 2,
                'three_stars' => 3,
                'four_stars' => 4,
                'five_stars' => 5,
            ],
            'scraping_session_id' => $scrapingSession->id
        ];

        $response = $this->scrapedDataService->store($data);

        $this->assertFalse($response['success']);
        $this->assertEquals(404, $response['status']);
        $this->assertStringContainsString('Product Retailer', $response['error']);
    }

    public function test_store_saves_scraped_data_with_images_and_ratings()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $data = [
            'mpn' => 'mpn-111',
            'product_retailer_id' => $productRetailer->id,
            'price' => 100,
            'title' => 'Test Scraped Data',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4,
            'images' => [
                ['url' => 'fake-image.jpg', 'name' => 'Image 1', 'position' => 1],
            ],
            'ratings' => [
                'one_star' => 1,
                'two_stars' => 2,
                'three_stars' => 3,
                'four_stars' => 4,
                'five_stars' => 5,
            ],
            'scraping_session_id' => $scrapingSession->id
        ];

        $response = $this->scrapedDataService->store($data);

        $this->assertTrue($response['success']);
        $this->assertDatabaseCount('scraped_data', 1);
        $this->assertDatabaseCount('scraped_data_images', 1);
        $this->assertDatabaseHas('ratings', ['five_stars' => 5]);
    }

    public function test_get_filtered_scraped_data_returns_expected_results()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        $user->retailers()->attach($retailer);

        $scraped = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $filters = [
            'retailer_ids' => [$retailer->id],
            'product_ids' => [$product->id],
        ];

        $result = $this->scrapedDataService->getFilteredScrapedData(
            Carbon::now()->subDays(2),
            Carbon::now()->addDay(),
            $filters
        );

        $this->assertCount(1, $result);
        $this->assertEquals($scraped->id, $result->first()->id);
    }

    public function test_get_filtered_scraped_data_returns_expected_results_without_filters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        $user->retailers()->attach($retailer);

        $scraped = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $result = $this->scrapedDataService->getFilteredScrapedData(
            Carbon::now()->subDays(2),
            Carbon::now()->addDay(),
            []
        );

        $this->assertCount(1, $result);
        $this->assertEquals($scraped->id, $result->first()->id);
    }

    public function test_get_filtered_scraped_data_with_only_end_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        $user->retailers()->attach($retailer);

        $scraped = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now()->subDays(1),
        ]);

        $result = $this->scrapedDataService->getFilteredScrapedData(
            null,
            Carbon::today(),
            []
        );

        $this->assertCount(1, $result);
        $this->assertEquals($scraped->id, $result->first()->id);
    }
}
