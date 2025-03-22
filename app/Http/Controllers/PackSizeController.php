<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PackSize\StoreRequest;
use App\Http\Resources\PackSize\PackSizeResource;
use App\Models\PackSize;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PackSize\IndexRequest;

class PackSizeController extends BaseController {
   private const ENTITY = 'pack size';

   /**
    * Retrieves the pack sizes.
    *
    * @param IndexRequest A request with pagination data (if provided)
    * 
    * @return JsonResponse A JSON response containing retrieved pack sizes.
   */
   public function index(IndexRequest $request): JsonResponse {
      $data = $request->validated();
      $packSizes = PackSize::paginate(
         $data['dataPerPage'] ?? 100, 
         ['*'], 
         'page', 
         $data['page'] ?? 1
      );;

      return $this->successResponse(
         PackSizeResource::collection($packSizes),
         'messages.index.success',
         ['attribute' => self::ENTITY]
      );
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
      $packSize = PackSize::create($data);

      return $this->successResponse(
         new PackSizeResource($packSize), 
         'messages.store.success',
         ['attribute' => self::ENTITY],
         Response::HTTP_CREATED
      );
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
      $this->authorize('update', PackSize::class);

      $data = $request->validated();
      $packSize->update($data);

      return $this->successResponse(
         new PackSizeResource($packSize), 
         'messages.update.success',
         ['attribute' => self::ENTITY],
         Response::HTTP_CREATED
      );  
   }

   /**
    * Deletes the pack size.
    * 
    * @param PackSize $packSize Instance of the pack size to delete
    * 
    * @return JsonResponse A JSON response containing success message for user or an error.
   */
   public function destroy(PackSize $packSize): JsonResponse {
      $this->authorize('delete', PackSize::class);
      $packSize->delete();
      
      return $this->successResponse(
         null,
         'messages.destroy.success',
         ['attribute' => self::ENTITY]
      );
   }
}