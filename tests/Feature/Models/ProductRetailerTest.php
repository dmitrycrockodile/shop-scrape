<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use Tests\TestCase;

class ProductRetailerTest extends TestCase
{
    public function test_product_retailer_belongs_to_product()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($productRetailer->product instanceof Product);
        $this->assertEquals($product->id, $productRetailer->product->id);
    }

    public function test_product_retailer_belongs_to_retailer()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($productRetailer->retailer instanceof Retailer);
        $this->assertEquals($retailer->id, $productRetailer->retailer->id);
    }

    public function test_product_has_many_scraped_data()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);

        ScrapedData::factory(3)->create([
            'product_retailer_id' => $productRetailer->id
        ]);

        $this->assertCount(3, $productRetailer->scrapedData);
        $this->assertTrue($productRetailer->scrapedData->first() instanceof ScrapedData);
    }
}