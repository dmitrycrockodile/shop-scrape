<?php

namespace Tests\Unit\Services;

use App\Service\CsvExporter;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class CsvExporterTest extends TestCase
{
    protected CsvExporter $csvExporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvExporter = new CsvExporter();
    }

    public function test_it_exports_data_as_csv_without_headers()
    {
        $data = collect([
            (object) ['name' => 'Product 1', 'price' => 100],
            (object) ['name' => 'Product 2', 'price' => 200],
        ]);

        $stream = fopen('php://temp', 'r+');

        Log::shouldReceive('info')->once();

        $this->csvExporter->export($stream, $data);

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString('name,price', $csvContent);
        $this->assertStringContainsString('"Product 1",100', $csvContent);
        $this->assertStringContainsString('"Product 2",200', $csvContent);
    }

    public function test_it_exports_data_as_csv_with_custom_headers()
    {
        Log::shouldReceive('info')
        ->once()
        ->with('CSV Export Performance:', \Mockery::on(function ($data) {
            return isset($data['execution_time']) && isset($data['memory_usage']) && isset($data['rows_processed']);
        }));

        $data = collect([
            (object) ['name' => 'Product 1', 'price' => 100],
            (object) ['name' => 'Product 2', 'price' => 200],
        ]);

        $headers = ['product_name', 'product_price'];
        $stream = fopen('php://temp', 'r+');

        $this->csvExporter->export($stream, $data, $headers);

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString('product_name,product_price', $csvContent);
        $this->assertStringContainsString('"Product 1",100', $csvContent);
        $this->assertStringContainsString('"Product 2",200', $csvContent);
    }

    public function test_it_exports_data_as_csv_when_data_is_array()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('CSV Export Performance:', \Mockery::on(function ($data) {
                return isset($data['execution_time']) && isset($data['memory_usage']) && isset($data['rows_processed']);
            }));

        $data = collect([
            ['name' => 'Product 1', 'price' => 100],
            ['name' => 'Product 2', 'price' => 200],
        ]);

        $stream = fopen('php://temp', 'r+');
        $this->csvExporter->export($stream, $data);
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString('name,price', $csvContent);
        $this->assertStringContainsString('"Product 1",100', $csvContent);
        $this->assertStringContainsString('"Product 2",200', $csvContent);
    }

    public function test_it_formats_row_correctly()
    {
        $row = [
            'name' => ' "Product 1" ',
            'price' => ' 100 ',
        ];

        $formattedRow = $this->invokePrivateMethod($this->csvExporter, 'formatRow', [$row]);

        $this->assertEquals([
            'name' => 'Product 1',
            'price' => '100',
        ], $formattedRow);
    }

    public function test_it_logs_performance_data()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('CSV Export Performance:', \Mockery::on(function ($data) {
                $this->assertArrayHasKey('execution_time', $data);
                $this->assertArrayHasKey('memory_usage', $data);
                $this->assertArrayHasKey('rows_processed', $data);
                $this->assertGreaterThan(0, $data['execution_time']);
                $this->assertGreaterThan(0, $data['memory_usage']);
                return true;
            }));

        $data = collect([
            (object) ['name' => 'Product 1', 'price' => 100],
            (object) ['name' => 'Product 2', 'price' => 200],
        ]);

        $stream = fopen('php://temp', 'r+');
        $this->csvExporter->export($stream, $data);
        fclose($stream);

        $this->assertGreaterThanOrEqual(2, $data->count());
    }
}
