<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Retailer\AddProductsRequest;
use App\Http\Requests\Retailer\RetailerRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Retailer\RetailerResource;
use App\Models\Retailer;
use App\Service\RetailerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(path="/api/retailers")
 */
class RetailerController extends BaseController
{
    protected RetailerService $retailerService;
    private const ENTITY_KEY = 'retailer';

    public function __construct(RetailerService $retailerService)
    {
        $this->retailerService = $retailerService;
    }

    /**
     * Retrieves the retailers.
     * 
     * @return JsonResponse A JSON response containing retrieved retailers or error message.
     */
    /**
     * @OA\Get(
     *     path="/api/retailers",
     *     summary="Retrieve all retailers",
     *     description="Fetches a list of all retailers with their associated currencies.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RetailerResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): JsonResponse
    {
        $this->authorize('seeAll', Retailer::class);

        $retailers = Retailer::with('currency')->get();

        return $this->successResponse(
            RetailerResource::collection($retailers),
            'messages.index.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }

    /**
     * Retrieves the products for the specified retailer.
     * 
     * @param Retailer $retailer Instance of the retailer whose products we want to retrieve
     * 
     * @return JsonResponse A JSON response containing retailer products or error info.
     */
    /**
     * @OA\Get(
     *     path="/api/retailers/{retailer}/products",
     *     summary="Retrieve retailer's products",
     *     description="Fetches the list of products associated with a specific retailer.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retailer",
     *         in="path",
     *         required=true,
     *         description="ID of the retailer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProductResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getProducts(Request $request, Retailer $retailer): JsonResponse
    {
        $this->authorize('seeProducts', $retailer);

        $dataPerPage = $request->query('dataPerPage', 10);
        $page = $request->query('page', 2);
        $products = $retailer->products()->paginate(
            $dataPerPage,
            ['*'],
            'page',
            $page
        );
        $meta = [
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'links' => $products->toArray()['links'],
        ];

        return $this->successResponse(
            ProductResource::collection($products),
            'messages.index.success',
            ['attribute' => 'retailer\'s products'],
            Response::HTTP_OK,
            $meta
        );
    }

    /**
     * Retrieves the products for the specified retailer.
     * 
     * @param 
     * @param Retailer $retailer Instance of the retailer whose products we want to retrieve
     * 
     * @return JsonResponse A JSON response containing retailer products or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/retailers/{retailer}/products",
     *     summary="Add products to a retailer",
     *     description="Assigns a list of products to a specific retailer.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retailer",
     *         in="path",
     *         required=true,
     *         description="ID of the retailer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="List of product IDs to be assigned",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products assigned to retailer",
     *         @OA\JsonContent(ref="#/components/schemas/RetailerResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addProducts(AddProductsRequest $request, Retailer $retailer): JsonResponse
    {
        $this->authorize('addProducts', $retailer);

        $data = $request->validated();
        $products = $data['products'] ?? [];

        $serviceResponse = $this->retailerService->syncOrAttachProducts($retailer, $products);

        return $this->successResponse(
            $serviceResponse['retailer'],
            'messages.assign.success',
            ['assigned' => 'Product', 'attribute' => 'retailer']
        );
    }

    /**
     * Stores the retailer.
     * 
     * @param StoreRequest $request A request with retailer data
     * 
     * @return JsonResponse A JSON response containing newly created retailer or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/retailers",
     *     summary="Create a retailer",
     *     description="Stores a new retailer in the database.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Retailer 1", description="Title of the retailer"),
     *             @OA\Property(property="url", type="string", example="http://retailer1.com", description="URL of the retailer"),
     *             @OA\Property(property="currency_id", type="integer", example=1, description="Currency ID associated with the retailer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retailer created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RetailerResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(RetailerRequest $request): JsonResponse
    {
        $this->authorize('store', Retailer::class);

        $data = $request->validated();

        $serviceResponse = $this->retailerService->store($data);

        return $this->successResponse(
            $serviceResponse['retailer'],
            'messages.store.success',
            ['attribute' => self::ENTITY_KEY],
            Response::HTTP_CREATED
        );
    }

    /**
     * Updates the retailer according to new data.
     * 
     * @param RetailerRequest $request A request with new retailer data
     * @param Retailer $retailer Instance of the retailer to update
     * 
     * @return JsonResponse A JSON response containing updated retailer or error info.
     */
    /**
     * @OA\Put(
     *     path="/api/retailers/{retailer}",
     *     summary="Update a retailer",
     *     description="Updates an existing retailer by its ID.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retailer",
     *         in="path",
     *         required=true,
     *         description="ID of the retailer to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Retailer", description="Updated title of the retailer"),
     *             @OA\Property(property="url", type="string", example="http://new-retailer.com", description="Updated URL"),
     *             @OA\Property(property="currency_id", type="integer", example=2, description="Updated currency ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailer updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RetailerResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(RetailerRequest $request, Retailer $retailer): JsonResponse
    {
        $this->authorize('update', Retailer::class);

        $data = $request->validated();
        $serviceResponse = $this->retailerService->update($data, $retailer);

        return $this->successResponse(
            $serviceResponse['retailer'],
            'messages.update.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }

    /**
     * Deletes the retailer.
     * 
     * @param Retailer $retailer Instance of the retailer to delete
     * 
     * @return JsonResponse A JSON response containing success message for user or an error.
     */
    /**
     * @OA\Delete(
     *     path="/api/retailers/{retailer}",
     *     summary="Delete a retailer",
     *     description="Deletes a retailer by its ID.",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retailer",
     *         in="path",
     *         required=true,
     *         description="ID of the retailer to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Operation success status"),
     *             @OA\Property(property="message", type="string", example="Retailer deleted successfully.", description="Success message"),
     *             @OA\Property(property="data", type="object", example=null, description="Additional response data (empty for delete operation)")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Retailer not found")
     * )
     */
    public function destroy(Retailer $retailer): JsonResponse
    {
        $this->authorize('delete', Retailer::class);

        $retailer->delete();

        return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }
}
