<?php

namespace App\Http\Resources\Metric;

use App\Models\ScrapedData;
use App\Models\Retailer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class MetricResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="MetricResource",
     *     type="object",
     *     title="Metric Resource",
     *     description="Schema for retailer metrics",
     *     @OA\Property(property="retailer_id", type="integer", example=1),
     *     @OA\Property(property="retailer_title", type="string", example="Amazon"),
     *     @OA\Property(property="product_id", type="integer", example=101),
     *     @OA\Property(property="product_title", type="string", example="Laptop XYZ"),
     *     @OA\Property(property="avg_rating", type="number", format="float", example=4.5),
     *     @OA\Property(property="avg_price", type="number", format="float", example=999.99),
     *     @OA\Property(property="avg_images", type="number", format="float", example=3.2),
     *     @OA\Property(property="Date", type="string", example="2024-03-22 to 2024-03-29"),
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'retailer_id' => $this->retailer_id,
            'retailer_title' => $this->retailer_title,
            'retailer_logo' => $this->retailer_logo,
            'avg_rating' => round($this->avg_rating, 2),
            'avg_price' => round($this->avg_price, 2),
            'avg_images_count' => round($this->avg_images, 2),
            'date' => $this->getDateRange($request),
            
        ];
    }

    private function getDateRange($request)
    {
        $latestAvailableDate = ScrapedData::query()->max('created_at');
        $startDate = $request->has('start_date') ? Carbon::parse($request->input('start_date'))->copy()->startOfDay() : Carbon::parse($latestAvailableDate)->copy()->startOfDay();
        $endDate = $request->has('end_date') ? Carbon::parse($request->input('end_date'))->copy()->endOfDay() : null;

        if ($startDate && $endDate) {
            return Carbon::parse($startDate)->toDateString() . ' - ' . Carbon::parse($endDate)->toDateString();
        }

        if ($startDate) {
            return Carbon::parse($startDate)->toDateString();
        }

        return null;
    }
}