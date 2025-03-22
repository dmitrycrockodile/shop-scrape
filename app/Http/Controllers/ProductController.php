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
   } 

   /**
    * Retrieves the retailers for the specified product.
    * 
    * @param Product $product Instance of the product whose retailers we want to retrieve
    * 
    * @return JsonResponse A JSON response containing product retailers or error info.
   */
   public function getRetailers(Product $product): JsonResponse {
      $retailers = $product->retailers;

      return $this->successResponse(
         RetailerResource::collection($retailers),
         'messages.index.success',
         ['attribute' => 'product retailers']
      );
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

      return $this->successResponse(
         $serviceResponse['product'],
         'messages.update.success',
         ['attribute' => self::ENTITY]
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
      $product->delete();
      return $this->successResponse(
         null,
         'messages.destroy.success',
         ['attribute' => self::ENTITY]
      );
   }
}