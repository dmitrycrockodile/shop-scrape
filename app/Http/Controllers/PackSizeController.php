<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PackSize\IndexRequest;
use App\Http\Requests\PackSize\StoreRequest;
use App\Http\Resources\PackSize\PackSizeResource;
use App\Models\PackSize;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * @OA\PathItem(path="/api/pack-sizes")
 */
class PackSizeController extends BaseController
{
    private const ENTITY = 'pack size';

    /**
     * Retrieves the pack sizes.
     *
     * @param IndexRequest A request with pagination data (if provided)
     * 
     * @return JsonResponse A JSON response containing retrieved pack sizes.
     */
    /**
     * @OA\Post(
     *     path="/api/pack-sizes",
     *     summary="Retrieve pack sizes",
     *     description="Fetches a list of pack sizes with optional pagination. Includes metadata such as current page, total items, and items per page.",
     *     tags={"Pack Sizes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPerPage", type="integer", example=10, description="Number of items per page"),
     *             @OA\Property(property="page", type="integer", example=1, description="Page number for pagination")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully."),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 description="Pagination metadata",
     *                 @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
     *                 @OA\Property(property="per_page", type="integer", example=10, description="Number of items per page"),
     *                 @OA\Property(property="last_page", type="integer", example=5, description="Total number of pages"),
     *                 @OA\Property(property="total", type="integer", example=50, description="Total number of records")
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Retrieved pack sizes",
     *                 @OA\Items(ref="#/components/schemas/PackSizeResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $data = $request->validated();
        $packSizes = PackSize::paginate(
            $data['dataPerPage'] ?? 100,
            ['*'],
            'page',
            $data['page'] ?? 1
        );;
        $meta = [
            'current_page' => $packSizes->currentPage(),
            'per_page' => $packSizes->perPage(),
            'last_page' => $packSizes->lastPage(),
            'total' => $packSizes->total(),
        ];

        return $this->successResponse(
            PackSizeResource::collection($packSizes),
            'messages.index.success',
            ['attribute' => self::ENTITY],
            Response::HTTP_OK,
            $meta
        );
    }

    /**
     * Stores the pack size.
     * 
     * @param StoreRequest $request A request with pack size data
     * 
     * @return JsonResponse A JSON response containing newly created pack size or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/pack-sizes/store",
     *     summary="Store a pack size",
     *     description="Creates a new pack size.",
     *     tags={"Pack Sizes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Small", description="Name of the pack size"),
     *             @OA\Property(property="description", type="string", example="Small sized package", description="Description of the pack size")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pack size created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PackSizeResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $packSize = PackSize::create($data);

        return $this->successResponse(
            new PackSizeResource($packSize),
            'messages.store.success',
            ['attribute' => self::ENTITY],
            Response::HTTP_CREATED
        );
    }

    /**
     * Updates the pack size according to new data.
     * 
     * @param StoreRequest $request A request with new pack size data
     * @param PackSize $packSize Instance of the pack size to update
     * 
     * @return JsonResponse A JSON response containing updated pack size or error info.
     */
    /**
     * @OA\Put(
     *     path="/api/pack-sizes/{packSize}",
     *     summary="Update a pack size",
     *     description="Updates an existing pack size.",
     *     tags={"Pack Sizes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="packSize",
     *         in="path",
     *         required=true,
     *         description="ID of the pack size to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Medium", description="Updated name of the pack size"),
     *             @OA\Property(property="description", type="string", example="Medium sized package", description="Updated description of the pack size")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pack size updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PackSizeResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(StoreRequest $request, PackSize $packSize): JsonResponse
    {
        $this->authorize('update', PackSize::class);

        $data = $request->validated();
        $packSize->update($data);

        return $this->successResponse(
            new PackSizeResource($packSize),
            'messages.update.success',
            ['attribute' => self::ENTITY]
        );
    }

    /**
     * Deletes the pack size.
     * 
     * @param PackSize $packSize Instance of the pack size to delete
     * 
     * @return JsonResponse A JSON response containing success message for user or an error.
     */
    /**
     * @OA\Delete(
     *     path="/api/pack-sizes/{packSize}",
     *     summary="Delete a pack size",
     *     description="Deletes a pack size by ID.",
     *     tags={"Pack Sizes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="packSize",
     *         in="path",
     *         required=true,
     *         description="ID of the pack size to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pack size deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Operation success status"),
     *             @OA\Property(property="message", type="string", example="Pack size deleted successfully.", description="Success message"),
     *             @OA\Property(property="data", type="object", example=null, description="Additional response data (empty for delete operation)")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     * )
     */
    public function destroy(PackSize $packSize): JsonResponse
    {
        $this->authorize('delete', PackSize::class);
        $packSize->delete();

        return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY]
        );
    }
}
