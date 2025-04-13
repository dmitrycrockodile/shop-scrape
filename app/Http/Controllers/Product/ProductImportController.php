<?php

namespace App\Http\Controllers\Product;

use App\Exceptions\CsvImportExceptionHandler;
use App\Http\Controllers\BaseController;
use App\Service\Product\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends BaseController
{
    protected ImportService $importService;
    private const ENTITY_KEY = 'product';

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Uploads the CSV file.
     * 
     * @param Request $request The request with the file
     * 
     * @return JsonResponse A JSON response with success or error message.
     */
    public function uploadCSV(Request $request): JsonResponse
    {
        $file = $request->file('file');
        if (!$file) {
            CsvImportExceptionHandler::handleEmptyCsvException();
        }

        $path = $file->store('uploads');
        $fullPath = storage_path("app/{$path}");

        $this->importService->importProducts($fullPath);

        Storage::delete($path);

        return $this->successResponse(
            [],
            'messages.import.success',
            ['attribute' => self::ENTITY_KEY . 's'],
            Response::HTTP_OK
        );
    }
}