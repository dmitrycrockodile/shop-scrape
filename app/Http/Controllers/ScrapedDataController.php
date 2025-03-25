<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ScrapedData\StoreRequest;
use App\Service\ScrapedDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\PathItem(path="/api/scraped-data")
 */
class ScrapedDataController extends BaseController
{
    protected ScrapedDataService $scrapedDataService;

    private const ENTITY_KEY = 'scraped_data';

    public function __construct(ScrapedDataService $scrapedDataService)
    {
        $this->scrapedDataService = $scrapedDataService;
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
}
