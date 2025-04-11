<?php

namespace Tests\Feature\Policies;

use App\Models\Currency;
use App\Models\Retailer;
use App\Policies\RetailerPolicy;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RetailerPolicyTest extends TestCase
{
    protected RetailerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RetailerPolicy();
    }

    public function test_see_products_returns_true_for_super_user()
    {
        $user = User::factory()->create(['role' => 'super_user']);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $this->assertTrue($this->policy->seeProducts($user, $retailer));
    }

    public function test_see_products_returns_true_when_user_has_access()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        DB::table('user_retailers')->insert([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($this->policy->seeProducts($user, $retailer));
    }

    public function test_see_products_returns_false_when_user_has_no_access()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $this->assertFalse($this->policy->seeProducts($user, $retailer));
    }

    public function test_add_products_returns_true_for_super_user()
    {
        $user = User::factory()->create(['role' => 'super_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $this->assertTrue($this->policy->addProducts($user, $retailer));
    }

    public function test_add_products_returns_true_when_user_has_access()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        DB::table('user_retailers')->insert([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($this->policy->addProducts($user, $retailer));
    }

    public function test_add_products_returns_false_when_user_has_no_access()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $this->assertFalse($this->policy->addProducts($user, $retailer));
    }

    public function test_get_metrics_returns_true_for_super_user()
    {
        $user = User::factory()->create(['role' => 'super_user']);

        $this->assertTrue($this->policy->getMetrics($user));
    }

    public function test_get_metrics_returns_true_when_user_has_retailers()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        DB::table('user_retailers')->insert([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertTrue($this->policy->getMetrics($user));
    }

    public function test_get_metrics_returns_false_when_user_has_no_retailers()
    {
        $user = User::factory()->create(['role' => 'regular_user']);

        $this->assertFalse($this->policy->getMetrics($user));
    }
}