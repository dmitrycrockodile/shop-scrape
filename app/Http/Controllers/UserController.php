<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Enums\UserRole;
use App\Models\User;
use App\Http\Controllers\BaseController;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\ManageRetailersRequest;
use App\Http\Resources\Retailer\RetailerResource;

class UserController extends BaseController {
   private const ENTITY = 'user';

   /**
    * Retrieves the regular users.
    * 
    * @return JsonResponse A JSON response containing retrieved regular users.
   */
   public function index(): JsonResponse {
      try {
         $users = User::where('role', UserRole::REGULAR_USER->value)
            ->with('retailers')
            ->get();

         return $this->successResponse(
            UserResource::collection($users),
            'messages.index.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve the users: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse(
            'messages.index.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }

   /**
    * Stores the user.
    * 
    * @param StoreRequest $request A request with user data
    * 
    * @return JsonResponse A JSON response containing newly created user or error info.
   */
   public function store(StoreRequest $request): JsonResponse {
      $data = $request->validated();

      try {
         $user = User::create($data);

         return $this->successResponse(
            new UserResource($user),
            'messages.store.success',
            ['attribute' => self::ENTITY],
            Response::HTTP_CREATED
         );
      } catch (\Exception $e) {
         Log::error('Failed to create the user: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse(
            'messages.store.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   } 

   /**
    * Updates the user according to new data.
    * 
    * @param UpdateRequest $request A request with new user data
    * @param User $user Instance of the user to update
    * 
    * @return JsonResponse A JSON response containing updated user or error info.
   */
   public function update(UpdateRequest $request, User $user): JsonResponse {
      $data = $request->validated();

      try {
         $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'password' => isset($validatedData['password']) ? bcrypt($validatedData['password']) : $user->password,
            'role' => $data['role'] ?? $user->role,
            'location' => $data['location'] ?? $user->location
         ]);

         return $this->successResponse(
            new UserResource($user),
            'messages.update.success',
            ['attribute' => self::ENTITY],
            Response::HTTP_CREATED
         );
      } catch (\Exception $e) {
         Log::error('Failed to create the user: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse(
            'messages.update.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }   
   } 

   /**
    * Assigns the retailers to the specific regular user.
    * 
    * @param ManageRetailersRequest $request A request with retailer ids 
    * @param User $user Instance of the user to assign
    * 
    * @return JsonResponse A JSON response containing updated user retailers list or error info.
   */
   public function assignRetailers(ManageRetailersRequest $request, User $user): JsonResponse {
      $data = $request->validated();

      try {
         if ($user->role->value === UserRole::SUPER_USER->value) {
            return $this->errorResponse(
               'messages.assign.not_allowed',
               ['assigned' => 'retailers', 'attribute' => self::ENTITY],
               "Attempted to assign retailers to a super user (ID: {$user->id})",
               Response::HTTP_FORBIDDEN
            );
         }

         $user->retailers()->syncWithoutDetaching($data['retailers']);

         return $this->successResponse(
            RetailerResource::collection($user->retailers),
            'messages.assign.success',
            ['assigned' => 'retailers', 'attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve the users: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse(
            'messages.assign.error',
            ['assigned' => 'retailers', 'attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }

   /**
    * Revoke the retailers from the specific regular user.
    * 
    * @param ManageRetailersRequest $request A request with retailer ids 
    * @param User $user Instance of the user to revoke
    * 
    * @return JsonResponse A JSON response containing updated user retailers list or error info.
   */
   public function revokeRetailers(ManageRetailersRequest $request, User $user): JsonResponse {
      $data = $request->validated();

      try {
         if ($user->role->value === UserRole::SUPER_USER->value) {
            return $this->errorResponse(
               'messages.revoke.not_allowed',
               ['revoked' => 'retailers', 'attribute' => self::ENTITY],
               "Attempted to revoke retailers from a super user (ID: {$user->id})",
               Response::HTTP_FORBIDDEN
            );
         }

         $user->retailers()->detach($data['retailers']);

         return $this->successResponse(
            RetailerResource::collection($user->retailers),
            'messages.revoke.success',
            ['revoked' => 'retailers', 'attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to retrieve the users: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse(
            'messages.revoke.error',
            ['revoked' => 'retailers', 'attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }


   /**
    * Deletes the user.
    * 
    * @param User $user Instance of the user to delete
    * 
    * @return JsonResponse A JSON response containing success message for user or an error.
   */
   public function destroy(User $user): JsonResponse {
      try {
         $user->delete();
         
         return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY]
         );
      } catch (\Exception $e) {
         Log::error('Failed to delete the user: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         
         return $this->errorResponse(
            'messages.destroy.error',
            ['attribute' => self::ENTITY],
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR
         );
      }
   }
}