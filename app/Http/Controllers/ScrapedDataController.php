<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Response;
use App\Http\Requests\ScrapedData\StoreRequest;
use App\Service\ScrapedDataService;
use Illuminate\Http\JsonResponse;

class ScrapedDataController extends BaseController {
   protected ScrapedDataService $scrapedDataService;

   public function __construct(ScrapedDataService $scrapedDataService) {
      $this->scrapedDataService = $scrapedDataService;
   }

   // /**
   //  * Retrieves the scraped data.
   //  * 
   //  * @return JsonResponse A JSON response containing retrieved scraped datas.
   // */
   // public function index(): JsonResponse {
   //    try {
   //       $packSizes = PackSize::all();

   //       return $this->successResponse(PackSizeResource::collection($packSizes));
   //    } catch (\Exception $e) {
   //       Log::error('Failed to retrieve scraped datas: ' . $e->getMessage(), [
   //          'trace' => $e->getTraceAsString()
   //       ]);

   //       return $this->errorResponse('Failed to recieve scraped datas, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
   //    }
   // }

   /**
    * Stores the scraped data.
    * 
    * @param StoreRequest $request A request with scraped data.
    * 
    * @return JsonResponse A JSON response containing newly created scraped data or error info.
   */
   public function store(StoreRequest $request): JsonResponse {
      $data = $request->validated();

      $serviceResponse = $this->scrapedDataService->store($data);
      
      return $serviceResponse['success']
         ? $this->successResponse($serviceResponse['scrapedData'], $serviceResponse['message'], Response::HTTP_CREATED)
         : $this->errorResponse($serviceResponse['message'], $serviceResponse['error'], $serviceResponse['status']);
   } 

   // /**
   //  * Updates the scraped data according to new data.
   //  * 
   //  * @param StoreRequest $request A request with new scraped data data
   //  * @param PackSize $packSize Instance of the scraped data to update
   //  * 
   //  * @return JsonResponse A JSON response containing updated scraped data or error info.
   // */
   // public function update(StoreRequest $request, PackSize $packSize): JsonResponse {
   //    $data = $request->validated();

   //    try {
   //       $packSize->update($data);

   //       return $this->successResponse(new PackSizeResource($packSize), 'Successfully updated the scraped data!', Response::HTTP_CREATED);
   //    } catch (\Exception $e) {
   //       Log::error('Failed to create the scraped data: ' . $e->getMessage(), [
   //          'trace' => $e->getTraceAsString()
   //       ]);

   //       return $this->errorResponse('Failed to update the scraped data, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
   //    }   
   // } 

   // /**
   //  * Deletes the scraped data.
   //  * 
   //  * @param PackSize $packSize Instance of the scraped data to delete
   //  * 
   //  * @return JsonResponse A JSON response containing success message for user or an error.
   // */
   // public function destroy(PackSize $packSize): JsonResponse {
   //    try {
   //       $packSize->delete();
         
   //       return $this->successResponse('scraped data successfully deleted.');
   //    } catch (\Exception $e) {
   //       Log::error('Failed to delete the scraped data: ' . $e->getMessage(), [
   //          'trace' => $e->getTraceAsString()
   //       ]);
         
   //       return $this->errorResponse('Failed to delete the scraped data, please try again.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
   //    }
   // }
}