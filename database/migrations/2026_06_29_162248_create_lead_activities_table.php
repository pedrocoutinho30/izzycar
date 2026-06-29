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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            // user_id é null para eventos automáticos do sistema
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Tipos: system (automático), note (nota livre), call (chamada), email (email enviado), meeting (reunião)
            $table->string('type')->default('system');
            $table->string('title');
            $table->text('body')->nullable();
            // Ícone Bootstrap Icons e cor Bootstrap para visualização na timeline
            $table->string('icon')->default('bi-circle-fill');
            $table->string('color')->default('secondary');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
