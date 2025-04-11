<?php

namespace App\Exceptions;

use App\Models\PackSize;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CsvImportExceptionHandler
{
    /**
     * Checks if an exception is caused by a duplicate product entry.
     */
    public static function isDuplicateProductError(\Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate entry');
    }

    /**
     * Extracts duplicate product details from an SQL error message.
     */
    public static function extractDuplicateProductDetails(\Throwable $e): ?string
    {
        preg_match("/Duplicate entry '([^']+)-(\d+)'/", $e->getMessage(), $matches);
        
        if (count($matches) === 3) {
            $mpn = $matches[1];
            $packSizeId = $matches[2];

            $packSizeDetails = PackSize::find($packSizeId);

            if ($packSizeDetails) {
                return "MPN: '$mpn', Pack Size: '{$packSizeDetails->name}, {$packSizeDetails->weight}{$packSizeDetails->weight_unit}, {$packSizeDetails->amount}'";
            }
        }

        return null;
    }

    /**
     * Handles import exceptions, specifically for duplicate entries.
     */
    public static function handleImportException(\Throwable $e)
    {
        DB::rollBack();

        
        if (self::isDuplicateProductError($e)) {
            $duplicateDetails = self::extractDuplicateProductDetails($e);
            
            if ($duplicateDetails) {
                throw new CsvImportException(
                    "Duplicate product found: A product with $duplicateDetails already exists.",
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        Log::warning('Handling import exception', [
            'message' => $e->getMessage(),
            'statusCode' => $e->getCode(),
        ]);
    
        throw new CsvImportException(
            "Error processing CSV: " . $e->getMessage(), 
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Handles exception when the retailer is not exist
     * 
     * @param array $missingRetailers
     */ 
    public static function handleMissingRetailersException(array $missingRetailers)
    {
        throw new CsvImportException(
            'The following retailers do not exist. Please create them first: ' . implode(', ', $missingRetailers),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Handles exception when the file is invalid
     */ 
    public static function handleInvalidCsvException()
    {
        throw new CsvImportException('No valid products found in the CSV.', Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handles exception when the file is empty
     */ 
    public static function handleEmptyCsvException()
    {
        throw new CsvImportException('Please select a CSV file to upload.', Response::HTTP_BAD_REQUEST);
    }
}