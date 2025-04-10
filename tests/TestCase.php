<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.database', 'shop_scrape_test');
        config()->set('database.connections.mysql.host', env('DB_HOST', 'shop_scrape_db'));
        config()->set('database.connections.mysql.username', env('DB_USERNAME', 'root'));
        config()->set('database.connections.mysql.password', env('DB_PASSWORD', 'root'));
        DB::reconnect();
        $this->artisan('migrate:fresh --database=shop_scrape_test')->run();
    }
}
