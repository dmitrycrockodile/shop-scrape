<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use App\Models\ScrapingSession;
use Tests\TestCase;

class ScrapingSessionTest extends TestCase
{
    public function test_scraping_session_has_many_scraped_data()
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

        $scrapingSession = ScrapingSession::factory()->create([
            'retailer_id' => $retailer->id,
        ]);

        ScrapedData::factory(3)->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id
        ]);


        $this->assertCount(3, $scrapingSession->scrapedData);
        $this->assertTrue($scrapingSession->scrapedData->first() instanceof ScrapedData);
    }

    public function test_scraping_session_belongs_to_retailer()
    {
        $currency = Currency::factory()->create();
    
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $scrapingSession = ScrapingSession::factory()->create([
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($scrapingSession->retailer instanceof Retailer);
        $this->assertEquals($retailer->id, $scrapingSession->retailer->id);
    }
}