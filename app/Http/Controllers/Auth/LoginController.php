<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class LoginController extends BaseController
{
    private const ENTITY = 'user';

    /** 
     * Login the user
     * 
     * @param LoginRequest $request
     * 
     * @return JsonResponse A JSON response with success or failure.
    */
    public function login(LoginRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $user = User::whereEmail($validated['email'])->first();

            if (!$user) {
                return $this->errorResponse(
                    'auth.login.not_found',
                    ['attribute' => self::ENTITY],
                    'No user record found.',
                    Response::HTTP_NOT_FOUND
                );
            }

            if (Hash::check($validated['password'], $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->remember_token = $token;
                $user->save();

                Auth::login($user);

                return $this->successResponse(
                    [
                        'id' => $user->id,
                        'email' => $user->email,
                        'token' => $token,
                    ],
                    'auth.login.success',
                    ['attribute' => self::ENTITY]
                );
            } else {
                return $this->errorResponse(
                    'auth.login.password_incorrect',
                    ['attribute' => self::ENTITY],
                    'Incorrect password.',
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } catch (QueryException $e) {
            Log::error('Failed to login the user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'auth.login.error',
                ['attribute' => self::ENTITY],
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            Log::error('Failed to login the user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'auth.login.error',
                ['attribute' => self::ENTITY],
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}