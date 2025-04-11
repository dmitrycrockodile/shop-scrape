<?php

namespace Tests\Unit\Policies;

use App\Models\PackSize;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\User;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    protected ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ProductPolicy();
    }

    public function test_super_user_can_update_any_product()
    {
        $user = User::factory()->create(['role' => 'super_user']);
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
    
        $this->assertTrue($this->policy->update($user, $product));
    }

    public function test_regular_user_can_update_their_own_products()
    {
        $user = User::factory()->create();
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $user->products()->attach($product);

        $this->assertTrue($this->policy->update($user, $product));
    }

    public function test_regular_user_cannot_update_other_users_products()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $user1->products()->attach($product);
    
        $this->assertFalse($this->policy->update($user2, $product));
    }

    public function test_super_user_can_delete_products()
    {
        $user = User::factory()->create(['role' => 'super_user']);
        $packSize = PackSize::factory()->create();

        Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
    
        $this->assertTrue($this->policy->delete($user));
    }

    public function test_regular_user_cannot_delete_products()
    {
        $user = User::factory()->create();
        $packSize = PackSize::factory()->create();

        Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
    
        $this->assertFalse($this->policy->delete($user));
    }
}
