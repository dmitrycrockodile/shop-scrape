<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\PackSize;
use App\Models\Product;

class PackSizeTest extends TestCase
{
    public function test_pack_size_has_many_products()
    {
        $packSize = PackSize::factory()->create();
        $products = Product::factory()->count(3)->create([
            'pack_size_id' => $packSize->id
        ]);

        $this->assertCount(3, $packSize->products);
        $this->assertTrue($packSize->products->first() instanceof Product);
    }
}