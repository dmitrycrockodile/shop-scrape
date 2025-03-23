<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Metrics\RetailerMetricsRequest;
use App\Http\Resources\Metric\MetricResource;
use App\Models\Retailer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\PathItem(path="/api/retailers/metrics")
*/
class MetricsController extends BaseController {
   private const ENTITY = 'metrics';

   /**
    * Retrieves the retailers.
    *
    * @param RetailerMetricsRequest $request Request with pagination and filters data (If provided)
    * 
    * @return JsonResponse A JSON response containing retrieved retailer metrics or error message.
   */
   /**
    * @OA\Post(
    *     path="/api/retailers/metrics",
    *     summary="Retrieve retailer metrics",
    *     description="Fetches retailer metrics based on various filters such as product IDs, manufacturer part numbers, and retailer IDs. Requires authentication.",
    *     tags={"Metrics"},
    *     security={{"bearerAuth":{}}},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"product_ids", "manufacturer_part_numbers", "retailer_ids", "start_date", "end_date"},
    *             @OA\Property(property="dataPerPage", type="integer", example=10),
    *             @OA\Property(property="page", type="integer", example=1),
    *             @OA\Property(property="product_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
    *             @OA\Property(property="manufacturer_part_numbers", type="array", @OA\Items(type="string"), example={"MPN123", "MPN456"}),
    *             @OA\Property(property="retailer_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
    *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
    *             @OA\Property(property="end_date", type="string", format="date", example="2025-12-31")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/MetricResource")
    *         )
    *     ),
    *     @OA\Response(response=401, description="Unauthorized"),
    *     @OA\Response(response=403, description="Forbidden - User does not have access"),
    *     @OA\Response(response=422, description="Validation error"),
    * )
   */
   public function getRetailerMetrics(RetailerMetricsRequest $request): JsonResponse {
      $this->authorize('getMetrics', Retailer::class); 
      
      $user = auth()->user();
      $data = $request->validated();
      $latestAvailableDate = DB::table('scraped_data')->max('created_at');
      $productIds = $data['product_ids'] ?? [];
      $mpns = $data['manufacturer_part_numbers'] ?? [];
      $retailerIds = $data['retailer_ids'] ?? [];
      $startDate = $data['start_date'] ?? $latestAvailableDate;
      $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date'])->endOfDay() : $latestAvailableDate;
      $accessibleRetailers = $this->getAccessibleRetailers($user);

      $query = $this->buildMetricsQuery(
         $startDate, 
         $endDate, 
         $accessibleRetailers, 
         $productIds, 
         $mpns, 
         $retailerIds
      );
      $metrics = $query->paginate(
         $data['dataPerPage'] ?? 100, 
         ['*'], 
         'page', 
         $data['page'] ?? 1
      );
      $meta = [
         'current_page' => $metrics->currentPage(),
         'per_page' => $metrics->perPage(),
         'last_page' => $metrics->lastPage(),
         'total' => $metrics->total(),
      ];

      return $this->successResponse(
         MetricResource::collection($metrics),
         'messages.index.success',
         ['attribute' => self::ENTITY],
         Response::HTTP_OK,
         $meta
      );
   } 

   /**
    * Get accessible retailers for the user.
    *
    * @param User $user The user to check access
    *
    * @return array An array with allowed to user retailers IDs 
   */
   private function getAccessibleRetailers(User $user): array {
      if ($user->isSuperUser()) {
         return DB::table('retailers')->pluck('id')->toArray();
      }
      return DB::table('user_retailers')->where('user_id', $user->id)->pluck('retailer_id')->toArray();
   }

   /**
    * Builds the query for retrieving retailer metrics.
    *
    * @param $startDate Start date of the scraping
    * @param $endDate End date of the scraping
    * @param $accessibleRetailers Retailers which user can access
    * @param $productIds Specified products
    * @param $mpns Manufacturer part numbers
    * @param $retailerIds Specified retailers IDs
    *
    * @return Builder
   */
   private function buildMetricsQuery(
         $startDate, 
         $endDate, 
         $accessibleRetailers, 
         $productIds, 
         $mpns, 
         $retailerIds
      ): Builder {
      $query = DB::table('scraped_data')
         ->join('product_retailers', 'scraped_data.product_retailer_id', '=', 'product_retailers.id')
         ->join('products', 'product_retailers.product_id', '=', 'products.id')
         ->join('retailers', 'product_retailers.retailer_id', '=', 'retailers.id')
         ->leftJoin('scraped_data_images', 'scraped_data.id', '=', 'scraped_data_images.scraped_data_id')
         ->select(
            'retailers.id as retailer_id',
            'retailers.title as retailer_title',
            'products.id as product_id',
            'products.title as product_title',
            DB::raw('AVG(scraped_data.avg_rating) as avg_rating'),
            DB::raw('AVG(scraped_data.price) as avg_price'),
            DB::raw('COUNT(scraped_data_images.id) / COUNT(DISTINCT scraped_data.id) as avg_images')
         )
         ->whereBetween('scraped_data.created_at', [$startDate, $endDate])
         ->whereIn('retailers.id', $accessibleRetailers)
         ->groupBy('retailers.id', 'retailers.title', 'products.id', 'products.title')
         ->orderBy('product_id', 'asc');

      if (!empty($productIds)) {
         $query->whereIn('products.id', $productIds);
      }
      if (!empty($mpns)) {
         $query->whereIn('products.manufacturer_part_number', $mpns);
      }
      if (!empty($retailerIds)) {
         $query->whereIn('retailers.id', array_intersect($retailerIds, $accessibleRetailers));
      }

      return $query;
   }
}