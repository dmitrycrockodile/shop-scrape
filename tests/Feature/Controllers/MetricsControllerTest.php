<?php

namespace Tests\Feature\Controllers;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\ScrapedData;
use App\Models\ProductRetailer;
use App\Models\ScrapingSession;
use App\Models\User;
use App\Models\UserRetailer;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MetricsControllerTest extends TestCase
{
    public function test_get_retailer_metrics_returns_paginated_data_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $payload = [
            'product_ids' => [$product->id],
            'manufacturer_part_numbers' => [],
            'retailers' => [$retailer->id],
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
            'dataPerPage' => 10,
            'page' => 1
        ];

        $response = $this->postJson('/api/retailers/metrics', $payload);
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'last_page',
                    'total',
                    'filters'
                ]
            ]);
        $responseData = $response->json()['data'];

        $this->assertCount(1, $responseData);
        $this->assertEquals(100, $responseData[0]['avg_price']);
        $this->assertEquals(4.5, $responseData[0]['avg_rating']);
    }

    public function test_get_retailer_metrics_returns_paginated_data_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        UserRetailer::factory()->create([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $payload = [
            'product_ids' => [$product->id],
            'manufacturer_part_numbers' => [],
            'retailer_ids' => [$retailer->id],
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
            'dataPerPage' => 10,
            'page' => 1
        ];

        $response = $this->postJson('/api/retailers/metrics', $payload);
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'last_page',
                    'total',
                    'filters'
                ]
            ]);
        $responseData = $response->json()['data'];

        $this->assertCount(1, $responseData);
        $this->assertEquals(100, $responseData[0]['avg_price']);
        $this->assertEquals(4.5, $responseData[0]['avg_rating']);
    }

    public function test_exports_retailer_metrics_for_super_user() 
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $payload = [
            'start_date' => '2025-04-01',
            'end_date'   => $today,
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("filename=metrics_2025-04-01_to_{$today}.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_exports_retailer_metrics_with_start_date() 
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $payload = [
            'start_date' => '2025-04-01',
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("filename=metrics.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_exports_retailer_metrics_with_end_date() 
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $payload = [
            'end_date' => $today,
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("filename=metrics.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_exports_retailer_metrics_without_filters_for_super_user() 
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $payload = [
            'startDate' => null,
            'endDate'   => null,
            'retailers' => []
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("filename=metrics.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_exports_retailer_metrics_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        UserRetailer::factory()->create([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id,
        ]);

        $scrapingSession = ScrapingSession::factory()->create();

        ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'title' => 'Test title',
            'description' => 'Test descr',
            'stock_count' => 10,
            'avg_rating' => 4.5,
            'price' => 100,
            'scraping_session_id' => $scrapingSession->id,
            'created_at' => now(),
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $payload = [
            'start_date' => '2025-04-01',
            'end_date'   => $today,
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("filename=metrics_2025-04-01_to_{$today}.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_returns_error_when_unauthorized()
    {
        $payload = [
            'startDate' => '2025-04-01',
            'endDate'   => '2025-04-10',
            'retailers' => []
        ];

        $response = $this->postJson('/api/retailers/metrics/export', $payload);

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_it_returns_avg_rating_for_last_week_for_accessible_retailers_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailers = [$retailer1, $retailer2];
        
        $user->retailers()->attach($retailers);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);

        foreach ($retailers as $retailer) {
            $productRetailer = ProductRetailer::factory()->create([
                'product_id' => $product->id,
                'retailer_id' => $retailer->id,
            ]);

            foreach (range(0, 6) as $dayOffset) {
                ScrapedData::factory()->create([
                    'product_retailer_id' => $productRetailer->id,
                    'avg_rating' => rand(1, 5),
                    'created_at' => Carbon::now()->subDays($dayOffset),
                ]);
            }
        }

        $response = $this->getJson('api/retailers/weekly-ratings');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'retailer_id',
                        'retailer_title',
                        'avg_ratings' => [
                            '*' => ['date', 'avg_rating']
                        ]
                    ]
                ],
                'message',
                'success',
            ]);
    }

    public function test_it_returns_avg_rating_for_last_week_for_accessible_retailers_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailers = [$retailer1, $retailer2];

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);

        foreach ($retailers as $retailer) {
            $productRetailer = ProductRetailer::factory()->create([
                'product_id' => $product->id,
                'retailer_id' => $retailer->id,
            ]);

            foreach (range(0, 6) as $dayOffset) {
                ScrapedData::factory()->create([
                    'product_retailer_id' => $productRetailer->id,
                    'avg_rating' => rand(1, 5),
                    'created_at' => Carbon::now()->subDays($dayOffset),
                ]);
            }
        }

        $response = $this->getJson('api/retailers/weekly-ratings');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'retailer_id',
                        'retailer_title',
                        'avg_ratings' => [
                            '*' => ['date', 'avg_rating']
                        ]
                    ]
                ],
                'message',
                'success',
            ]);
    }

    public function test_it_returns_avg_price_for_last_week_for_accessible_retailers_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailers = [$retailer1, $retailer2];
        
        $user->retailers()->attach($retailers);

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);

        foreach ($retailers as $retailer) {
            $productRetailer = ProductRetailer::factory()->create([
                'product_id' => $product->id,
                'retailer_id' => $retailer->id,
            ]);
            
            foreach (range(0, 6) as $dayOffset) {
                ScrapedData::factory()->create([
                    'product_retailer_id' => $productRetailer->id,
                    'price' => rand(100, 500) / 10,
                    'created_at' => Carbon::now()->subDays($dayOffset),
                ]);
            }
        }
        
        $response = $this->getJson('api/retailers/weekly-pricing');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'retailer_id',
                        'retailer_title',
                        'avg_prices' => [
                            '*' => ['date', 'avg_price']
                        ]
                    ]
                ],
                'message',
                'success',
            ]);
    }

    public function test_it_returns_avg_price_for_last_week_for_accessible_retailers_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailers = [$retailer1, $retailer2];

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'manufacturer_part_number' => 'mpn-111'
        ]);

        foreach ($retailers as $retailer) {
            $productRetailer = ProductRetailer::factory()->create([
                'product_id' => $product->id,
                'retailer_id' => $retailer->id,
            ]);
            
            foreach (range(0, 6) as $dayOffset) {
                ScrapedData::factory()->create([
                    'product_retailer_id' => $productRetailer->id,
                    'price' => rand(100, 500) / 10,
                    'created_at' => Carbon::now()->subDays($dayOffset),
                ]);
            }
        }
        
        $response = $this->getJson('api/retailers/weekly-pricing');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'retailer_id',
                        'retailer_title',
                        'avg_prices' => [
                            '*' => ['date', 'avg_price']
                        ]
                    ]
                ],
                'message',
                'success',
            ]);
    }
}
