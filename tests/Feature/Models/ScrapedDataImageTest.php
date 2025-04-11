<?php

namespace Tests\Feature\Models;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use Tests\TestCase;

class ScrapedDataImageTest extends TestCase
{
    public function test_scraped_data_image_belongs_to_scraped_data()
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

        $scrapedDataImage = ScrapedDataImage::factory()->create([
            'scraped_data_id' => $scrapedData->id
        ]);

        $this->assertTrue($scrapedDataImage->scrapedData instanceof ScrapedData);
        $this->assertEquals($scrapedData->id, $scrapedDataImage->scrapedData->id);
    }
}