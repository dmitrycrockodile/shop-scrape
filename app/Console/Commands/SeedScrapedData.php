<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ScrapedDataSeeder;

class SeedScrapedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraped:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with scraped data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call(ScrapedDataSeeder::class);
        $this->info('ScrapedDataSeeder has been run successfully.');
    }
}
