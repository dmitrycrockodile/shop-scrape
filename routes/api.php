<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckSuperUser;
use Illuminate\Support\Facades\Route;

// Accessible for REGULAR users routes
Route::middleware('auth:sanctum')->group(function() {
   Route::post('/logout', [LogoutController::class, 'logout']);

   Route::get('/pack-sizes', [PackSizeController::class, 'index'])->name('pack-sizes.index');
   Route::post('/pack-sizes', [PackSizeController::class, 'store'])->name('pack-sizes.store');

   Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
   Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
});

// Accessible for SUPER users routes
Route::middleware(['auth:sanctum', CheckSuperUser::class])->group(function() {
   Route::get('/users', [UserController::class, 'index'])->name('users.index');
   Route::post('/users', [UserController::class, 'store'])->name('users.store');
   Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
   Route::post('/users/{user}/assign-retailers', [UserController::class, 'assignRetailers'])->name('users.retailers.assign');
   Route::post('/users/{user}/revoke-retailers', [UserController::class, 'revokeRetailers'])->name('users.retailers.revoke');
   Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

   Route::get('/retailers', [RetailerController::class, 'index'])->name('retailers.index');
   Route::get('/retailers/{retailer}/products', [RetailerController::class, 'getProducts'])->name('retailers.products.get');
   Route::post('/retailers', [RetailerController::class, 'store'])->name('retailers.store');
   Route::post('/retailers/{retailer}/products', [RetailerController::class, 'addProducts'])->name('retailers.products.add');
   Route::put('/retailers/{retailer}', [RetailerController::class, 'update'])->name('retailers.update');
   Route::delete('/retailers/{retailer}', [RetailerController::class, 'destroy'])->name('retailers.destroy');

   Route::get('/products/{product}/retailers', [ProductController::class, 'getRetailers'])->name('products.retailers');
   Route::post('/products', [ProductController::class, 'index'])->name('products.index');
   Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

   Route::put('/pack-sizes/{packSize}', [PackSizeController::class, 'update'])->name('pack-sizes.update');
   Route::delete('/pack-sizes/{packSize}', [PackSizeController::class, 'destroy'])->name('pack-sizes.destroy');
});

// Accessible for guests routes
Route::middleware('guest:sanctum')->group(function() {
   Route::post('/login', [LoginController::class, 'login']);
});