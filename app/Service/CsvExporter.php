<?php

namespace App\Service;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class CsvExporter
{
    /**
     * Export a collection of data as CSV.
     *
     * @param Collection $data The data to export, typically an Eloquent collection.
     * @param string $fileName The desired name of the exported CSV file.
     * @param array $headers An optional array of headers (columns) for the CSV.
     * 
     * @return StreamedResponse
     */
    public function export(Collection $data, string $fileName, array $headers = []): StreamedResponse
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        if (!count($headers) && $data->isNotEmpty()) {
            $headers = array_keys($data->first()->toArray());
        }

        $response = new StreamedResponse(function () use ($data, $headers, $startTime, $startMemory) {
            $output = fopen('php://output', 'w');

            fputcsv($output, $headers);
            $rowsCount = 0;

            foreach ($data as $row) {
                fputcsv($output, $row->toArray());
                $rowsCount++;
            }

            $executionTime = microtime(true) - $startTime;
            $endMemory = memory_get_usage() - $startMemory;

            fclose($output);

            Log::info('CSV Export Performance:', [
                'execution_time' => round($executionTime, 2) . ' seconds',
                'memory_usage' => round($endMemory / 1024, 2) . ' KB',
                'rows_processed' => $rowsCount,
            ]);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

        return $response;
    }
}