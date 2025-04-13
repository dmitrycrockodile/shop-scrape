<?php

namespace Tests\Unit\Resources;

use App\Models\Retailer;
use App\Models\Currency;
use App\Http\Resources\Retailer\RetailerResource;
use Tests\TestCase;

class RetailerResourceTest extends TestCase
{
    public function test_retailer_resource_returns_expected_structure(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
        ]);
        $retailer = Retailer::factory()->create([
            'title' => 'Best Buy',
            'url' => 'http://www.bestbuy.com',
            'logo' => 'logo.png',
            'currency_id' => $currency->id,
        ]);

        $retailer->refresh()->load('currency');

        $resource = (new RetailerResource($retailer))->toArray(request());

        $this->assertSame($retailer->id, $resource['id']);
        $this->assertSame('Best Buy', $resource['title']);
        $this->assertSame('http://www.bestbuy.com', $resource['url']);
        $this->assertStringContainsString('logo.png', $resource['logo']);

        $this->assertSame($currency->id, $resource['currency']['id']);
        $this->assertSame('USD', $resource['currency']['code']);
        $this->assertSame('$', $resource['currency']['symbol']);
        $this->assertSame('US Dollar', $resource['currency']['name']);
    }
}
