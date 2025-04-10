<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Retailer;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    public function test_it_can_create_a_currency(): void
    {
        $currency = Currency::create([
            'code' => 'UAH',
            'name' => 'Ukrainian Hryvnia',
            'symbol' => '+',
        ]);

        $this->assertDatabaseHas('currencies', [
            'code' => 'UAH',
            'name' => 'Ukrainian Hryvnia',
            'symbol' => '+',
        ]);
    }

    public function test_it_has_retailers_relationship(): void
    {
        $currency = Currency::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $this->assertTrue($currency->retailers->contains($retailer));
    }
}