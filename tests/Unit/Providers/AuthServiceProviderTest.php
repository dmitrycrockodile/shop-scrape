<?php

namespace Tests\Unit\Providers;

use App\Models\PackSize;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\User;
use App\Policies\PackSizePolicy;
use App\Policies\ProductPolicy;
use App\Policies\RetailerPolicy;
use App\Policies\UserPolicy;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
    public function test_policies_are_registered_correctly()
    {
        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $this->assertInstanceOf(UserPolicy::class, Gate::getPolicyFor(User::class));
        $this->assertInstanceOf(ProductPolicy::class, Gate::getPolicyFor(Product::class));
        $this->assertInstanceOf(RetailerPolicy::class, Gate::getPolicyFor(Retailer::class));
        $this->assertInstanceOf(PackSizePolicy::class, Gate::getPolicyFor(PackSize::class));
    }
}