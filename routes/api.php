<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RetailerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
   return $request->user();
})->middleware('auth:sanctum');

Route::get('/products/{product}/retailers', [ProductController::class, 'getRetailers'])->name('products.retailers');
Route::post('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/retailers', [RetailerController::class, 'index'])->name('retailers.index');
Route::get('/retailers/{retailer}/products', [RetailerController::class, 'getProducts'])->name('retailers.products');
Route::post('/retailers', [RetailerController::class, 'store'])->name('retailers.store');
Route::put('/retailers/{retailer}', [RetailerController::class, 'update'])->name('retailers.update');
Route::delete('/retailers/{retailer}', [RetailerController::class, 'destroy'])->name('retailers.destroy');