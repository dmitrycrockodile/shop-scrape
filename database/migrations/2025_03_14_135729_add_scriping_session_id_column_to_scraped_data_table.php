<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scraped_data', function (Blueprint $table) {
            $table->foreignId('scraping_session_id')->constrained('scraping_sessions', 'id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_data', function (Blueprint $table) {
            $table->dropForeign('scraped_data_scraping_session_id_foreign');
            $table->dropColumn('scraping_session_id');
        });
    }
};
