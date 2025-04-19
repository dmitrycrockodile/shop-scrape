<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\PathItem(path="/api/login")
 */
class LoginController extends BaseController
{
    private const ENTITY_KEY = 'user';

    /** 
     * Login the user
     * 
     * @param LoginRequest $request
     * 
     * @return JsonResponse A JSON response with success or failure.
     */
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Authenticates the user and returns the user's basic details upon successful login.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="test2@user.com"),
     *             @OA\Property(property="password", type="string", format="password", example="TE5T_user_passw0rd")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1, description="User ID"),
     *             @OA\Property(property="email", type="string", example="user@example.com", description="User email address")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Incorrect password"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::whereEmail($validated['email'])->first();

        if (Hash::check($validated['password'], $user->password)) {
            // $user->tokens()->delete();
            // $token = $user->createToken('auth_token')->plainTextToken;
            // $user->remember_token = $token;
            // $user->save();

            Auth::login($user);

            return $this->successResponse(
                new UserResource($user),
                'auth.login.success',
                ['attribute' => self::ENTITY_KEY]
            );
        } else {
            return $this->errorResponse(
                'auth.login.password_incorrect',
                ['attribute' => self::ENTITY_KEY],
                'Incorrect password.',
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
