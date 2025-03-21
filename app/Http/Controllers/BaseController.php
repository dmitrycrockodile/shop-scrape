<?php 

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseController {
   /**
    * Method for all controllers to handle successful JSON responses
    *
    * @param array $data The data client needs to recieve
    * @param string $messageLocal String with message localization
    * @param array $placeholders Array with placeholders to error message localization
    * @param int $statusCode The response status 
    *
    * @return JsonResponse The response with all data
   */
   protected function successResponse( 
      mixed $data = [], 
      string $messageLocal, 
      array $placeholders = [],
      int $statusCode = Response::HTTP_OK
   ): JsonResponse {
      return response()->json([
         'success' => true,
         'message' => __($messageLocal, $placeholders),
         'data' => $data,
      ], $statusCode);
   }

   /**
    * Method for all controllers to handle JSON responses with failed operations
    *
    * @param string $messageLocal String with error localization
    * @param array $placeholders Array with placeholders to error message localization
    * @param string $error Raw error message,
    * @param int $statusCode The response status 
    *
    * @return JsonResponse The response with error info
   */
   protected function errorResponse( 
      string $messageLocal,
      array $placeholders = [],
      string $error = 'An error occured.',
      int $statusCode = Response::HTTP_BAD_REQUEST 
   ): JsonResponse {
      return response()->json([
         'success' => false,
         'message' => __($messageLocal, $placeholders),
         'error' => $error
      ], $statusCode);
   }
}