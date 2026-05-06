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
        Schema::create('legalization_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legalization_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->string('nome_original');
            $table->string('caminho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legalization_documents');
    }
};
