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
}
