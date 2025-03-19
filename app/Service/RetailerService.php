<?php

namespace App\Service;

use App\Http\Resources\Retailer\RetailerResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Retailer;

class RetailerService {
   /**
    * Store a new retailer.
    *
    * @param array $data
    *
    * @return array
    */
   public function store(array $data): array {
      try {
         $this->checkAndStoreLogo($data);
         $retailer = Retailer::create($data);

         return $this->successResponse($retailer, 'Successfully created the retailer!');
      } catch (\Exception $e) {
         Log::error('Failed to store the retailer: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse($e->getMessage(), $e);
      }
   } 

   /**
    * Update the retailer.
    *
    * @param array $data
    * @param Retailer $retailer
    *
    * @return array
    */
   public function update(array $data, Retailer $retailer): array {
      try {
         $this->checkAndStoreLogo($data);
         $retailer->update($data);

         return $this->successResponse($retailer, 'Successfully updated the retailer!');
      } catch (\Exception $e) {
         Log::error('Failed to update the retailer: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return $this->errorResponse('Failed to update the retailer, please try again.', $e);
      }
   }

   /**
    * Check and stores the retailers logo.
    *
    * @param array $data
    *
    * @return void
   */
   private function checkAndStoreLogo(array &$data): void {
      if (isset($data['logo'])) {
         $data['logo'] = Storage::disk('public')->put('/images', $data['logo']);
      }
   }

   /**
    * Success response formatting.
    *
    * @param Retailer $retailer
    * @param string $message
    * @return array
    */
    private function successResponse(Retailer $retailer, string $message): array {
      return [
         'success' => true,
         'retailer' => new RetailerResource($retailer),
         'message' => $message,
      ];
   }

   /**
    * Error response formatting.
    *
    * @param string $errorMessage
    * @param Exception $exception
    * @return array
    */
   private function errorResponse(string $errorMessage, \Exception $exception, int $statusCode = 500): array {
      Log::error($errorMessage, [
         'exception' => $exception->getMessage(),
         'trace' => $exception->getTraceAsString(),
      ]);

      return [
         'success' => false,
         'message' => $errorMessage,
         'error' => $exception->getMessage(),
         'status' => $statusCode,
      ];
   }
}