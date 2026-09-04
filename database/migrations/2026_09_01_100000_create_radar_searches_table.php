<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radar_searches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('make');
            $table->string('model')->nullable();
            $table->json('filters')->nullable();
            $table->text('base_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_searches');
    }
};
