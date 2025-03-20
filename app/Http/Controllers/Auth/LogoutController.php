<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class LogoutController extends BaseController {
   /**
     * Handles user unauthorization.
     * 
     * @param Request $request
     * 
     * @return JsonResponse A JSON response indicating success or failure.
    */
   public function logout(Request $request): JsonResponse {
      try {
         $user = $request->user();

         $user->tokens()->delete();

         return $this->successResponse([], 'Logged out successfully.');
      }catch (QueryException $e) {
         Log::error('Failed to logout the user: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse('Failed to logout the user.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      } catch (\Exception $e) {
         Log::error("Failed to logout the user: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse('Failed to logout the user.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }
}