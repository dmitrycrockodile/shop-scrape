<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\IndexRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use App\Service\ProductService;

class ProductController extends BaseController {
   protected ProductService $productService;

   public function __construct(ProductService $productService) {
      $this->productService = $productService;
   }

   public function index() {
      return 111;
   } 

   public function store(IndexRequest $request): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->store($data);
      
      if (!$serviceResponse['success']) {
         return $this->errorResponse($serviceResponse['message'], $serviceResponse['status']);
      }

      return $this->successResponse($serviceResponse['product'], $serviceResponse['message'], Response::HTTP_CREATED);
   } 

   public function update(IndexRequest $request, Product $product): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->update($data, $product);

      if (!$serviceResponse['success']) {
         return $this->errorResponse($serviceResponse['message'], $serviceResponse['status']);
      }

      return $this->successResponse($serviceResponse['product'], $serviceResponse['message']);
   } 

   public function destroy() {
      
   } 
}