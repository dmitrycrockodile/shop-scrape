<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\CsvImportException;
use App\Exceptions\CsvImportExceptionHandler;
use App\Models\PackSize;
use Tests\TestCase;
use Illuminate\Http\Response;
use Exception;

class CsvImportExceptionHandlerTest extends TestCase
{
    public function test_it_detects_duplicate_product_error()
    {
        $e = new \Exception("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'ABC-3'");
        $this->assertTrue(CsvImportExceptionHandler::isDuplicateProductError($e));
    }

    public function test_it_extracts_duplicate_product_details()
    {
        PackSize::factory()->create([
            'id' => 7,
            'name' => 'Box',
            'weight' => 500,
            'weight_unit' => 'g',
            'amount' => 10
        ]);

        $e = new \Exception("Duplicate entry 'XYZ-7' for key 'products_unique'");

        $result = CsvImportExceptionHandler::extractDuplicateProductDetails($e);

        $this->assertStringContainsString("MPN: 'XYZ'", $result);
        $this->assertStringContainsString('Box', $result);
        $this->assertStringContainsString('500g', $result);
        $this->assertStringContainsString('10', $result);
    }

    public function test_extract_duplicate_product_details_returns_null_on_invalid_message()
    {
        $e = new \Exception("Duplicate entry 'MPN-9999' for key 'products_unique'");

        $this->assertDatabaseMissing('pack_sizes', ['id' => 9999]);

        $result = CsvImportExceptionHandler::extractDuplicateProductDetails($e);

        $this->assertNull($result);
    }

    public function test_handle_import_exception_with_duplicate()
    {
        PackSize::factory()->create(['id' => 1, 'name' => 'Test', 'weight' => 123, 'weight_unit' => 'g', 'amount' => 5]);
        $exception = new CsvImportException("Duplicate entry 'TEST-1' for key 'products_unique'");

        try {
            CsvImportExceptionHandler::handleImportException($exception);
        } catch (CsvImportException $e) {
            $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $e->statusCode);
            $this->assertEquals('Duplicate product found: A product with MPN: \'TEST\', Pack Size: \'Test, 123g, 5\' already exists.', $e->getMessage());
            return;
        }
    }

    public function test_handle_import_exception_throws_generic_csv_exception()
    {
        $exception = new CsvImportException("Some error");

        try {
            CsvImportExceptionHandler::handleImportException($exception);
        } catch (CsvImportException $e) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $e->statusCode);
            $this->assertEquals('Error processing CSV: Some error', $e->getMessage());
            return;
        }
    
        $this->fail("Expected CsvImportException was not thrown.");
    }

    public function test_handle_missing_retailers_exception()
    {
        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessage('The following retailers do not exist');

        CsvImportExceptionHandler::handleMissingRetailersException(['Retailer A', 'Retailer B']);
    }

    public function test_handle_invalid_csv_exception()
    {
        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessage('No valid products found in the CSV');

        CsvImportExceptionHandler::handleInvalidCsvException();
    }

    public function test_handle_empty_csv_exception()
    {
        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessage('Please select a CSV file to upload');

        CsvImportExceptionHandler::handleEmptyCsvException();
    }
}
