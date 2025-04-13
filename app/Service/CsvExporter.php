<?php

namespace App\Service;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CsvExporter
{
    /**
     * Export a collection of data as CSV.
     *
     * @param resource $stream The stream.
     * @param Collection $data The data to export, typically an Eloquent collection.
     * @param array $headers An optional array of headers (columns) for the CSV.
     */
    public function export($stream, Collection $data, array $headers = []): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        if (empty($headers) && $data->isNotEmpty()) {
            $first = $data->first();

            $firstArray = is_array($first)
                ? $first
                : (method_exists($first, 'toArray') ? $first->toArray() : (array)$first);
            $headers = array_keys($firstArray);

            fputcsv($stream, $headers);
        } elseif (!empty($headers)) {
            fputcsv($stream, $headers);
        }
        
        $rowsCount = 0;

        foreach ($data as $row) {
            $rowArray = is_array($row)
                ? $row
                : (method_exists($row, 'toArray') ? $row->toArray() : (array)$row);

            $formattedRow = $this->formatRow($rowArray);

            fputcsv($stream, $formattedRow);
            $rowsCount++;
        }

        $this->logPerformance($startTime, $startMemory, $rowsCount);
    }

    /**
     * Replaces the double quotes, trims the rows.
     *
     * @param array $row The row to format.
     * 
     * @return array Formatted array withour double quotes and trimmed.
     */
    private function formatRow(array $row): array
    {
        return array_map(function ($value) {
            return is_string($value) ? trim(str_replace('"', '', $value)) : $value;
        }, $row);
    }

    /**
     * Logs the perfomance of the export (execution_time, memory_usage, rows_processed).
     *
     * @param float $startTime Start time of the execution.
     * @param int $startMemory Memory on the start of the operation.
     * @param int $rowsCount Count of the proccessed rows.
     * 
     * @return void
     */
    private function logPerformance(float $startTime, int $startMemory, int $rowsCount): void
    {
        $executionTime = microtime(true) - $startTime;
        $endMemory = memory_get_usage() - $startMemory;

        Log::info('CSV Export Performance:', [
            'execution_time' => round($executionTime, 2) . ' seconds',
            'memory_usage' => round($endMemory / 1024, 2) . ' KB',
            'rows_processed' => $rowsCount,
        ]);
    }
}