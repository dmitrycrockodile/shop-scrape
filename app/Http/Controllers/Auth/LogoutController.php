<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{
    private const ENTITY = 'user';

    /**
     * Handles user unauthorization.
     *
     * @param Request $request
     *
     * @return JsonResponse A JSON response indicating success or failure.
     */
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     description="Logs the user out and deletes their authentication tokens. Requires authentication.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         description="No payload required. The action will log out the authenticated user."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged out successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized - Invalid token or not authenticated"),
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->tokens()->delete();

        return $this->successResponse(
            [],
            'auth.logout.success',
            ['attribute' => self::ENTITY]
        );
    }
}
