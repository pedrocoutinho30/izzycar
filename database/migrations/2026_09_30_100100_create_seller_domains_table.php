<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domínios de email de um vendedor (pode ter vários, ex. .de e .com). Servem
 * só para sugerir correspondências — nunca para associar automaticamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->index();
            $table->timestamps();

            $table->unique(['seller_id', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_domains');
    }
};
