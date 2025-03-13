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
        Schema::create('scraped_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'id')->onDelete('cascade');
            $table->foreignId('retailer_id')->constrained('retailers', 'id')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_count');
            $table->decimal('avg_rating', 2, 1)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_data');
    }
};
