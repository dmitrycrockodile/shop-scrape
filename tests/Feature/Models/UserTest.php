<?php

namespace Tests\Feature\Models;

use App\Enums\UserRole;
use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_belongs_to_many_retailers()
    {
        $user = User::factory()->create();

        $currency = Currency::factory()->create();

        $retailers = Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);

        $user->retailers()->attach($retailers->pluck('id')->toArray());
        $user->load('retailers');

        $userRetailer = $user->retailers->first()->pivot;

        $this->assertCount(3, $user->retailers);
        $this->assertTrue($user->retailers->first() instanceof Retailer);
        $this->assertNotNull($userRetailer);
    }

    public function test_user_has_products()
    {
        $user = User::factory()->create();

        $packSize = PackSize::factory()->create();

        $products = Product::factory()->count(3)->create([
            'pack_size_id' => $packSize->id
        ]);

        $user->products()->attach($products->pluck('id')->toArray());
        $user->load('products');

        $userProduct = $user->products->first()->pivot;

        $this->assertCount(3, $user->products);
        $this->assertTrue($user->products->first() instanceof Product);
        $this->assertNotNull($userProduct);
    }

    public function test_user_has_pack_sizes()
    {
        $user = User::factory()->create();

        $packSizes = PackSize::factory()->count(3)->create();

        $user->packSizes()->attach($packSizes->pluck('id')->toArray());
        $user->load('packSizes');

        $userPackSize = $user->packSizes->first()->pivot;
        
        $this->assertCount(3, $user->packSizes);
        $this->assertTrue($user->packSizes->first() instanceof PackSize);
        $this->assertNotNull($userPackSize);
    }

    public function test_super_user_can_access_all_retailers()
    {
        $superUser = User::factory()->create([
            'role' => UserRole::SUPER_USER,
        ]);

        $currency = Currency::factory()->create();
        
        $retailers = Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);

        $accessibleRetailers = $superUser->accessibleRetailers()->get();
        $this->assertCount(3, $accessibleRetailers);
    }

    public function test_non_super_user_can_access_associated_retailers()
    {    
        $user = User::factory()->create([
            'role' => UserRole::REGULAR_USER,
        ]);

        $currency = Currency::factory()->create();

        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
    
        $user->retailers()->attach($retailer1);

        $accessibleRetailers = $user->accessibleRetailers()->get();

        $this->assertCount(1, $accessibleRetailers);
        $this->assertTrue($accessibleRetailers->contains($retailer1));
        $this->assertFalse($accessibleRetailers->contains($retailer2));
    }
}