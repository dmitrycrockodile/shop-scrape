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
        Schema::create('product_retailers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'id')->onDelete('cascade');
            $table->foreignId('retailer_id')->constrained('retailers', 'id')->onDelete('cascade');
            $table->unique(['product_id', 'retailer_id']);
            $table->string('product_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_retailers');
    }
};
