<?php

namespace App\Http\Resources\Metric;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class MetricResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */
   public function toArray(Request $request): array
   {
      return [
         'Retailer ID' => $this->retailer_id,
         'Retailer title' => $this->retailer_title,
         'Product ID' => $this->product_id,
         'Product title' => $this->product_title,
         'Average rating' => round($this->avg_rating, 2),
         'Average price' => round($this->avg_price, 2),
         'Average images count' => round($this->avg_images, 2),
         'Date' => $this->getDateRange($request)
      ];
   }

   private function getDateRange($request) {
      $startDate = $request->has('start_date') ? Carbon::parse($request->input('start_date'))->toDateString() : null;
      $endDate = $request->has('end_date') ? Carbon::parse($request->input('end_date'))->toDateString() : null;

      if ($startDate && $endDate) {
         return Carbon::parse($startDate)->toDateString() . ' - ' . Carbon::parse($endDate)->toDateString();
      }

      if ($startDate) {
         return Carbon::parse($startDate)->toDateString();
      }

      if ($endDate) {
         return Carbon::parse($endDate)->toDateString();
      }

      return null;
   }
}
