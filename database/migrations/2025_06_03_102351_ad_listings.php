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
        Schema::create('ad_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_search_id')->constrained('ad_searches')->onDelete('cascade');
            $table->string('external_id'); // ID do site original (standvirtual)
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->integer('year');
            $table->integer('mileage')->nullable();
            $table->string('location')->nullable();
            $table->string('published_time')->nullable();
            $table->text('url');
            $table->timestamps();

            $table->unique(['ad_search_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_listings');
    }
};
