<?php

namespace Tests\Unit\Resources;

use App\Models\Currency;
use App\Models\ScrapedDataImage;
use App\Models\ScrapingSession;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Http\Resources\ScrapedDataImage\ScrapedDataImageResource;
use Tests\TestCase;

class ScrapedDataImageResourceTest extends TestCase
{
    public function test_scraped_data_image_resource_returns_expected_structure(): void
    {
        PackSize::factory()->create();
        Currency::factory()->create();

        $image = ScrapedDataImage::factory()->create([
            'file_url' => 'http://example.com/image1.jpg',
            'file_name' => 'image1.jpg',
            'position' => 1,
        ]);

        $resource = (new ScrapedDataImageResource($image))->toArray(request());

        $this->assertSame($image->id, $resource['id']);
        $this->assertSame('http://example.com/image1.jpg', $resource['file_url']);
        $this->assertSame('image1.jpg', $resource['file_name']);
        $this->assertSame(1, $resource['position']);
    }
}
