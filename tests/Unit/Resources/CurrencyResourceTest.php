<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\Currency\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Tests\TestCase;

class CurrencyResourceTest extends TestCase
{
    public function test_it_returns_correct_currency_resource_structure()
    {
        $currency = Currency::factory()->make([
            'id' => 1,
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
        ]);

        $resource = (new CurrencyResource($currency))->toArray(Request::create('/'));

        $this->assertEquals([
            'id' => 1,
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
        ], $resource);
    }
}