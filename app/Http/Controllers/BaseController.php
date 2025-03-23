<?php 

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 *   title="APIs for Shop Scrape App",
 *   version="1.0.0",
 * ),
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * ),
*/
class BaseController {
   use AuthorizesRequests;

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
      int $statusCode = Response::HTTP_OK,
      ?array $meta = null
   ): JsonResponse {
      $response = [
         'success' => true,
         'message' => __($messageLocal, $placeholders),
         'data' => $data,
      ];

      if (!is_null($meta)) {
         $response['meta'] = $meta;
      }
      
      return response()->json($response, $statusCode);
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