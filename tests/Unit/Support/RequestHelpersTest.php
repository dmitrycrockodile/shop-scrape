<?php

namespace Tests\Unit\Support;

use Illuminate\Http\Request;
use Tests\TestCase;

class RequestHelpersTest extends TestCase
{
    public function test_extract_resource_name_from_request()
    {
        $request = Request::create('/api/products/1', 'GET');

        $this->assertEquals('product', extractResourceName($request));
    }

    public function test_extract_resource_name_defaults_to_resource()
    {
        $request = Request::create('/', 'GET');

        $this->assertEquals('resource', extractResourceName($request));
    }

    public function test_determine_action_from_http_method()
    {
        $this->assertEquals('index', determineAction('GET'));
        $this->assertEquals('store', determineAction('POST'));
        $this->assertEquals('update', determineAction('PUT'));
        $this->assertEquals('update', determineAction('PATCH'));
        $this->assertEquals('destroy', determineAction('DELETE'));
        $this->assertEquals('operation', determineAction('HEAD'));
    }
}