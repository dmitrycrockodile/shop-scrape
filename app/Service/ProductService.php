<?php

namespace App\Service;

use App\Exceptions\CsvImportExceptionHandler;
use App\Http\Resources\Product\ProductResource;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Models\User;
use App\Service\CsvImporter;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Carbon;

class ProductService
{
    /**
     * Store a new product.
     *
     * @param array $data
     *
     * @return array
     */
    public function store(array $data, User $user): array
    {
        DB::beginTransaction();

        $existingProduct = Product::where('manufacturer_part_number', $data['manufacturer_part_number'])
            ->where('pack_size_id', $data['pack_size_id'])
            ->first();

        if ($existingProduct) {
            return $this->errorResponse(
                'Product with this MPN (Manufacturer Part Number) and pack size (id) already exists.',
                new \Exception('Duplicate product entry'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $images = $this->extractImages($data);
        $product = Product::create($data);

        $user->products()->attach($product->id);

        if ($images) {
            $this->storeProductImages($images, $product);
        }

        DB::commit();
        return $this->successResponse($product);
    }

    /**
     * Update the product.
     *
     * @param array $data
     * @param Product $product
     *
     * @return array
     */
    public function update(array $data, Product $product): array
    {
        DB::beginTransaction();

        $images = $this->extractImages($data);
        $product->update($data);

        if ($images) {
            ProductImage::where('product_id', $product->id)->delete();
            $this->storeProductImages($images, $product);
        }

        DB::commit();
        return $this->successResponse($product);
    }

    public function importProducts(string $filepath): array
    {
        DB::beginTransaction();

        try {
            $startTime = microtime(true);
            $initialMemoryUsage = memory_get_usage();

            $importer = new CsvImporter();
            $records = $importer->import($filepath);
            if (empty($records)) {
                CsvImportExceptionHandler::handleInvalidCsvException();
            }

            $numberOfRows = count($records);

            $existingRetailers = Retailer::pluck('id', 'title')->toArray();
            $this->checkIfRetailersExist($records, $existingRetailers);

            $existingPackSizes = $this->getExistingPackSizes();
            $updatedPackSizes = $this->findAndInsertMissingPackSizes($records, $existingPackSizes);

            list($products, $rawProductData) = $this->prepareProductData($records, $updatedPackSizes);

            Product::insert($products);

            $productMap = $this->buildProductLookupMap($rawProductData);

            list($productImages, $productRetailers) = $this->prepareRelatedData($rawProductData, $existingRetailers, $productMap);

            if (!empty($productImages)) {
                ProductImage::insert($productImages);
            }
            if (!empty($productRetailers)) {
                ProductRetailer::insert($productRetailers);
            }

            DB::commit();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            $memoryUsed = memory_get_usage() - $initialMemoryUsage;
            $memoryUsedInMB = $memoryUsed / 1048576;

            return [
                'message' => 'CSV processed successfully!',
                'products' => $products,
                'execution_time' => $executionTime * 1000 . ' ms',
                'memory_used' => $memoryUsedInMB . ' MB',
                'row_count' => $numberOfRows,      
            ];
        } catch (Throwable $e) {
            CsvImportExceptionHandler::handleImportException($e);
        }
    }

    /**
     * Retrieves scraped data within a specified date range.
     *
     * @param string $startDate The start date in 'YYYY-MM-DD' format.
     * @param string $endDate The end date in 'YYYY-MM-DD' format.
     * 
     * @return Collection A collection of scraped data entries.
     */
    public function getByDataRangeAndRetailers(?Carbon $startDate, ?Carbon $endDate, array $retailerIds): Collection
    {
        $accessibleRetailerIds = auth()->user()->retailers()->pluck('retailers.id');

        $query = Product::join('product_retailers', 'products.id', '=', 'product_retailers.product_id')
        ->join('pack_sizes', 'products.pack_size_id', '=', 'pack_sizes.id')
        ->join('retailers', 'product_retailers.retailer_id', '=', 'retailers.id')
        ->leftJoin('product_images', 'products.id', '=', 'product_images.product_id')
        ->whereIn('product_retailers.retailer_id', $accessibleRetailerIds)
        ->selectRaw("
            products.title as title,
            products.description as description,
            products.manufacturer_part_number as manufacturer_part_number,
            pack_sizes.name as pack_size_name,
            pack_sizes.weight as pack_size_weight,
            pack_sizes.weight_unit as pack_size_weight_unit,
            pack_sizes.amount as pack_size_amount,
            GROUP_CONCAT(DISTINCT retailers.title SEPARATOR '|') as retailer_titles,
            GROUP_CONCAT(DISTINCT product_images.file_url SEPARATOR '|') as image_urls,
            GROUP_CONCAT(DISTINCT product_images.file_name SEPARATOR '|') as image_names
        ")
        ->groupBy('products.id', 'pack_sizes.id');

        if ($startDate && $endDate) {
            $query->whereBetween('products.created_at', [$startDate, $endDate]);
        } 

        if (count($retailerIds)) {
            $query->whereIn('product_retailers.retailer_id', $retailerIds);
        }

        return $query->get();
    }

    /**
     * Check that all retailers referenced in the records exist.
     * Throws an Exception if any retailer is missing.
     *
     * @param array $records
     * @param array $existingRetailers
     * 
     * @return void
     */
    private function checkIfRetailersExist(array $records, array $existingRetailers): void
    {
        $missingRetailers = [];

        foreach ($records as $record) {
            $retailerTitle = $record['retailer_title'] ?? null;
            if ($retailerTitle && !isset($existingRetailers[$retailerTitle])) {
                $missingRetailers[$retailerTitle] = $retailerTitle;
            }
        }

        if (!empty($missingRetailers)) {
            CsvImportExceptionHandler::handleMissingRetailersException($missingRetailers);
        }
    }

    /**
     * Find missing pack sizes from the CSV records and bulk-insert them.
     *
     * @param array $records
     * @param array $existingPackSizes
     * 
     * @return array Updated pack sizes list 
     */
    private function findAndInsertMissingPackSizes(array $records, array $existingPackSizes): array
    {
        $uniquePackSizes = [];

        foreach ($records as $record) {
            $packSizeName = $record['pack_size_name'] ?? null;
            $packSizeWeight = $record['pack_size_weight'] ?? null;
            $packSizeWeightUnit = $record['pack_size_weight_unit'] ?? null;
            $packSizeAmount = $record['pack_size_amount'] ?? null;

            if ($packSizeName && $packSizeWeight && $packSizeWeightUnit && $packSizeAmount) {
                $uniquePackSizes[] = [
                    'name' => $packSizeName,
                    'weight' => $packSizeWeight,
                    'weight_unit' => $packSizeWeightUnit,
                    'amount' => $packSizeAmount
                ];
            }
        }

        $uniquePackSizes = array_unique($uniquePackSizes, SORT_REGULAR);

        $existingPackSizeKeys = [];
        foreach ($existingPackSizes as $packSize) {
            $key = "{$packSize['name']}-{$packSize['weight']}-{$packSize['weight_unit']}-{$packSize['amount']}";
            $existingPackSizeKeys[$key] = $packSize['id'];
        }

        $newPackSizes = [];
        foreach ($uniquePackSizes as $packSize) {
            $key = "{$packSize['name']}-{$packSize['weight']}-{$packSize['weight_unit']}-{$packSize['amount']}";
            if (!isset($existingPackSizeKeys[$key])) {
                $newPackSizes[] = array_merge($packSize, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if (!empty($newPackSizes)) {
            PackSize::insert($newPackSizes);
        }

        return $this->getExistingPackSizes();
    }

    /**
     * Fetch existing pack sizes.
     *
     * @return array An array of pack sizes (each as an associative array).
     */
    private function getExistingPackSizes(): array
    {
        return PackSize::all()->toArray();
    }

    /**
     * Prepare product data for bulk insertion.
     *
     * Returns an array with two elements:
     *  - [0] Products array (for the products table).
     *  - [1] Raw product data array (includes extra fields for linking related data).
     *
     * @param array $records
     * @param array $packSizes
     * 
     * @return array
     */
    private function prepareProductData(array $records, array $packSizes): array
    {
        $products = [];
        $rawProductData = [];

        foreach ($records as $record) {
            $packSize = collect($packSizes)->firstWhere(function ($pack) use ($record) {
                return $pack['name'] === ($record['pack_size_name'] ?? null) &&
                    $pack['weight'] == ($record['pack_size_weight'] ?? null) &&
                    $pack['weight_unit'] === ($record['pack_size_weight_unit'] ?? null) &&
                    $pack['amount'] == ($record['pack_size_amount'] ?? null);
            });
            $packSizeId = $packSize['id'] ?? null;

            $productData = [
                'title' => $record['title'] ?? null,
                'description' => $record['description'] ?? null,
                'manufacturer_part_number' => $record['manufacturer_part_number'] ?? null,
                'pack_size_id' => $packSizeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $products[] = $productData;

            $rawProductData[] = array_merge($productData, [
                'image_urls' => $record['image_urls'] ?? null,
                'file_name' => $record['image_name'] ?? null,
                'product_url' => $record['product_url'] ?? null,
                'retailer_title' => $record['retailer_title'] ?? null,
            ]);
        }

        return [$products,  $rawProductData];
    }

    /**
     * After inserting products, build a lookup map that maps the combination of
     * manufacturer_part_number and pack_size_id to the product's ID.
     *
     * @param array $rawProductData
     * @return array
     */
    private function buildProductLookupMap(array $rawProductData): array
    {
        $mpns = array_column($rawProductData, 'manufacturer_part_number');
        $packSizeIds = array_column($rawProductData, 'pack_size_id');
        $insertedProducts = Product::whereIn('manufacturer_part_number', $mpns)
            ->whereIn('pack_size_id', $packSizeIds)
            ->get();

        $productMap = [];
        foreach ($insertedProducts as $product) {
            $key = $product->manufacturer_part_number . '-' . $product->pack_size_id;
            $productMap[$key] = $product->id;
        }
        return $productMap;
    }

    /**
     * Prepare related data for product images and product-retailer relationships.
     *
     * @param array $rawProductData
     * @param array $existingRetailers
     * @param array $productMap
     * 
     * @return array
     */
    private function prepareRelatedData(array $rawProductData, array $existingRetailers, array $productMap): array
    {
        $productImages = [];
        $productRetailers = [];

        foreach ($rawProductData as $data) {
            $mpn = $data['manufacturer_part_number'] ?? null;
            $packSizeId = $data['pack_size_id'] ?? null;
            $productKey = $mpn . '-' . $packSizeId;
            $productId = $productMap[$productKey];

            if (isset($data['image_urls'])) {
                $imageUrls = array_map('trim', explode('|', $data['image_urls']));
                foreach ($imageUrls as $imageUrl) {
                    $productImages[] = [
                        'product_id' => $productId,
                        'file_url' => $imageUrl,
                        'file_name' => $data['file_name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $retailerTitle = $data['retailer_title'] ?? null;
            if ($retailerTitle && isset($existingRetailers[$retailerTitle])) {
                $productRetailers[] = [
                    'product_id' => $productId,
                    'retailer_id' => $existingRetailers[$retailerTitle],
                    'product_url' => $data['product_url'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return [$productImages, $productRetailers];
    }

    /**
     * Store images for a product.
     *
     * @param array $images
     * @param Product $product
     *
     * @return void
     */
    private function storeProductImages(array $images, Product $product): void
    {
        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $file_url = Storage::disk('public')->put('/images', $image);
            } else {
                $file_url = $image;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'file_url' => $file_url,
                'file_name' => "{$product->title} image"
            ]);
        }
    }

    /**
     * Extract images from data.
     *
     * @param array $data Data from which we remove images
     * 
     * @return array|null
     */
    private function extractImages(array &$data): ?array
    {
        $images = $data['images'] ?? [];
        $imageUrls = isset($data['image_urls']) ? json_decode($data['image_urls'], true) : [];

        unset($data['images'], $data['image_urls']);

        return array_merge($images, $imageUrls);
    }

    /**
     * Success response formatting.
     *
     * @param Product $product
     *
     * @return array
     */
    private function successResponse(Product $product): array
    {
        return [
            'success' => true,
            'product' => new ProductResource($product)
        ];
    }

    /**
     * Error response formatting.
     *
     * @param string $errorMessage
     * @param Exception $exception
     * @return array
     */
    private function errorResponse(string $errorMessage, \Exception $exception, int $statusCode = 500): array
    {
        Log::error($errorMessage, [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'error' => $exception->getMessage(),
            'status' => $statusCode,
        ];
    }
}