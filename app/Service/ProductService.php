<?php

namespace App\Service;

use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Store a new product.
     *
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array
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
