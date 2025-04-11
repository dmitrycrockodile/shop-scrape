<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Rating;
use App\Models\Retailer;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use App\Models\ScrapingSession;
use Tests\TestCase;

class ScrapedDataTest extends TestCase
{
    public function test_scraped_data_belongs_to_product_retailer()
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

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
        ]);

        $this->assertTrue($scrapedData->productRetailer instanceof ProductRetailer);
        $this->assertEquals($productRetailer->id, $scrapedData->productRetailer->id);
    }

    public function test_scraped_data_has_many_ratings()
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

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
        ]);

        Rating::factory()->count(3)->create([
            'scraped_data_id' => $scrapedData->id
        ]);

        $this->assertCount(3, $scrapedData->ratings);
        $this->assertTrue($scrapedData->ratings->first() instanceof Rating);
    }

    public function test_scraped_data_has_many_images()
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

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
        ]);

        ScrapedDataImage::factory()->count(3)->create([
            'scraped_data_id' => $scrapedData->id
        ]);

        $this->assertCount(3, $scrapedData->images);
        $this->assertTrue($scrapedData->images->first() instanceof ScrapedDataImage);
    }

    public function test_scraped_data_belongs_to_scraping_session()
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
        $scrapingSession = ScrapingSession::factory()->create([
            'retailer_id' => $retailer->id
        ]);

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id
        ]);

        $this->assertTrue($scrapedData->scrapingSession instanceof ScrapingSession);
        $this->assertEquals($scrapingSession->id, $scrapedData->scrapingSession->id);
    }
}