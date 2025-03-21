<?php

namespace App\Service;

use App\Http\Resources\ScrapedData\ScrapedDataResource;
use App\Models\Rating;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ProductRetailer;
use Illuminate\Http\Response;

class ScrapedDataService {
   /**
    * Store new scraped data.
    *
    * @param array $data
    *
    * @return array
    */
   public function store(array $data): array {
      try {
         $productRetailer = ProductRetailer::whereHas('product', function ($query) use ($data) {
            $query->where('manufacturer_part_number', $data['mpn']);
         })->where('id', $data['product_retailer_id'])->first();
   
         if (!$productRetailer) {
            return $this->errorResponse(
               'Product Retailer with provided MPN not found.', 
               new \Exception('Product Retailer with provided MPN not found.'),
               Response::HTTP_NOT_FOUND
            );
         }

         DB::beginTransaction();

         $images = $this->extractImages($data);
         $ratings = $this->extractRatings($data);
         $scrapedData = ScrapedData::create($data);

         if ($images) {
            $this->storeScrapedDataImages($images, $scrapedData);
         }
         if ($ratings) {
            $this->storeScrapedDataRatings($ratings, $scrapedData);
         }

         DB::commit();
         return $this->successResponse($scrapedData, 'Successfully stored scriped data!');
      } catch (\Exception $e) {
         DB::rollBack();

         Log::error('Failed to store the scriped data: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return $this->errorResponse($e->getMessage(), $e);
      }
   } 

   /**
    * Store images for the scraped data.
    *
    * @param array $images
    * @param ScrapedData $scrapedData
    *
    * @return void
   */
   private function storeScrapedDataImages(array $images, ScrapedData $scrapedData): void {
      foreach ($images as $image) {
         $file_url = Storage::disk('public')->put('/images', $image['url']);

         ScrapedDataImage::create([
            'scraped_data_id' => $scrapedData->id,
            'file_url' => $file_url,
            'file_name' => $image['name'],
            'position' => $image['position'],
         ]);
      }
   }

   /**
    * Store ratings for the scraped data.
    *
    * @param array $ratings
    * @param ScrapedData $scrapedData
    *
    * @return void
   */
   private function storeScrapedDataRatings(array $ratings, ScrapedData $scrapedData): void {
      Rating::create([
         'scraped_data_id' => $scrapedData->id, 
         'one_star' => $ratings['one_star'],
         'two_stars' => $ratings['two_stars'],
         'three_stars' => $ratings['three_stars'],
         'four_stars' => $ratings['four_stars'],
         'five_stars' => $ratings['five_stars'],
      ]);
   }

   /**
     * Extract images from data.
     *
     * @param array $data Data from which we remove images
     * 
     * @return array|null
   */
   private function extractImages(array &$data): ?array {
      $images = $data['images'] ?? null;
      unset($data['images']);

      return $images;
   }

   /**
     * Extract ratings from data.
     *
     * @param array $data Data from which we remove ratings
     * 
     * @return array|null
   */
   private function extractRatings(array &$data): ?array {
      
      $ratings = $data['ratings'] ?? null;
      unset($data['ratings']);

      return $ratings;
   }

   /**
    * Success response formatting.
    *
    * @param ScrapedData $scrapedData
    * @param string $message
    * @return array
    */
    private function successResponse(ScrapedData $scrapedData, string $message): array {
      return [
         'success' => true,
         'scrapedData' => new ScrapedDataResource($scrapedData),
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