<?php

namespace Tests\Unit\Resources;

use App\Models\Currency;
use App\Http\Resources\Currency\CurrencyResource;
use Tests\TestCase;

class CurrencyResourceTest extends TestCase
{
    public function test_currency_resource_returns_expected_structure(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
        ]);

        $resource = (new CurrencyResource($currency))->toArray(request());

        $this->assertSame($currency->id, $resource['id']);
        $this->assertSame('USD', $resource['code']);
        $this->assertSame('US Dollar', $resource['name']);
        $this->assertSame('$', $resource['symbol']);
    }
}