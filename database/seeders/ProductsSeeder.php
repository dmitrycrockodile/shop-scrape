<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductsSeeder extends Seeder
{
    private const PRODUCTS_COUNT = 100;
    private const PRODUCT_IMAGES_COUNT = 2;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::factory(self::PRODUCTS_COUNT)->create();

        foreach ($products as $product) {
            ProductImage::factory(self::PRODUCT_IMAGES_COUNT)->create([
                'product_id' => $product->id
            ]);
        }
    }
}
