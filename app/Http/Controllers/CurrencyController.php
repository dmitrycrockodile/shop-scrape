<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Currency\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

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
    public function index(): JsonResponse
    {
        $currencies = Currency::all();

        return $this->successResponse(
            CurrencyResource::collection($currencies),
            'messages.index.success',
            ['attribute' => self::ENTITY_KEY]
        );
    }
}