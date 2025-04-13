<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionTest extends TestCase
{
    public function test_connected_to_database_shop_scrape_test()
    {
        $currentConnection = DB::connection()->getDatabaseName();
        
        $this->assertEquals('shop_scrape_test', $currentConnection);
    }
}
