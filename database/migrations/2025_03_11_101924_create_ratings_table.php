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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraped_data_id')->constrained('scraped_data', 'id')->onDelete('cascade');
            $table->unsignedInteger('one_star')->default(0);
            $table->unsignedInteger('two_stars')->default(0);
            $table->unsignedInteger('three_stars')->default(0);
            $table->unsignedInteger('four_stars')->default(0);
            $table->unsignedInteger('five_stars')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
