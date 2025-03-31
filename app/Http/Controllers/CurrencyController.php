<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Currency\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(path="/api/pack-sizes")
 */
class CurrencyController extends BaseController
{
    private const ENTITY_KEY = 'currency';

    /**
     * Retrieves the pack sizes.
     * 
     * @return JsonResponse A JSON response containing retrieved pack sizes.
    */
    public function index(Request $request): JsonResponse
    {
        $dataPerPage = $request->query('dataPerPage', 10);
        $page = $request->query('page', 2);
        $currencies = Currency::paginate(
            $dataPerPage,
            ['*'],
            'page',
            $page
        );
        $meta = [
            'current_page' => $currencies->currentPage(),
            'per_page' => $currencies->perPage(),
            'last_page' => $currencies->lastPage(),
            'total' => $currencies->total(),
            'links' => $currencies->toArray()['links'],
        ];

        return $this->successResponse(
            CurrencyResource::collection($currencies),
            'messages.index.success',
            ['attribute' => self::ENTITY_KEY],
            Response::HTTP_OK,
            $meta
        );
    }
}