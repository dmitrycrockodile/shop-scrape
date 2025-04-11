<?php

namespace Tests\Unit\Models;

use App\Models\Retailer;
use Tests\TestCase;

class RetailerTest extends TestCase
{
    public function test_logo_url_returns_null_when_logo_is_null()
    {
        $retailer = new Retailer(['logo' => null]);

        $this->assertNull($retailer->logo_url);
    }

    public function test_logo_url_returns_full_url_when_logo_is_set()
    {
        $retailer = new Retailer(['logo' => 'logos/image.png']);

        $expected = url('storage/logos/image.png');

        $this->assertEquals($expected, $retailer->logo_url);
    }
}