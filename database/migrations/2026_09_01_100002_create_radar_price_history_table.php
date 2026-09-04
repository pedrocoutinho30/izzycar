<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radar_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radar_listing_id')->constrained('radar_listings')->cascadeOnDelete();
            $table->unsignedInteger('price_eur');
            $table->timestamp('scraped_at');
            $table->index(['radar_listing_id', 'scraped_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_price_history');
    }
};
