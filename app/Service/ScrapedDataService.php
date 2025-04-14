<?php

namespace App\Service;

use App\Http\Resources\ScrapedData\ScrapedDataResource;
use App\Models\ProductRetailer;
use App\Models\Rating;
use App\Models\ScrapedData;
use App\Models\ScrapedDataImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScrapedDataService
{
    /**
     * Store new scraped data.
     *
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array
    {
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
        return $this->successResponse($scrapedData);
    }

    /**
     * Retrieves scraped data filtered by retailers, products and date range.
     *
     * @param Carbon $startDate The start date in 'YYYY-MM-DD' format.
     * @param Carbon $endDate The end date in 'YYYY-MM-DD' format.
     * @param array $filters The retailers ids and the products ids.
     * 
     * @return Collection A collection of scraped data entries.
     */
    public function getFilteredScrapedData(?Carbon $startDate, ?Carbon $endDate, array $filters = []): Collection
    {
        $retailerIds = $filters['retailer_ids'] ?? [];
        $productIds = $filters['product_ids'] ?? [];

        $filteredRetailerIds = $this->filterAccessibleRetailerIds($retailerIds);
        $filteredProductIds = $this->filterAccessibleProductIds($productIds);

        $query =  ScrapedData::query()
            ->join('product_retailers', 'scraped_data.product_retailer_id', '=', 'product_retailers.id')
            ->join('products', 'product_retailers.product_id', '=', 'products.id')
            ->whereIn('product_retailers.retailer_id', $filteredRetailerIds)
            ->when(!empty($filteredProductIds), fn($q) => $q->whereIn('product_retailers.product_id', $filteredProductIds))
            ->select('scraped_data.*');

        if ($startDate && $endDate) {
            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();
    
            $query->whereBetween('scraped_data.created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $startDate = $startDate->startOfDay();
            $query->where('scraped_data.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $endDate = $endDate->endOfDay();
            $query->where('scraped_data.created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Retrievies an array of the accessible to user products.
     *
     * @param array $requestedIds The products ids from the request.
     * 
     * @return array An array of the accessible to user products.
     */
    private function filterAccessibleProductIds(array $requestedIds = []): array
    {
        $accessible = auth()->user()->accessibleRetailers()
            ->join('product_retailers', 'retailers.id', '=', 'product_retailers.retailer_id')
            ->pluck('product_retailers.product_id')
            ->unique()
            ->toArray();

        return empty($requestedIds)
            ? $accessible
            : array_values(array_intersect($requestedIds, $accessible));
    }

    /**
     * Retrievies an array of the accessible to user retailers.
     *
     * @param array $requestedIds The retailers ids from the request.
     * 
     * @return array An array of the accessible to user retailers.
     */
    private function filterAccessibleRetailerIds(array $requestedIds = []): array
    {
        $accessible = auth()->user()->accessibleRetailers()->pluck('retailers.id')->toArray();

        return empty($requestedIds)
            ? $accessible
            : array_values(array_intersect($requestedIds, $accessible));
    }

    /**
     * Store images for the scraped data.
     *
     * @param array $images
     * @param ScrapedData $scrapedData
     *
     * @return void
     */
    private function storeScrapedDataImages(array $images, ScrapedData $scrapedData): void
    {
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
    private function storeScrapedDataRatings(array $ratings, ScrapedData $scrapedData): void
    {
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
    private function extractImages(array &$data): ?array
    {
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
    private function extractRatings(array &$data): ?array
    {

        $ratings = $data['ratings'] ?? null;
        unset($data['ratings']);

        return $ratings;
    }

    /**
     * Success response formatting.
     *
     * @param ScrapedData $scrapedData
     * 
     * @return array
     */
    private function successResponse(ScrapedData $scrapedData): array
    {
        return [
            'success' => true,
            'scrapedData' => new ScrapedDataResource($scrapedData)
        ];
    }

    /**
     * Error response formatting.
     *
     * @param string $errorMessage
     * @param Exception $exception
     *
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
            'status' => $statusCode
        ];
    }
}
