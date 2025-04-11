<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\PackSize;
use App\Policies\PackSizePolicy;
use Tests\TestCase;

class PackSizePolicyTest extends TestCase
{   
    protected PackSizePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PackSizePolicy();
    }

    public function test_super_user_can_update_any_pack_size()
    {
        $user = User::factory()->create(['role' => 'super_user']);
        $packSize = PackSize::factory()->create();
    
        $this->assertTrue($this->policy->update($user, $packSize));
    }

    public function test_regular_user_can_update_their_own_pack_size()
    {
        $user = User::factory()->create();
        $packSize = PackSize::factory()->create();
        $user->packSizes()->attach($packSize);

        $this->assertTrue($this->policy->update($user, $packSize));
    }

    public function test_regular_user_cannot_update_other_users_pack_size()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $packSize = PackSize::factory()->create();
        $user1->packSizes()->attach($packSize);
    
        $this->assertFalse($this->policy->update($user2, $packSize));
    }

    public function test_super_user_can_delete_pack_size()
    {
        $user = User::factory()->create(['role' => 'super_user']);
    
        $this->assertTrue($this->policy->delete($user));
    }

    public function test_regular_user_cannot_delete_pack_size()
    {
        $user = User::factory()->create();
    
        $this->assertFalse($this->policy->delete($user));
    }
}
