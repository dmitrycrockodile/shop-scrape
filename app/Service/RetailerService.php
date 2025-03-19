<?php

namespace App\Service;

use App\Http\Resources\Retailer\RetailerResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Retailer;

class RetailerService {
   /**
    * Store a new retailer.
    *
    * @param array $data
    *
    * @return array
   */
   public function store(array $data): array {
      try {
         $this->checkAndStoreLogo($data);
         $retailer = Retailer::create($data);

         return $this->successResponse($retailer, 'Successfully created the retailer!');
      } catch (\Exception $e) {
         Log::error('Failed to store the retailer: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse($e->getMessage(), $e);
      }
   } 

   /**
    * Update the retailer.
    *
    * @param array $data
    * @param Retailer $retailer
    *
    * @return array
   */
   public function update(array $data, Retailer $retailer): array {
      try {
         $this->checkAndStoreLogo($data);
         $retailer->update($data);

         return $this->successResponse($retailer, 'Successfully updated the retailer!');
      } catch (\Exception $e) {
         Log::error('Failed to update the retailer: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return $this->errorResponse('Failed to update the retailer, please try again.', $e);
      }
   }

   /**
    * Syncs or attaches the products.
    *
    * @param Retailer $retailer to whom we sync/attach products
    * @param array $products that we want to sync/attach
    *
    * @return array
   */
   public function syncOrAttachProducts(Retailer $retailer, $products): array {
      try {
         $productIds = array_column($products, 'id');

         $existingProducts = $retailer->products()
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();

         [$attachData, $syncData] = $this->prepareProductData($products, $existingProducts);
         
         if (!empty($syncData)) {
            $retailer->products()->syncWithoutDetaching($syncData);
         }
         if (!empty($attachData)) {
            $retailer->products()->attach($attachData);
         }

         return $this->successResponse($retailer, 'Successfuly updated the products list.');
      } catch (\Exception $e) {
         Log::error('Failed to update the products list: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse($e->getMessage(), $e);
      }
   }

   /**
    * Check and stores the retailers logo.
    *
    * @param array $data
    *
    * @return void
   */
   private function checkAndStoreLogo(array &$data): void {
      if (isset($data['logo'])) {
         $data['logo'] = Storage::disk('public')->put('/images', $data['logo']);
      }
   }

   /**
    * Prepares arrays of the products to sync and attach.
    *
    * @param array $products
    * @param array $existingProducts
    *
    * @return void
   */
   private function prepareProductData(array $products, array $existingProducts): array {
      $attachData = [];
      $syncData = [];

      foreach ($products as $product) {
         $productData = [
            'product_url' => $product['url'],
            'updated_at' => now()
         ];

         if (in_array($product['id'], $existingProducts)) {
            $syncData[$product['id']] = $productData;
         } else {
            $attachData[$product['id']] = array_merge($productData, [
               'created_at' => now()
            ]);
         }
      }

      return [$attachData, $syncData];
   }

   /**
    * Success response formatting.
    *
    * @param Retailer $retailer
    * @param string $message
    * @return array
   */
    private function successResponse(Retailer $retailer, string $message): array {
      return [
         'success' => true,
         'retailer' => new RetailerResource($retailer),
         'message' => $message,
      ];
   }

   /**
    * Error response formatting.
    *
    * @param string $errorMessage
    * @param Exception $exception
    * @return array
   */
   private function errorResponse(string $errorMessage, \Exception $exception, int $statusCode = 500): array {
      Log::error($errorMessage, [
         'exception' => $exception->getMessage(),
         'trace' => $exception->getTraceAsString(),
      ]);

      return [
         'success' => false,
         'message' => $errorMessage,
         'error' => $exception->getMessage(),
         'status' => $statusCode,
      ];
   }
}