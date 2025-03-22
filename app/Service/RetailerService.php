<?php

namespace App\Service;

use App\Http\Resources\Retailer\RetailerResource;
use Illuminate\Support\Facades\Storage;
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
      $this->checkAndStoreLogo($data);
      $retailer = Retailer::create($data);

      return $this->successResponse($retailer);
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
      $this->checkAndStoreLogo($data);
      $retailer->update($data);

      return $this->successResponse($retailer);
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
      $productIds = array_column($products, 'id');

      $existingProducts = $retailer->products()
         ->whereIn('products.id', $productIds)
         ->pluck('products.id')
         ->toArray();

      [$attachData, $syncData] = $this->prepareProductData($products, $existingProducts);
      
      if (!empty($syncData)) {
         $retailer->products()->syncWithoutDetaching($syncData);
      }
      if (!empty($attachData)) {
         $retailer->products()->attach($attachData);
      }

      return $this->successResponse($retailer);
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
    *
    * @return array
   */
   private function successResponse(Retailer $retailer): array {
      return [
         'success' => true,
         'retailer' => new RetailerResource($retailer)
      ];
   }
}