<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Rating;
use App\Models\Retailer;
use App\Models\ScrapedData;
use Tests\TestCase;

class RatingTest extends TestCase
{
    public function test_product_belongs_to_pack_size()
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

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id
        ]);
        
        $rating = Rating::factory()->create([
            'scraped_data_id' => $scrapedData->id
        ]);

        $this->assertTrue($rating->scrapedData instanceof ScrapedData);
        $this->assertEquals($scrapedData->id, $rating->scrapedData->id);
    }
}