<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_product_has_many_images()
    {
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        ProductImage::factory()->count(3)->create([
            'product_id' => $product->id
        ]);

        $this->assertCount(3, $product->images);
        $this->assertTrue($product->images->first() instanceof ProductImage);
    }

    public function test_product_has_many_scraped_data()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

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
        
        ScrapedData::factory()->count(2)->create([
            'product_retailer_id' => $productRetailer->id
        ]);

        $this->assertCount(2, $product->scrapedData);
        $this->assertTrue($product->scrapedData->first() instanceof ScrapedData);
    }

    public function test_product_belongs_to_many_retailers()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailers = Retailer::factory(2)->create([
            'currency_id' => $currency->id
        ]);

        foreach ($retailers as $retailer) {
            $product->retailers()->attach($retailer->id, ['product_url' => fake()->url()]);
        }

        $this->assertCount(2, $product->retailers);
        $this->assertTrue($product->retailers->first() instanceof Retailer);
        $this->assertNotNull($product->retailers->first()->pivot->product_url);
    }

    public function test_product_belongs_to_pack_size()
    {
        $packSize = PackSize::factory()->create();
        
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $this->assertTrue($product->packSize instanceof PackSize);
        $this->assertEquals($packSize->id, $product->packSize->id);
    }
}