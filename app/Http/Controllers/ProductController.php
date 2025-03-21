<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Retailer\RetailerResource;
use App\Models\Product;
use App\Service\ProductService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends BaseController {
   protected ProductService $productService;
   private const ENTITY = 'product';

   public function __construct(ProductService $productService) {
      $this->productService = $productService;
   }

   /**
    * Retrieves the products.
    * 
    * @param IndexRequest A request with pagination data (if provided)
    * 
    * @return JsonResponse A JSON response containing retrieved paginated products.
   */
   public function index(IndexRequest $request): JsonResponse {
      try {
         $data = $request->validated();
         $products = Product::with(['packSize', 'images'])->paginate(
            $data['dataPerPage'] ?? 100, 
            ['*'], 
            'page', 
            $data['page'] ?? 1
         );

         return $this->successResponse(
            ProductResource::collection($products),
            'messages.index.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse(
            'messages.index.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(), 
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   } 

   /**
    * Retrieves the retailers for the specified product.
    * 
    * @param Product $product Instance of the product whose retailers we want to retrieve
    * 
    * @return JsonResponse A JSON response containing product retailers or error info.
   */
   public function getRetailers(Product $product): JsonResponse {
      try {
         $retailers = $product->retailers;

         return $this->successResponse(
            RetailerResource::collection($retailers),
            'messages.index.success',
            ['attribute' => 'product retailers']
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve product\'s retailers: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse(
            'messages.index.error',
            ['attribute' => 'product retailers'],
            $e->getMessage(), 
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }

   /**
    * Stores the product.
    * 
    * @param ProductRequest $request A request with product data
    * 
    * @return JsonResponse A JSON response containing newly created product or error info.
   */
   public function store(ProductRequest $request): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->store($data);
      
      return $serviceResponse['success']
         ? $this->successResponse(
            $serviceResponse['product'], 
            'messages.store.success',
            ['attribute' => self::ENTITY],
            Response::HTTP_CREATED
         )
         : $this->errorResponse(
            'messages.store.error',
            ['attribute' => self::ENTITY],
            $serviceResponse['error'], 
            $serviceResponse['status']
         );
   } 

   /**
    * Updates the product according to new data.
    * 
    * @param ProductRequest $request A request with new product data
    * @param Product $product Instance of the product to update
    * 
    * @return JsonResponse A JSON response containing updated product or error info.
   */
   public function update(ProductRequest $request, Product $product): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->update($data, $product);

      return $serviceResponse['success']
         ? $this->successResponse(
            $serviceResponse['product'],
            'messages.update.success',
            ['attribute' => self::ENTITY]
         )
         : $this->errorResponse(
            'messages.update.error',
            ['attribute' => self::ENTITY],
            $serviceResponse['error'],
            $serviceResponse['status']
         );
   } 

   /**
    * Deletes the product.
    * 
    * @param Product $product Instance of the product to delete
    * 
    * @return JsonResponse A JSON response containing success message for user or an error.
   */
   public function destroy(Product $product): JsonResponse {
      try {
         $product->delete();
         return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to delete the product: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse(
            'messages.destroy.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }
}