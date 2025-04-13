<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\Product\ProductResource;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    public function test_product_resource_returns_expected_structure(): void
    {
        $packSize = PackSize::factory()->create([
            'name' => 'Medium',
            'weight' => 2.0,
            'weight_unit' => 'l',
            'amount' => 10,
        ]);

        $product = Product::factory()->create([
            'title' => 'Test Product',
            'description' => 'Nice product',
            'manufacturer_part_number' => 'ABC123',
            'pack_size_id' => $packSize->id
        ]);

        $images = ProductImage::factory()->count(2)->create([
            'product_id' => $product->id
        ]);

        $product->load(['packSize', 'images']);

        $resource = (new ProductResource($product))->toArray(request());

        $this->assertSame($product->id, $resource['id']);
        $this->assertSame('Test Product', $resource['title']);
        $this->assertSame('Nice product', $resource['description']);
        $this->assertSame('ABC123', $resource['manufacturer_part_number']);

        $this->assertSame($packSize->id, $resource['pack_size']['id']);
        $this->assertSame($packSize->name, $resource['pack_size']['name']);
        $this->assertSame($packSize->weight, $resource['pack_size']['weight']);
        $this->assertSame($packSize->weight_unit, $resource['pack_size']['weight_unit']);
        $this->assertSame($packSize->amount, $resource['pack_size']['amount']);

        $this->assertCount(2, $resource['images']);
        $this->assertArrayHasKey('id', $resource['images'][0]);
        $this->assertArrayHasKey('file_url', $resource['images'][0]);
        $this->assertArrayHasKey('file_name', $resource['images'][0]);
    }
}