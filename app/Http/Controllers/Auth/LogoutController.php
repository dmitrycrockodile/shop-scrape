<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class LogoutController extends BaseController {
   private const ENTITY = 'user';

   /**
     * Handles user unauthorization.
     *
     * @param Request $request
     *
     * @return JsonResponse A JSON response indicating success or failure.
   */
   public function logout(Request $request): JsonResponse {
      $user = $request->user();

      $user->tokens()->delete();

      return $this->successResponse(
         [],
         'auth.logout.success',
         ['attribute' => self::ENTITY]
      );
   }
}