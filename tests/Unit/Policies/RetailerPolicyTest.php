<?php

namespace Tests\Unit\Policies;

use App\Models\Currency;
use App\Models\Retailer;
use App\Policies\RetailerPolicy;
use App\Models\User;
use Tests\TestCase;

class RetailerPolicyTest extends TestCase
{
    protected RetailerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RetailerPolicy();
    }

    public function test_seeAll_returns_true_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        
        $this->assertTrue($this->policy->seeAll($user));
    }

    public function test_seeAll_returns_false_for_non_super_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        
        $this->assertFalse($this->policy->seeAll($user));
    }

    public function test_store_returns_true_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        
        $this->assertTrue($this->policy->store($user));
    }

    public function test_store_returns_false_for_non_super_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        
        $this->assertFalse($this->policy->store($user));
    }

    public function test_update_returns_true_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        
        $this->assertTrue($this->policy->update($user, $retailer));
    }

    public function test_update_returns_false_for_non_super_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
    
        $this->assertFalse($this->policy->update($user, $retailer));
    }

    public function test_delete_returns_true_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        
        $this->assertTrue($this->policy->delete($user));
    }

    public function test_delete_returns_false_for_non_super_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        
        $this->assertFalse($this->policy->delete($user));
    }
}