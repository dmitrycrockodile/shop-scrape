<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Response;
use App\Http\Requests\ScrapedData\StoreRequest;
use App\Service\ScrapedDataService;
use Illuminate\Http\JsonResponse;

class ScrapedDataController extends BaseController {
   protected ScrapedDataService $scrapedDataService;

   private const ENTITY = 'scraped data';

   public function __construct(ScrapedDataService $scrapedDataService) {
      $this->scrapedDataService = $scrapedDataService;
   }

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
         ? $this->successResponse(
            $serviceResponse['scrapedData'],
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
}