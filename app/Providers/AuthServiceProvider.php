<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider {
   protected $policies = [
      User::class => UserPolicy::class,
   ];

   public function boot() {
      Gate::policy(User::class, UserPolicy::class);
   }
}