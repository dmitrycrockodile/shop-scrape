<?php

namespace Tests\Unit\Models;

use App\Models\ProductImage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    public function test_get_image_url_when_file_url_is_null()
    {
        $productImage = new ProductImage([
            'file_url' => null,
        ]);

        $this->assertNull($productImage->image_url);
    }

    public function test_get_image_url_when_file_url_is_valid_url()
    {
        $productImage = new ProductImage([
            'file_url' => 'https://example.com/image.jpg',
        ]);

        $this->assertEquals('https://example.com/image.jpg', $productImage->image_url);
    }

    public function test_get_image_url_when_file_url_is_relative_path()
    {
        $productImage = new ProductImage([
            'file_url' => 'images/product1.jpg',
        ]);

        $expectedUrl = url('storage/images/product1.jpg');
        $this->assertEquals($expectedUrl, $productImage->image_url);
    }
}