<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class BaseControllerTest extends TestCase
{
    protected BaseController $baseController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseController = new BaseController();
    }

    public function test_success_response_returns_expected_structure()
    {
        $data = ['name' => 'Test'];
        $message = 'messages.index.success';
        $placeholders = ['attribute' => 'test'];

        $response = $this->invokePrivateMethod($this->baseController, 'successResponse', [$data, $message, $placeholders]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_response_returns_expected_structure_with_meta()
    {
        $data = ['name' => 'Test'];
        $message = 'messages.index.success';
        $placeholders = ['attribute' => 'test'];
        $meta = ['additional_info' => 'Some extra info about the data'];
    
        $response = $this->invokePrivateMethod($this->baseController, 'successResponse', [$data, $message, $placeholders, Response::HTTP_OK, $meta]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $responseData = $response->getData(true);
    
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
    
        $this->assertTrue($responseData['success']);
        $this->assertEquals(__($message, $placeholders), $responseData['message']);
        $this->assertEquals($data, $responseData['data']);

        $this->assertEquals($meta, $responseData['meta']);
    }

    public function test_error_response_returns_expected_structure()
    {
        $message = 'messages.error.not_found';
        $placeholders = ['attribute' => 'resource'];
        $error = 'Resource not found';
        $statusCode = Response::HTTP_NOT_FOUND;

        $response = $this->invokePrivateMethod($this->baseController, 'errorResponse', [$message, $placeholders, $error, $statusCode]);

        $this->assertInstanceOf(JsonResponse::class, $response);    
        $this->assertEquals($statusCode, $response->status());
    
        $responseData = $response->getData(true);
    
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('error', $responseData);

        $this->assertFalse($responseData['success']);
        $this->assertEquals(__($message, $placeholders), $responseData['message']);
        $this->assertEquals($error, $responseData['error']);
    }
}
