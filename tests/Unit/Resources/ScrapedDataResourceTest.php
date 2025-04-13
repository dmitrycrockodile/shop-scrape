<?php

namespace Tests\Unit\Resources;

use App\Models\Currency;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use App\Models\ScrapingSession;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Rating;
use App\Models\Retailer;
use App\Http\Resources\ScrapedData\ScrapedDataResource;
use Tests\TestCase;

class ScrapedDataResourceTest extends TestCase
{
    public function test_scraped_data_resource_returns_expected_structure(): void
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        $scrapingSession = ScrapingSession::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id
        ]);

        $scrapedData = ScrapedData::factory()->create([
            'product_retailer_id' => $productRetailer->id,
            'scraping_session_id' => $scrapingSession->id,
            'title' => 'Product Title',
            'description' => 'A detailed product description.',
            'price' => 29.99,
            'stock_count' => 150,
            'avg_rating' => 4.8,
        ]);

        $image1 = ScrapedDataImage::factory()->create(['scraped_data_id' => $scrapedData->id]);
        $image2 = ScrapedDataImage::factory()->create(['scraped_data_id' => $scrapedData->id]);

        $rating = Rating::factory()->create(['scraped_data_id' => $scrapedData->id]);

        $scrapedData->load(['images', 'ratings']);

        $resource = (new ScrapedDataResource($scrapedData))->toArray(request());

        $this->assertSame(1, $resource['product-retailer id']);
        $this->assertSame(1, $resource['scraping session id']);
        $this->assertSame('Product Title', $resource['title']);
        $this->assertSame('A detailed product description.', $resource['description']);
        $this->assertSame(29.99, $resource['price']);
        $this->assertSame(150, $resource['stock count']);
        $this->assertSame(4.8, $resource['average rating']);

        $this->assertCount(2, $resource['images']);
        $this->assertEquals($image1->id, $resource['images'][0]['id']);
        $this->assertEquals($image2->id, $resource['images'][1]['id']);

        $this->assertCount(1, $resource['rating']);
        $this->assertEquals($rating->one_star, $resource['rating'][0]['one_star']);
        $this->assertEquals($rating->five_stars, $resource['rating'][0]['five_stars']);
    }
}