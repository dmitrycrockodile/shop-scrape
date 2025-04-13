<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\PackSize\PackSizeResource;
use App\Models\PackSize;
use Illuminate\Http\Request;
use Tests\TestCase;

class PackSizeResourceTest extends TestCase
{
    public function test_it_returns_correct_currency_resource_structure()
    {
        $currency = PackSize::factory()->make([
            'id' => 1,
            'name' => 'Small',
            'weight' => 500,
            'weight_unit' => 'g',
            'amount' => 10,
        ]);

        $resource = (new PackSizeResource($currency))->toArray(Request::create('/'));

        $this->assertEquals([
            'id' => 1,
            'name' => 'Small',
            'weight' => 500,
            'weight_unit' => 'g',
            'amount' => 10,
        ], $resource);
    }
}