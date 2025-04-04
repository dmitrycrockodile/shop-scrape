<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CurrencyController;
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
    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::resource('pack-sizes', PackSizeController::class)->only(['index', 'store', 'update', 'destroy']);
    
    Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');

    Route::get('/products/{product}/retailers', [ProductController::class, 'getRetailers'])->name('products.retailers');
    Route::post('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/upload-csv', [ProductController::class, 'uploadCSV'])->name('products.file.upload');
    Route::post('/products/export', [ProductController::class, 'export'])->name('products.file.export');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::resource('retailers', RetailerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/retailers/{retailer}/products', [RetailerController::class, 'getProducts'])->name('retailers.products.get');
    Route::post('/retailers/{retailer}/products', [RetailerController::class, 'addProducts'])->name('retailers.products.add');

    Route::post('/retailers/metrics', [MetricsController::class, 'getRetailerMetrics'])->name('metrics.retailers.index');
    Route::get('/retailers/weekly-ratings', [MetricsController::class, 'getAvgRatingForLastWeek']);
    Route::get('/retailers/weekly-pricing', [MetricsController::class, 'getAvgPriceForLastWeek']);

    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/users/{user}/assign-retailers', [UserController::class, 'assignRetailers'])->name('users.retailers.assign');
    Route::post('/users/{user}/revoke-retailers', [UserController::class, 'revokeRetailers'])->name('users.retailers.revoke');

    Route::get('/scraped-data/export', [ScrapedDataController::class, 'exportCSV'])->name('scraped-data.export');
});
Route::post('/scraped-data', [ScrapedDataController::class, 'store'])->name('scraped-data.store');

Route::middleware('guest:sanctum')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});
