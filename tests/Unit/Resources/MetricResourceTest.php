<?php

namespace Tests\Unit\Resources;

use App\Models\ScrapedData;
use App\Http\Resources\Metric\MetricResource;
use App\Models\Currency;
use App\Models\PackSize;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MetricResourceTest extends TestCase
{
    public function test_metric_resource_returns_null_date_if_no_input_and_no_data()
    {
        PackSize::factory()->create();
        Currency::factory()->create();

        $metric = (object) [
            'retailer_id' => 4,
            'retailer_title' => 'Walmart',
            'retailer_logo' => 'https://walmart.com/logo.png',
            'avg_rating' => 4.0,
            'avg_price' => 50.0,
            'avg_images' => 1.5,
        ];

        $resource = (new MetricResource($metric))->toArray(request());

        $this->assertNull($resource['date']);
    }

    public function test_metric_resource_returns_expected_structure()
    {
        PackSize::factory()->create();
        Currency::factory()->create();
        ScrapedData::factory()->create([
            'created_at' => Carbon::parse('2024-03-29'),
        ]);

        $metric = (object) [
            'retailer_id' => 1,
            'retailer_title' => 'Amazon',
            'retailer_logo' => 'https://amazon.com/logo.png',
            'product_id' => 101,
            'product_title' => 'Laptop XYZ',
            'avg_rating' => 4.49,
            'avg_price' => 999.987,
            'avg_images' => 3.212,
        ];

        $request = request()->merge([
            'start_date' => '2024-03-22',
            'end_date' => '2024-03-29',
        ]);

        $resource = (new MetricResource($metric))->toArray($request);

        $this->assertEquals(1, $resource['retailer_id']);
        $this->assertEquals('Amazon', $resource['retailer_title']);
        $this->assertEquals('https://amazon.com/logo.png', $resource['retailer_logo']);
        $this->assertEquals(4.49, $resource['avg_rating']);
        $this->assertEquals(999.99, $resource['avg_price']);
        $this->assertEquals(3.21, $resource['avg_images_count']);
        $this->assertEquals('2024-03-22 - 2024-03-29', $resource['date']);
    }

    public function test_metric_resource_returns_default_date_if_no_input()
    {
        PackSize::factory()->create();
        Currency::factory()->create();
        ScrapedData::factory()->create([
            'created_at' => Carbon::parse('2024-04-01'),
        ]);

        $metric = (object) [
            'retailer_id' => 2,
            'retailer_title' => 'eBay',
            'retailer_logo' => 'https://ebay.com/logo.png',
            'avg_rating' => 3.78,
            'avg_price' => 105.123,
            'avg_images' => 2.987,
        ];

        $resource = (new MetricResource($metric))->toArray(request());

        $this->assertEquals('2024-04-01', $resource['date']);
    }
}