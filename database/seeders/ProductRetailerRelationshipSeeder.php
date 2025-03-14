<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\ProductRetailer;

class ProductRetailerRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailers = Retailer::all();
        $products = Product::all();

        foreach ($products as $product) {
            $assignedRetailers = $retailers->random(rand(1, 2));
            foreach ($assignedRetailers as $assignedRetailer) {
                ProductRetailer::create([
                    'product_id'  => $product->id,
                    'retailer_id' => $assignedRetailer->id,
                    'product_url' => fake()->url(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
