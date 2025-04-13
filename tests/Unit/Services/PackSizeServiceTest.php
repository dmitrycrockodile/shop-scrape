<?php

namespace Tests\Unit\Services;

use App\Models\PackSize;
use App\Service\PackSizeService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PackSizeServiceTest extends TestCase
{
    private PackSizeService $packSizeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->packSizeService = new PackSizeService();
    }

    public function test_store_method_creates_pack_size_and_attaches_to_user()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $user = User::factory()->create();

        $response = $this->packSizeService->store([
            'name' => 'Test Pack', 
            'weight' => 200, 
            'weight_unit' => 'ml', 
            'amount' => 3
        ], $user);
        
        $this->assertTrue($response['success']);
        $this->assertEquals('Test Pack', $response['packSize']->resource->name);
        $this->assertEquals(200, $response['packSize']->resource->weight);
        $this->assertEquals('ml', $response['packSize']->resource->weight_unit);
        $this->assertEquals(3, $response['packSize']->resource->amount);
        $this->assertInstanceOf(PackSize::class, $response['packSize']->resource);
    }
}
