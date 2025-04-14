<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\ScrapedData;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapingSession;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Tests\TestCase;

class ScrapedDataControllerTest extends TestCase
{
    public function test_store_scraped_data_successfully()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        $payload = [
            'mpn' => $product->manufacturer_part_number,
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'title' => $product->title,
            'description' => $product->description,
            'price' => 9.99,
            'stock_count' => 5,
            'avg_rating' => 4.2,
            'currency' => 'USD',
            'scraped_at' => now()->toISOString(),
            'ratings' => [
                'one_star' => 1,
                'two_stars' => 2,
                'three_stars' => 3,
                'four_stars' => 4,
                'five_stars' => 5,
            ]
        ];

        $response = $this->postJson('/api/scraped-data', $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'success' => true,
                'message' => __('messages.store.success', ['attribute' => 'scraped_data']),
            ])
            ->assertJsonStructure(['data' => [
                'product-retailer id', 
                'scraping session id', 
                'title', 
                'description', 
                'price', 
                'stock count',
                'average rating',
                'images',
                'rating'
            ]]);
    }

    public function test_store_scraped_data_fails_validation()
    {
        $response = $this->postJson('/api/scraped-data', []);

        $response->assertStatus(422);
    }

    public function test_export_csv_downloads_file_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $startDate = now()->subDays(2)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'retailer_ids' => [],
            'product_ids' => [],
        ];

        $response = $this->postJson('/api/scraped-data/export?' . http_build_query($params));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=scraped_data_{$startDate}_to_{$endDate}", $disposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_downloads_file_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $user->retailers()->attach($retailer);
        $user->products()->attach($product);
        
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'created_at' => now()->subDay(),
            'product_retailer_id' => $productRetailer->id,
        ]);

        $startDate = now()->subDays(2)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'retailer_ids' => [],
            'product_ids' => [],
        ];

        $response = $this->postJson('/api/scraped-data/export?' . http_build_query($params));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=scraped_data_{$startDate}_to_{$endDate}", $disposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_downloads_file_without_dates()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $params = [
            'retailer_ids' => [],
            'product_ids' => [],
        ];

        $response = $this->postJson('/api/scraped-data/export?' . http_build_query($params));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=scraped_data.csv", $disposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_downloads_file_with_start_date()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $startDate = now()->subDays(2)->format('Y-m-d');
        $params = [
            'startDate' => $startDate,
            'retailer_ids' => [],
            'product_ids' => [],
        ];

        $response = $this->postJson('/api/scraped-data/export?' . http_build_query($params));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=scraped_data.csv", $disposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_downloads_file_with_end_date()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $endDate = now();
        $params = [
            'endDate' => $endDate,
            'retailer_ids' => [],
            'product_ids' => [],
        ];

        $response = $this->postJson('/api/scraped-data/export?' . http_build_query($params));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=scraped_data.csv", $disposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }
}
