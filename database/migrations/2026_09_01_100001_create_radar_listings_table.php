<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radar_listings', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->foreignId('radar_search_id')->nullable()->constrained('radar_searches')->nullOnDelete();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('version')->nullable();
            $table->unsignedSmallInteger('first_registration_year')->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->unsignedInteger('power_hp')->nullable();
            $table->string('fuel')->nullable();
            $table->string('gearbox')->nullable();
            $table->string('body_type')->nullable();
            $table->string('seller_type')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('location_zip')->nullable();
            $table->string('location_city')->nullable();
            $table->unsignedInteger('price_eur')->nullable();
            $table->text('url')->nullable();
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_listings');
    }
};
