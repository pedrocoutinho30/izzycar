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
        Schema::create('recommendation_posts', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->unsignedInteger('power')->nullable();
            $table->string('fuel')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->json('equipment')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('savings', 10, 2)->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_posts');
    }
};
