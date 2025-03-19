<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Retailer\RetailerRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Retailer\RetailerResource;
use App\Models\Retailer;
use App\Service\RetailerService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RetailerController extends BaseController {
   protected RetailerService $retailerService;

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

         return $this->successResponse(RetailerResource::collection($retailers));
      } catch (\Exception $e) {
         Log::error('Failed to retrieve retailers: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse('Failed to recieve retailers, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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

         return $this->successResponse(ProductResource::collection($products));
      } catch (\Exception $e) {
         Log::error('Failed to retrieve retailer\'s products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse('Failed to retrieve retailer\'s products, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
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
         ? $this->successResponse($serviceResponse['retailer'], $serviceResponse['message'], Response::HTTP_CREATED)
         : $this->errorResponse($serviceResponse['message'], $serviceResponse['error'], $serviceResponse['status']);
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
         ? $this->successResponse($serviceResponse['retailer'], $serviceResponse['message'])
         : $this->errorResponse($serviceResponse['message'], $serviceResponse['error'], $serviceResponse['status']);
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
         return $this->successResponse('Retailer successfully deleted.');
      } catch (\Exception $e) {
         Log::error('Failed to delete the retailer: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse('Failed to delete the retailer, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }
}