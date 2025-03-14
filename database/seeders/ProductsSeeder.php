<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::factory(100)->create();

        foreach ($products as $product) {
            ProductImage::factory(2)->create([
                'product_id' => $product->id
            ]);
        }
    }
}
