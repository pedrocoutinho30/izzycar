<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radar_search_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radar_search_id')->constrained('radar_searches')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('running');
            $table->unsignedInteger('listings_found')->default(0);
            $table->unsignedInteger('pages_scraped')->default(0);
            $table->text('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_search_runs');
    }
};
