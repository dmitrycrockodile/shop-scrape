<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Retailer;
use App\Models\Currency;
use App\Models\PackSize;
use App\Service\RetailerService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RetailerServiceTest extends TestCase
{
    protected RetailerService $retailerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->retailerService = new RetailerService();
    }

    public function test_store_creates_retailer()
    {
        $currency = Currency::factory()->create();

        $data = [
            'title' => 'New Retailer',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'url' => 'http://retailer-url.com',
            'currency_id' => $currency->id,
        ];

        $response = $this->retailerService->store($data);

        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('retailers', [
            'title' => 'New Retailer',
        ]);
    }

    public function test_update_updates_retailer()
    {
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'title' => 'Old Retailer',
            'currency_id' => $currency->id,
        ]);
        $newLogo = UploadedFile::fake()->image('new_logo.png');

        $data = ['title' => 'Updated Retailer', 'logo' => $newLogo];

        $response = $this->retailerService->update($data, $retailer);

        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('retailers', ['title' => 'Updated Retailer']);
    }

    public function test_sync_or_attach_products_attaches_and_syncs_correctly()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id,
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $retailer->products()->attach($product1->id, [
            'product_url' => 'http://old-url.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $products = [
            ['id' => $product1->id, 'url' => 'http://updated-url.com', 'pack_size_id' => $packSize->id],
            ['id' => $product2->id, 'url' => 'http://new-url.com', 'pack_size_id' => $packSize->id],
        ];

        $response = $this->retailerService->syncOrAttachProducts($retailer, $products);

        $this->assertTrue($response['success']);

        $this->assertDatabaseHas('product_retailers', [
            'retailer_id' => $retailer->id,
            'product_id' => $product1->id,
            'product_url' => 'http://updated-url.com',
        ]);

        $this->assertDatabaseHas('product_retailers', [
            'retailer_id' => $retailer->id,
            'product_id' => $product2->id,
            'product_url' => 'http://new-url.com',
        ]);
    }
}