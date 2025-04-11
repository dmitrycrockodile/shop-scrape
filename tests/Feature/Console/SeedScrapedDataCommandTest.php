<?php

namespace Tests\Feature\Console;

namespace Tests\Feature\Console;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\ScrapedData;
use App\Models\ScrapingSession;
use Tests\TestCase;

class SeedScrapedDataCommandTest extends TestCase
{
    public function test_scraped_data_seeder_runs_successfully()
    {
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $productRetailer = ProductRetailer::factory()->create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
        ]);
    
        $this->artisan('scraped:seed')
            ->expectsOutput('ScrapedDataSeeder has been run successfully.')
            ->assertExitCode(0);
    
        $this->assertDatabaseHas('scraped_data', [
            'product_retailer_id' => $productRetailer->id,
            'title' => $product->title,
            'description' => $product->description,
        ]);

        $scrapingSession = ScrapingSession::where('retailer_id', $retailer->id)->first();
        $this->assertNotNull($scrapingSession);
        $this->assertEquals('success', $scrapingSession->status);

        $scrapedData = ScrapedData::first();
        $this->assertDatabaseHas('ratings', [
            'scraped_data_id' => $scrapedData->id,
        ]);

        $this->assertDatabaseHas('scraped_data_images', [
            'scraped_data_id' => $scrapedData->id,
            'position' => 1,
        ]);
    
        $this->assertDatabaseHas('scraped_data', [
            'id' => $scrapedData->id,
        ]);
        
        $scrapedData = ScrapedData::find($scrapedData->id);
        $this->assertGreaterThanOrEqual(0, $scrapedData->avg_rating);
        $this->assertLessThanOrEqual(5, $scrapedData->avg_rating);
    }

    public function test_no_scraped_data_is_created_for_missing_product_retailer()
    {    
        $this->artisan('scraped:seed')
            ->expectsOutput('ScrapedDataSeeder has been run successfully.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('scraped_data', 0);
    }
}
