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
      $retailers = Retailer::with('currency')->get();

      return $this->successResponse(
         RetailerResource::collection($retailers),
         'messages.index.success',
         ['attribute' => self::ENTITY]
      );
   } 

   /**
    * Retrieves the products for the specified retailer.
    * 
    * @param Retailer $retailer Instance of the retailer whose products we want to retrieve
    * 
    * @return JsonResponse A JSON response containing retailer products or error info.
   */
   public function getProducts(Retailer $retailer): JsonResponse {
      $products = $retailer->products;

      return $this->successResponse(
         ProductResource::collection($products),
         'messages.index.success',
         ['attribute' => 'retailer\'s products']
      );
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

      return $this->successResponse(
         $serviceResponse['retailer'],
         'messages.store.success',
         ['attribute' => 'products']
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

      return $this->successResponse(
         $serviceResponse['retailer'],
         'messages.store.success',
         ['attribute' => self::ENTITY],
         Response::HTTP_CREATED
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

      return $this->successResponse(
         $serviceResponse['retailer'],
         'messages.update.success',
         ['attribute' => self::ENTITY]
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
      $retailer->delete();

      return $this->successResponse(
         null,
         'messages.destroy.success',
         ['attribute' => self::ENTITY]
      );
   }
}