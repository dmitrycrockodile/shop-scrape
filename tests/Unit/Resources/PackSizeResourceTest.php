<?php

namespace Tests\Unit\Resources;

use App\Models\PackSize;
use App\Http\Resources\PackSize\PackSizeResource;
use Tests\TestCase;

class PackSizeResourceTest extends TestCase
{
    public function test_pack_size_resource_returns_expected_structure(): void
    {
        $packSize = PackSize::factory()->create([
            'name' => 'Medium',
            'weight' => '750',
            'weight_unit' => 'g',
            'amount' => 20,
        ]);

        $resource = (new PackSizeResource($packSize))->toArray(request());

        $this->assertSame($packSize->id, $resource['id']);
        $this->assertSame('Medium', $resource['name']);
        $this->assertSame('750', $resource['weight']);
        $this->assertSame('g', $resource['weight_unit']);
        $this->assertSame(20, $resource['amount']);
    }
}
