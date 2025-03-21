<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Retailer\RetailerRequest;
use App\Http\Requests\Retailer\AddProductsRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Retailer\RetailerResource;
use App\Models\Retailer;
use App\Service\RetailerService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RetailerController extends BaseController {
   protected RetailerService $retailerService;
   private const ENTITY = 'retailer';

   public function __construct(RetailerService $retailerService) {
      $this->retailerService = $retailerService;
   }

   /**
    * Retrieves the retailers.
    * 
    * @return JsonResponse A JSON response containing retrieved retailers or error message.
   */
   public function index(): JsonResponse {
      try {
         $retailers = Retailer::with('currency')->get();

         return $this->successResponse(
            RetailerResource::collection($retailers),
            'messages.index.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve retailers: ' . $e->getMessage(), [
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
    * Retrieves the products for the specified retailer.
    * 
    * @param Retailer $retailer Instance of the retailer whose products we want to retrieve
    * 
    * @return JsonResponse A JSON response containing retailer products or error info.
   */
   public function getProducts(Retailer $retailer): JsonResponse {
      try {
         $products = $retailer->products;

         return $this->successResponse(
            ProductResource::collection($products),
            'messages.index.success',
            ['attribute' => 'retailer\'s products']
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve retailer\'s products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse(
            'messages.index.error',
            ['attribute' => 'retailer\'s products'],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
      }
   }

   /**
    * Retrieves the products for the specified retailer.
    * 
    * @param 
    * @param Retailer $retailer Instance of the retailer whose products we want to retrieve
    * 
    * @return JsonResponse A JSON response containing retailer products or error info.
   */
   public function addProducts(AddProductsRequest $request, Retailer $retailer): JsonResponse {
      $data = $request->validated();
      $products = $data['products'] ?? [];

      $serviceResponse = $this->retailerService->syncOrAttachProducts($retailer, $products);

      return $serviceResponse['success']
         ? $this->successResponse(
            $serviceResponse['retailer'],
            'messages.store.success',
            ['attribute' => 'products']
         )
         : $this->errorResponse(
            'messages.store.error',
            ['attribute' => 'products'],
            $serviceResponse['error'],
            $serviceResponse['status']
         );
   }

   /**
    * Stores the retailer.
    * 
    * @param StoreRequest $request A request with retailer data
    * 
    * @return JsonResponse A JSON response containing newly created retailer or error info.
   */
   public function store(RetailerRequest $request): JsonResponse {
      $data = $request->validated();

      $serviceResponse = $this->retailerService->store($data);

      return $serviceResponse['success']
         ? $this->successResponse(
            $serviceResponse['retailer'],
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
    * Updates the retailer according to new data.
    * 
    * @param RetailerRequest $request A request with new retailer data
    * @param Retailer $retailer Instance of the retailer to update
    * 
    * @return JsonResponse A JSON response containing updated retailer or error info.
   */
   public function update(RetailerRequest $request, Retailer $retailer): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->retailerService->update($data, $retailer);

      return $serviceResponse['success']
         ? $this->successResponse(
            $serviceResponse['retailer'],
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
    * Deletes the retailer.
    * 
    * @param Retailer $retailer Instance of the retailer to delete
    * 
    * @return JsonResponse A JSON response containing success message for user or an error.
   */
   public function destroy(Retailer $retailer): JsonResponse {
      try {
         $retailer->delete();

         return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to delete the retailer: ' . $e->getMessage(), [
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