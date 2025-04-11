<?php

namespace App\Http\Controllers;

use App\Exceptions\CsvImportExceptionHandler;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\ExportRequest;
use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Retailer\RetailerResource;
use App\Models\Product;
use App\Service\CsvExporter;
use App\Service\Product\ImportService;
use App\Service\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\PathItem(path="/api/products")
 */
class ProductController extends BaseController
{
    protected ProductService $productService;
    protected ImportService $importService;
    protected CsvExporter $csvExporter;

    private const ENTITY_KEY = 'product';

    public function __construct(ProductService $productService, ImportService $importService, CsvExporter $csvExporter)
    {
        $this->productService = $productService;
        $this->importService = $importService;
        $this->csvExporter = $csvExporter;
    }

    /**
     * Retrieves the products.
     * 
     * @param IndexRequest A request with pagination data (if provided)
     * 
     * @return JsonResponse A JSON response containing retrieved paginated products.
     */
    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Retrieve products",
     *     description="Fetches a list of products with optional pagination. Includes metadata such as current page, total items, and items per page.",
     *     tags={"Products"},
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
     *                 description="Retrieved products",
     *                 @OA\Items(ref="#/components/schemas/ProductResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $isSuperUser = $user->isSuperUser();

        if ($isSuperUser) {
            $products = Product::paginate(
                $data['dataPerPage'] ?? 100, 
                ['*'], 
                'page', 
                $data['page'] ?? 1
            );
        } else {
            $products = $user->products()->paginate(
                $data['dataPerPage'] ?? 100,
                ['*'],
                'page',
                $data['page'] ?? 1
            );
        }
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
            ['attribute' => self::ENTITY_KEY],
            Response::HTTP_OK,
            $meta
        );
    }

    /**
     * Retrieves the retailers for the specified product.
     * 
     * @param Product $product Instance of the product whose retailers we want to retrieve
     * 
     * @return JsonResponse A JSON response containing product retailers or error info.
     */
    /**
     * @OA\Get(
     *     path="/api/products/{product}/retailers",
     *     summary="Retrieve retailers for a product",
     *     description="Fetches the retailers associated with a specific product.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RetailerResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getRetailers(Product $product): JsonResponse
    {
        $user = auth()->user();

        if ($user->isSuperUser()) {
            $retailers = $product->retailers;
        } else {
            $userRetailerIds = $user->retailers()->pluck('retailers.id');

            $retailers = $product->retailers()->whereIn('retailers.id', $userRetailerIds)->get();
        }

        return $this->successResponse(
            RetailerResource::collection($retailers),
            'messages.index.success',
            ['attribute' => 'product retailers']
        );
    }

    /**
     * Stores the product.
     * 
     * @param ProductRequest $request A request with product data
     * 
     * @return JsonResponse A JSON response containing newly created product or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/products/store",
     *     summary="Create a new product",
     *     description="Stores a new product in the database.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Laptop", description="Title of the product"),
     *             @OA\Property(property="description", type="string", example="High-performance laptop", description="Description of the product"),
     *             @OA\Property(property="manufacturer_part_number", type="string", example="LAP12345", description="Manufacturer part number"),
     *             @OA\Property(property="pack_size_id", type="integer", example=2, description="ID of the associated pack size")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $serviceResponse = $this->productService->store($data, $user);

        return $serviceResponse['success']
            ? $this->successResponse(
                $serviceResponse['product'],
                'messages.store.success',
                ['attribute' => self::ENTITY_KEY],
                Response::HTTP_CREATED
            )
            : $this->errorResponse(
                'messages.store.error',
                ['attribute' => self::ENTITY_KEY],
                $serviceResponse['error'],
                $serviceResponse['status']
            );
    }

    /**
     * Updates the product according to new data.
     * 
     * @param ProductRequest $request A request with new product data
     * @param Product $product Instance of the product to update
     * 
     * @return JsonResponse A JSON response containing updated product or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/products/{product}",
     *     summary="Update a product",
     *     description="Updates an existing product by its ID.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Gaming Laptop", description="Updated title of the product"),
     *             @OA\Property(property="description", type="string", example="High-performance gaming laptop", description="Updated description"),
     *             @OA\Property(property="manufacturer_part_number", type="string", example="LAP67890", description="Updated part number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $serviceResponse = $this->productService->update($data, $product);

        return $this->successResponse(
            $serviceResponse['product'],
            'messages.update.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }

    public function uploadCSV(Request $request): JsonResponse
    {
        $file = $request->file('file');
        if (!$file) {
            CsvImportExceptionHandler::handleEmptyCsvException();
        }

        $path = $file->store('uploads');
        $fullPath = storage_path("app/{$path}");

        $this->importService->importProducts($fullPath);

        Storage::delete($path);

        return $this->successResponse(
            [],
            'messages.import.success',
            ['attribute' => self::ENTITY_KEY . 's'],
            Response::HTTP_OK
        );
    }

    /**
     * Exports the CSV file based on filters (date, retailers).
     * 
     * @param ExportRequest $request The request with start/end dates, retailer ids 
     * 
     * @return StreamedResponse|JsonResponse A streamed CSV file or a JSON response in case of errors.
     */
    public function export(ExportRequest $request) 
    {
        $data = $request->validated();
        $startDate = $data['startDate'] ? Carbon::parse($data['startDate'])->copy()->startOfDay() : null;
        $endDate = $data['endDate'] ? Carbon::parse($data['endDate'])->copy()->endOfDay() : Carbon::today()->endOfDay();
        
        ($startDate && $endDate)
        ? $fileName = "products_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv"
        : $fileName = "products.csv";
        
        $products = $this->productService->getByDataRangeAndRetailers($startDate, $endDate, $data['retailers']);

        return $this->csvExporter->export($products, $fileName);
    }

    /**
     * Deletes the product.
     * 
     * @param Product $product Instance of the product to delete
     * 
     * @return JsonResponse A JSON response containing success message for user or an error.
     */
    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     summary="Delete a product",
     *     description="Deletes a product by its ID.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Operation success status"),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully.", description="Success message"),
     *             @OA\Property(property="data", type="object", example=null, description="Additional response data (empty for delete operation)")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', Product::class);

        $product->delete();
        return $this->successResponse(
            null,
            'messages.destroy.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }
}
