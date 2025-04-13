<?php

namespace App\Service\Product;

use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Retailer;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            DB::rollBack();

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

    /**
     * Retrieves scraped data within a specified date range.
     *
     * @param Carbon $startDate The start date in 'YYYY-MM-DD' format.
     * @param Carbon $endDate The end date in 'YYYY-MM-DD' format.
     * @param array $retailerIds Ids of the retailers to access
     * 
     * @return Collection A collection of scraped data entries.
     */
    public function getByDataRangeAndRetailers(?Carbon $startDate, ?Carbon $endDate, array $retailerIds): Collection
    {
        $accessibleRetailerIds = auth()->user()->accessibleRetailers()->pluck('retailers.id');
        
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
                $startDate = $startDate->startOfDay();
                $endDate = $endDate->endOfDay();
        
                $query->whereBetween('products.created_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                $startDate = $startDate->startOfDay();
                $query->where('products.created_at', '>=', $startDate);
            } elseif ($endDate) {
                $endDate = $endDate->endOfDay();
                $query->where('products.created_at', '<=', $endDate);
            }

        if (count($retailerIds)) {
            $query->whereIn('product_retailers.retailer_id', $retailerIds);
        }
        
        return $query->get();
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