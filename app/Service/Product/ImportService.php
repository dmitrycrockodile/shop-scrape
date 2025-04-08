<?php

namespace App\Service\Product;

use App\Exceptions\CsvImportExceptionHandler;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRetailer;
use App\Models\Retailer;
use App\Service\CsvImporter;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportService
{
    /**
     * Imports the products from the csv files
     * 
     * @param string $filepath
     * 
     * @return array With statistics data and message
     */   
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

            list(
                $productsToCreate,
                $rawProductsToCreate,
                $productsToUpdate,
                $rawProductsToUpdate
            ) = $this->prepareProductData($records, $updatedPackSizes);

            $createdProductsCount = $this->bulkStore($productsToCreate, $rawProductsToCreate, $existingRetailers);
            $updatedProductsCount = $this->bulkUpdate($productsToUpdate, $rawProductsToUpdate, $existingRetailers);

            DB::commit();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            $memoryUsed = memory_get_usage() - $initialMemoryUsage;
            $memoryUsedInMB = $memoryUsed / 1048576;

            return [
                'message' => 'CSV processed successfully!',
                'created' => $createdProductsCount,
                'updated' => $updatedProductsCount,
                'execution_time' => $executionTime * 1000 . ' ms',
                'memory_used' => $memoryUsedInMB . ' MB',
                'row_count' => $numberOfRows,
            ];
        } catch (Throwable $e) {
            CsvImportExceptionHandler::handleImportException($e);
        }
    }

    /**
     * Bulk stores the products
     * 
     * @param array $products
     * @param array $rawData
     * @param array $existingRetailers
     * 
     * @return int count of the created products
     */
    private function bulkStore(array $products, array $rawData, array $existingRetailers): int
    {
        Product::insert($products);

        $productMap = $this->buildProductLookupMap($rawData);
        list($productImages, $productRetailers) = $this->prepareRelatedData($rawData, $existingRetailers, $productMap);

        if (!empty($productImages)) {
            ProductImage::insert($productImages);
        }
        if (!empty($productRetailers)) {
            ProductRetailer::insert($productRetailers);
        }

        return count($products);
    }

    /**
     * Bulk updates the products
     * 
     * @param array $products
     * @param array $rawData
     * @param array $existingRetailers
     * 
     * @return int Count of the updated products
     */
    private function bulkUpdate(array $products, array $rawData, array $existingRetailers): int
    {
        $productIds = array_filter(array_column($rawData, 'product_id'));
        list($productImages, $productRetailers) = $this->prepareRelatedDataToUpdate($rawData, $existingRetailers);

        ProductImage::whereIn('product_id', $productIds)->delete();
        ProductImage::insert($productImages);

        ProductRetailer::whereIn('product_id', $productIds)->delete();
        ProductRetailer::insert($productRetailers);

        foreach ($products as $product) {
            if (!empty($product['id'])) {
                Product::where('id', $product['id'])->update($product);
            }
        }

        return count($products);
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
        $productsToCreate = [];
        $productsToUpdate = [];
        $rawProductsToCreate = [];
        $rawProductsToUpdate = [];

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

            if (strtolower($record['action']) === 'create') {
                $productsToCreate[] = $productData;
                $rawProductsToCreate[] = array_merge($productData, [
                    'image_urls' => $record['image_urls'] ?? null,
                    'file_name' => $record['image_name'] ?? null,
                    'product_url' => $record['product_url'] ?? null,
                    'retailer_title' => $record['retailer_title'] ?? null,
                ]);
            }
            if (strtolower($record['action']) === 'update') {
                $productData['id'] = $record['product_id'];
                $productsToUpdate[] = $productData;
                $rawProductsToUpdate[] = array_merge($productData, [
                    'image_urls' => $record['image_urls'] ?? null,
                    'file_name' => $record['image_name'] ?? null,
                    'product_url' => $record['product_url'] ?? null,
                    'retailer_title' => $record['retailer_title'] ?? null,
                ]);
            }
        }

        return [$productsToCreate,  $rawProductsToCreate, $productsToUpdate, $rawProductsToUpdate];
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
     * Fetch existing pack sizes.
     *
     * @return array An array of pack sizes (each as an associative array).
     */
    private function getExistingPackSizes(): array
    {
        return PackSize::all()->toArray();
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
     * Prepare related data for product images and product-retailer relationships on update.
     *
     * @param array $rawProductData
     * @param array $existingRetailers
     * 
     * @return array
     */
    private function prepareRelatedDataToUpdate(array $rawProductData, array $existingRetailers): array
    {
        $productImages = [];
        $productRetailers = [];

        foreach ($rawProductData as $data) {
            $productId = $data['product_id'] ?? null;

            if ($productId) {
                if (isset($data['image_urls'])) {
                    $imageUrls = array_map('trim', explode('|', $data['image_urls']));
                    foreach ($imageUrls as $imageUrl) {
                        $productImages[] = [
                            'product_id' => $productId,
                            'file_url' => $imageUrl,
                            'file_name' => $data['file_name'] ?? "{$data['title']} image",
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
                        'product_url' => $data['product_url'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return [$productImages, $productRetailers];
    }
}
