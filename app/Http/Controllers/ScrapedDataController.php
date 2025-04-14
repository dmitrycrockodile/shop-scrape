<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ScrapedData\ExportRequest;
use App\Http\Requests\ScrapedData\StoreRequest;
use App\Models\ScrapedData;
use App\Service\CsvExporter;
use App\Service\ScrapedDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;

/**
 * @OA\PathItem(path="/api/scraped-data")
 */
class ScrapedDataController extends BaseController
{
    protected ScrapedDataService $scrapedDataService;
    protected CsvExporter $csvExporter;

    private const ENTITY_KEY = 'scraped_data';

    public function __construct(ScrapedDataService $scrapedDataService, CsvExporter $csvExporter)
    {
        $this->scrapedDataService = $scrapedDataService;
        $this->csvExporter = $csvExporter;
    }

    /**
     * Stores the scraped data.
     * 
     * @param StoreRequest $request A request with scraped data.
     * 
     * @return JsonResponse A JSON response containing newly created scraped data or error info.
     */
    /**
     * @OA\Post(
     *     path="/api/scraped-data",
     *     summary="Store scraped data",
     *     description="Stores new scraped data in the database.",
     *     tags={"Scraped Data"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ScrapedDataResource")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Scraped data stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Operation success status"),
     *             @OA\Property(property="message", type="string", example="Scraped data stored successfully.", description="Success message"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ScrapedDataResource",
     *                 description="The newly created scraped data entry"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $serviceResponse = $this->scrapedDataService->store($data);

        return $serviceResponse['success']
            ? $this->successResponse(
                $serviceResponse['scrapedData'],
                'messages.store.success',
                ['attribute' => self::ENTITY_KEY],
                Response::HTTP_CREATED
            )
            : $this->errorResponse(
                'messages.store.error',
                ['attribute' => self::ENTITY_KEY],
                $serviceResponse['error'],
                $serviceResponse['status']
            );
    }

    /**
     * Exports scraped data as a CSV file for a given date range.
     *
     * @param Request $request
     * 
     * @return StreamedResponse|JsonResponse A streamed CSV file or a JSON response in case of errors.
     */
    public function exportCSV(ExportRequest $request)
    {
        $data = $request->validated();

        $startDate = isset($data['startDate'])
            ? Carbon::parse($data['startDate'])->startOfDay()
            : null;

        $endDate = isset($data['endDate'])
            ? Carbon::parse($data['endDate'])->endOfDay()
            : null;

        $filters = [
            'retailer_ids' => $data['retailer_ids'] ?? [],
            'product_ids' => $data['product_ids'] ?? [],
        ];

        $scrapedData = $this->scrapedDataService->getFilteredScrapedData($startDate, $endDate, $filters);

        (isset($data['startDate']) && isset($data['endDate']))
            ? $fileName = "scraped_data_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv"
            : $fileName = "scraped_data.csv";

        return response()->streamDownload(function () use ($scrapedData) {
            $output = fopen('php://output', 'w');
            $this->csvExporter->export($output, $scrapedData);
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}