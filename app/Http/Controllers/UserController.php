<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\ManageRetailersRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\Retailer\RetailerResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\PathItem(path="/api/users")
 */
class UserController extends BaseController
{
    private const ENTITY_KEY = 'user';

    /**
     * Retrieves the regular users.
     * 
     * @return JsonResponse A JSON response containing retrieved regular users.
     */
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Retrieve regular users",
     *     description="Fetches a list of all regular users with their associated retailers.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $users = User::where('role', UserRole::REGULAR_USER->value)
            ->with('retailers')
            ->get();

        return $this->successResponse(
            UserResource::collection($users),
            'messages.index.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }

    /**
     * Stores the user.
     * 
     * @param StoreRequest $request A request with user data
     * 
     * @return JsonResponse A JSON response containing newly created user or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Store a new user",
     *     description="Creates a new user and saves it in the database.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe", description="Name of the user"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="Email address of the user"),
     *             @OA\Property(property="password", type="string", example="securepassword", description="Password for the user"),
     *             @OA\Property(property="role", type="string", example="REGULAR_USER", description="Role of the user"),
     *             @OA\Property(property="location", type="string", example="New York", description="Location of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $data = $request->validated();

        $user = User::create($data);

        return $this->successResponse(
            new UserResource($user),
            'messages.store.success',
            ['attribute' => self::ENTITY_KEY],
            Response::HTTP_CREATED
        );
    }

    /**
     * Updates the user according to new data.
     * 
     * @param UpdateRequest $request A request with new user data
     * @param User $user Instance of the user to update
     * 
     * @return JsonResponse A JSON response containing updated user or error info.
     */
    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     summary="Update a user",
     *     description="Updates an existing user by its ID.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe", description="Updated name of the user"),
     *             @OA\Property(property="email", type="string", example="janedoe@example.com", description="Updated email address of the user"),
     *             @OA\Property(property="password", type="string", example="newsecurepassword", description="Updated password"),
     *             @OA\Property(property="role", type="string", example="REGULAR_USER", description="Updated role of the user"),
     *             @OA\Property(property="location", type="string", example="San Francisco", description="Updated location of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $data = $request->validated();

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
            ['attribute' => self::ENTITY_KEY]
        );
    }

    /**
     * Assigns the retailers to the specific regular user.
     * 
     * @param ManageRetailersRequest $request A request with retailer ids 
     * @param User $user Instance of the user to assign
     * 
     * @return JsonResponse A JSON response containing updated user retailers list or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/users/{user}/assign-retailers",
     *     summary="Assign retailers to a user",
     *     description="Assigns a list of retailers to a specific user.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to assign retailers to",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="retailers",
     *                 type="array",
     *                 description="List of retailer IDs to assign",
     *                 @OA\Items(type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailers assigned successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RetailerResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function assignRetailers(ManageRetailersRequest $request, User $user): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $data = $request->validated();

        if ($user->role->value === UserRole::SUPER_USER->value) {
            return $this->errorResponse(
                'messages.assign.not_allowed',
                ['assigned' => 'retailers', 'attribute' => self::ENTITY_KEY],
                "Attempted to assign retailers to a super user (ID: {$user->id})",
                Response::HTTP_FORBIDDEN
            );
        }

        $retailerIds = collect($data['retailers'])->pluck('id')->all();
        $user->retailers()->syncWithoutDetaching($retailerIds);

        return $this->successResponse(
            RetailerResource::collection($user->retailers),
            'messages.assign.success',
            ['assigned' => 'retailers', 'attribute' => self::ENTITY_KEY]
        );
    }

    /**
     * Revoke the retailers from the specific regular user.
     * 
     * @param ManageRetailersRequest $request A request with retailer ids 
     * @param User $user Instance of the user to revoke
     * 
     * @return JsonResponse A JSON response containing updated user retailers list or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/users/{user}/revoke-retailers",
     *     summary="Revoke retailers from a user",
     *     description="Removes a list of retailers from a specific user.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to revoke retailers from",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="retailers",
     *                 type="array",
     *                 description="List of retailer IDs to revoke",
     *                 @OA\Items(type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailers revoked successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RetailerResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function revokeRetailers(ManageRetailersRequest $request, User $user): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $data = $request->validated();

        if ($user->role->value === UserRole::SUPER_USER->value) {
            return $this->errorResponse(
                'messages.revoke.not_allowed',
                ['revoked' => 'retailers', 'attribute' => self::ENTITY_KEY],
                "Attempted to revoke retailers from a super user (ID: {$user->id})",
                Response::HTTP_FORBIDDEN
            );
        }

        $user->retailers()->detach($data['retailers']);

        return $this->successResponse(
            RetailerResource::collection($user->retailers),
            'messages.revoke.success',
            ['revoked' => 'retailers', 'attribute' => self::ENTITY_KEY]
        );
    }


    /**
     * Deletes the user.
     * 
     * @param User $user Instance of the user to delete
     * 
     * @return JsonResponse A JSON response containing success message for user or an error.
     */
    /**
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     summary="Delete a user",
     *     description="Deletes a user by its ID.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Operation success status"),
     *             @OA\Property(property="message", type="string", example="User deleted successfully.", description="Success message"),
     *             @OA\Property(property="data", type="object", example=null, description="Additional response data")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('manageUsers', User::class);

        $user->delete();

        return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }
}
