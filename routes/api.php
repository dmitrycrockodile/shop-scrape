<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\ScrapedDataController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // UI
    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::post('/pack-sizes', [PackSizeController::class, 'index'])->name('pack-sizes.index');
    Route::post('/pack-sizes/store', [PackSizeController::class, 'store'])->name('pack-sizes.store');
    Route::resource('pack-sizes', PackSizeController::class)->only(['update', 'destroy']);

    // UI
    Route::get('/products/{product}/retailers', [ProductController::class, 'getRetailers'])->name('products.retailers');
    Route::post('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // UI
    Route::resource('retailers', RetailerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/retailers/{retailer}/products', [RetailerController::class, 'getProducts'])->name('retailers.products.get');
    Route::post('/retailers/{retailer}/products', [RetailerController::class, 'addProducts'])->name('retailers.products.add');

    // Delete retailer from the product ??

    Route::post('/retailers/metrics', [MetricsController::class, 'getRetailerMetrics'])->name('metrics.retailers.index');

    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/users/{user}/assign-retailers', [UserController::class, 'assignRetailers'])->name('users.retailers.assign');
    Route::post('/users/{user}/revoke-retailers', [UserController::class, 'revokeRetailers'])->name('users.retailers.revoke');
});
Route::post('/scraped-data', [ScrapedDataController::class, 'store'])->name('scraped-data.store');

Route::middleware('guest:sanctum')->group(function () {
     // UI
    Route::post('/login', [LoginController::class, 'login']);
});
