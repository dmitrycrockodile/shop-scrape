<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\CsvImportException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class CsvImportExceptionTest extends TestCase
{
    public function test_render_returns_expected_json_response_with_default_status_code()
    {
        $exception = new CsvImportException('Something went wrong during import');

        $request = Request::create('/fake-url', 'GET');
        $response = $exception->render($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertEquals([
            'success' => false,
            'message' => 'Something went wrong during import',
            'errors' => 'Something went wrong during import',
        ], $response->getData(true));
    }

    public function test_render_returns_expected_json_response_with_custom_status_code()
    {
        $exception = new CsvImportException('Invalid format', 422);

        $request = Request::create('/fake-url', 'POST');
        $response = $exception->render($request);

        $this->assertEquals(422, $response->getStatusCode());

        $this->assertEquals([
            'success' => false,
            'message' => 'Invalid format',
            'errors' => 'Invalid format',
        ], $response->getData(true));
    }
}