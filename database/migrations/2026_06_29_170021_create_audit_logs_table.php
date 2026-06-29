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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Utilizador que realizou a acção (null = sistema/não autenticado)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Acção: created, updated, deleted, login, logout, exported, converted
            $table->string('action', 50);
            // Modelo e ID do registo afectado (ex: App\Models\Client, 42)
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            // Descrição legível da acção
            $table->string('description');
            // Valores antes e depois (apenas em updates)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            // IP e user-agent para rastreabilidade
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
