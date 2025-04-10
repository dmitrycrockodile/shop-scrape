<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionTest extends TestCase
{
    public function test_database_is_shop_scrape_test()
    {
        $currentConnection = DB::connection()->getDatabaseName();
        
        $this->assertEquals('shop_scrape_test', $currentConnection);
    }
}
