<?php

namespace Tests\Unit\Services;

use App\Service\CsvImporter;
use Exception;
use Illuminate\Http\Response;
use Tests\TestCase;

class CsvImporterTest extends TestCase
{
    private CsvImporter $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer = new CsvImporter();
    }

    public function test_it_imports_csv_with_normal_headers()
    {
        $csv = "name,price\nProduct 1,100\nProduct 2,200";
        $file = $this->createTempCsv($csv);

        $result = $this->importer->import($file);

        $this->assertCount(2, $result);
        $this->assertEquals('Product 1', $result[1]['name']);
        $this->assertEquals('200', $result[2]['price']);
    }

    public function test_it_handles_bom()
    {
        $bom = "\xEF\xBB\xBF";
        $csv = $bom . "name,price\nProduct 1,100";
        $file = $this->createTempCsv($csv);

        $result = $this->importer->import($file);

        $this->assertEquals('Product 1', $result[1]['name']);
    }

    public function test_it_fixes_duplicate_headers()
    {
        $csv = "name,name,name\nProduct 1,Product 2";
        $file = $this->createTempCsv($csv);

        $result = $this->importer->import($file);

        $this->assertArrayHasKey('name', $result[1]);
        $this->assertArrayHasKey('name_1', $result[1]);
        $this->assertArrayHasKey('name_2', $result[1]);
        $this->assertEquals('Product 1', $result[1]['name']);
        $this->assertEquals('Product 2', $result[1]['name_1']);
    }

    public function test_it_uses_row_mapper()
    {
        $csv = "name,price\nProduct 1,100\nProduct 2,200";
        $file = $this->createTempCsv($csv);

        $result = $this->importer->import($file, fn($row) => [
            'label' => strtoupper($row['name']),
            'cost' => (int) $row['price']
        ]);

        $this->assertEquals('PRODUCT 1', $result[1]['label']);
        $this->assertEquals(200, $result[2]['cost']);
    }

    public function test_it_throws_exception_on_empty_file()
    {
        $file = $this->createTempCsv('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CSV file is empty.');
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->importer->import($file);
    }

    public function test_it_splits_lines_with_various_newline_characters()
    {
        $csvContent = "row1\rrow2\nrow3\r\nrow4";

        $result = $this->invokePrivateMethod(
            $this->importer,
            'splitLines',
            [$csvContent]
        );

        $this->assertEquals(['row1', 'row2', 'row3', 'row4'], $result);
    }

    private function createTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($path, $content);
        return $path;
    }
}
