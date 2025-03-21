<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PackSize\StoreRequest;
use App\Http\Resources\PackSize\PackSizeResource;
use App\Models\PackSize;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PackSizeController extends BaseController {
   /**
    * Retrieves the pack sizes.
    * 
    * @return JsonResponse A JSON response containing retrieved pack sizes.
   */
   public function index(): JsonResponse {
      try {
         $packSizes = PackSize::all();

         return $this->successResponse(PackSizeResource::collection($packSizes));
      } catch (\Exception $e) {
         Log::error('Failed to retrieve pack sizes: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse('Failed to recieve pack sizes, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }

   /**
    * Stores the pack size.
    * 
    * @param StoreRequest $request A request with pack size data
    * 
    * @return JsonResponse A JSON response containing newly created pack size or error info.
   */
   public function store(StoreRequest $request): JsonResponse {
      $data = $request->validated();

      try {
         $packSize = PackSize::create($data);

         return $this->successResponse(new PackSizeResource($packSize), 'Successfully created the pack size!', Response::HTTP_CREATED);
      } catch (\Exception $e) {
         Log::error('Failed to create the pack size: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse('Failed to create the pack size, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   } 

   /**
    * Updates the pack size according to new data.
    * 
    * @param StoreRequest $request A request with new pack size data
    * @param PackSize $packSize Instance of the pack size to update
    * 
    * @return JsonResponse A JSON response containing updated pack size or error info.
   */
   public function update(StoreRequest $request, PackSize $packSize): JsonResponse {
      $data = $request->validated();

      try {
         $packSize->update($data);

         return $this->successResponse(new PackSizeResource($packSize), 'Successfully updated the pack size!', Response::HTTP_CREATED);
      } catch (\Exception $e) {
         Log::error('Failed to create the pack size: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse('Failed to update the pack size, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }   
   } 

   /**
    * Deletes the pack size.
    * 
    * @param PackSize $packSize Instance of the pack size to delete
    * 
    * @return JsonResponse A JSON response containing success message for user or an error.
   */
   public function destroy(PackSize $packSize): JsonResponse {
      try {
         $packSize->delete();
         
         return $this->successResponse('Pack size successfully deleted.');
      } catch (\Exception $e) {
         Log::error('Failed to delete the pack size: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse('Failed to delete the pack size, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }
}