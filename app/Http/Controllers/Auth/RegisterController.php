<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RegisterController extends BaseController
{
    /**
     * Register the user
     * 
     * @param RegisterRequest $request
     * 
     * @return JsonResponse A JSON response with success or failure.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return $this->successResponse(
                $user['email'], 
                'User registered successfully.', 
                Response::HTTP_CREATED
            );
        } catch (QueryException $e) {
            Log::error('Failed to register the user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Failed to register the user.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error('Failed to register the user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Failed to register the user.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}