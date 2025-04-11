<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\Currency;
use App\Models\Retailer;

class CurrencyTest extends TestCase
{
    public function test_currency_has_many_retailers()
    {
        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);

        $this->assertCount(3, $currency->retailers);
        $this->assertTrue($currency->retailers->first() instanceof Retailer);
    }
}