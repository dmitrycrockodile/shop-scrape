<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CsvImportException extends Exception
{
    public $statusCode;

    public function __construct(string $message, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->getMessage(),
        ], $this->statusCode);
    }
}
