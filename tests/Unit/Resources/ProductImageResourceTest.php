<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ProductImage\ProductImageResource;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use Tests\TestCase;

class ProductImageResourceTest extends TestCase
{
    public function test_product_image_resource_returns_expected_structure(): void
    {
        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $productImage = ProductImage::factory()->create([
            'product_id' => $product->id,
            'file_url' => 'http://example.com/image.jpg',
            'file_name' => 'image.jpg',
        ]);

        $resource = (new ProductImageResource($productImage))->toArray(request());

        $this->assertSame($productImage->id, $resource['id']);
        $this->assertSame($productImage->imageUrl, $resource['file_url']);
        $this->assertSame($productImage->file_name, $resource['file_name']);
    }
}
