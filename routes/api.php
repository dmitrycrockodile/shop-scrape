<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
   return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function() {
   Route::post('/logout', [LogoutController::class, 'logout']);

   Route::get('/packsizes', [PackSizeController::class, 'index'])->name('packsizes.index');
   Route::post('/packsizes', [PackSizeController::class, 'store'])->name('packsizes.store');
   Route::put('/packsizes/{packSize}', [PackSizeController::class, 'update'])->name('packsizes.update');
   Route::delete('/packsizes/{packSize}', [PackSizeController::class, 'destroy'])->name('packsizes.destroy');
   
   Route::get('/products/{product}/retailers', [ProductController::class, 'getRetailers'])->name('products.retailers');
   Route::post('/products', [ProductController::class, 'index'])->name('products.index');
   Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
   Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
   Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
   
   Route::get('/retailers', [RetailerController::class, 'index'])->name('retailers.index');
   Route::get('/retailers/{retailer}/products', [RetailerController::class, 'getProducts'])->name('retailers.products.get');
   Route::post('/retailers', [RetailerController::class, 'store'])->name('retailers.store');
   Route::post('/retailers/{retailer}/products', [RetailerController::class, 'addProducts'])->name('retailers.products.add');
   Route::put('/retailers/{retailer}', [RetailerController::class, 'update'])->name('retailers.update');
   Route::delete('/retailers/{retailer}', [RetailerController::class, 'destroy'])->name('retailers.destroy');
});

Route::middleware('guest:sanctum')->group(function() {
   // Auth related, accessible for guests routes
   Route::post('/register', [RegisterController::class, 'register']);
   Route::post('/login', [LoginController::class, 'login']);
});