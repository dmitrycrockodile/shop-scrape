<?php

namespace App\Service;

use Exception;
use League\Csv\Reader;
use Illuminate\Http\Response;

class CsvImporter
{
    /**
     * Imports CSV records from a file.
     *
     * @param string $filePath Absolute path to the CSV file.
     * @param callable|null $rowMapper Optional callback to transform each row.
     * 
     * @return array
     */
    public function import(string $filePath, callable $rowMapper = null): array
    {
        $csvContent = file_get_contents($filePath);
        $csvContent = $this->removeBom($csvContent);

        $lines = $this->splitLines($csvContent);
        if (empty($lines) || $lines[0] === "") {
            throw new Exception('CSV file is empty.', Response::HTTP_BAD_REQUEST);
        }

        $headerLine = array_shift($lines);
        $rawHeaders = str_getcsv($headerLine, ',');
        $cleanHeaders = array_map('trim', $rawHeaders);
        $finalHeaders = $this->fixDuplicateHeaders($cleanHeaders);
        array_unshift($lines, implode(',', $finalHeaders));

        $newCsvContent = implode("\n", $lines);
        $csv = Reader::createFromString($newCsvContent, 'r');
        $csv->setDelimiter(',');
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());
        if ($rowMapper) {
            $records = array_map($rowMapper, $records);
        }
        
        return $records;
    }

    /**
     * Removes Byte Order Mark from the beggining of the file if needed
     *
     * @param string $content A string to remove BOM from.
     * 
     * @return string
     */
    private function removeBom(string $content): string
    {
        return (substr($content, 0, 3) === "\xef\xbb\xbf") ? substr($content, 3) : $content;
    }

    /**
     * Splits the file content into the lines by newlines chars
     *
     * @param string $content A file content to split.
     * 
     * @return array
     */
    private function splitLines(string $content): array 
    {
        return preg_split("/\r\n|\n|\r/", $content);
    }

    /**
     * Checks if the headers are duplicated.
     *
     * @param array $headers The headers array to check.
     * 
     * @return array
     */
    private function fixDuplicateHeaders(array $headers): array {
        $finalHeaders = [];

        foreach ($headers as $header) {
            if (in_array($header, $finalHeaders)) {
                $suffix = 1;
                $newHeader = $header . '_' . $suffix;
                
                while (in_array($newHeader, $finalHeaders)) {
                    $suffix++;
                    $newHeader = $header . '_' . $suffix;
                }

                $finalHeaders[] = $newHeader;
            } else {
                $finalHeaders[] = $header;
            }
        }

        return $finalHeaders;
    }
}
