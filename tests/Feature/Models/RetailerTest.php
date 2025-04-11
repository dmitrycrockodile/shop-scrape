<?php

namespace Tests\Feature\Models;

use App\Models\Retailer;
use App\Models\Product;
use App\Models\User;
use App\Models\ScrapedData;
use App\Models\Currency;
use App\Models\PackSize;
use App\Models\ProductRetailer;
use Tests\TestCase;

class RetailerTest extends TestCase
{
    public function test_create_retailer_with_fillable_fields()
    {
        $currency = Currency::factory()->create();

        $retailer = Retailer::create([
            'title' => 'Retailer 1',
            'url' => 'https://example.com',
            'currency_id' => $currency->id,
            'logo' => 'logos/logo1.png',
        ]);

        $this->assertDatabaseHas('retailers', [
            'title' => 'Retailer 1',
            'url' => 'https://example.com',
            'currency_id' => $currency->id,
            'logo' => 'logos/logo1.png',
        ]);
    }

    public function test_retailer_has_products_when_related_products_exist()
    {
        $currency = Currency::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
    
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create(['pack_size_id' => $packSize->id]);
    
        $retailer->products()->attach($product->id, ['product_url' => 'https://product-url.com']);
        $retailer->load('products');
    
        $this->assertTrue($retailer->products->contains($product));
    }

    public function test_retailer_has_users_when_related_users_exist()
    {
        $currency = Currency::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $user = User::factory()->create();

        $retailer->users()->attach($user);

        $this->assertTrue($retailer->users->contains($user));
    }

    public function test_retailer_has_scraped_data_when_related_scraped_data_exists()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id,
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);
        

        $scrapedData = ScrapedData::factory()->create(['product_retailer_id' => $productRetailer->id]);

        $this->assertTrue($retailer->scrapedData->contains($scrapedData));
    }

    public function test_retailer_has_currency()
    {
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create(['currency_id' => $currency->id]);

        $this->assertEquals($currency->id, $retailer->currency->id);
    }
}