<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RetailerPolicy;
use App\Models\User;
use App\Models\Product;
use App\Models\Retailer;

class AuthServiceProvider extends ServiceProvider {
   protected $policies = [
      User::class => UserPolicy::class,
      Product::class => ProductPolicy::class,
      Retailer::class => RetailerPolicy::class,
   ];

   public function boot() {
      Gate::policy(User::class, UserPolicy::class);
      Gate::policy(Product::class, ProductPolicy::class);
      Gate::policy(Retailer::class, RetailerPolicy::class);
   }
}