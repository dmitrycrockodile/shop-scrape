<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;

class UserProductRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        foreach ($products as $product) {
            $assignedUsers = $users->random(1);
            foreach ($assignedUsers as $assignedUser) {
                UserProduct::create([
                    'product_id'  => $product->id,
                    'user_id' => $assignedUser->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
