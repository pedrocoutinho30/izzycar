<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration - cria a tabela de tarefas
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            
            // Informação básica da tarefa
            $table->string('title'); // Título da tarefa
            $table->text('description')->nullable(); // Descrição detalhada
            
            // Datas
            $table->timestamp('created_at')->useCurrent(); // Data de criação
            $table->date('due_date'); // Data limite para conclusão
            $table->date('reminder_date')->nullable(); // Data do lembrete
            
            // Relações opcionais
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Usuário responsável
            $table->string('user_name')->nullable(); // Nome do usuário (caso não seja do sistema)
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('cascade'); // Veículo relacionado
            
            // Estado da tarefa
            $table->enum('status', ['pendente', 'em_progresso', 'concluida', 'cancelada'])->default('pendente');
            
            // Controle
            $table->boolean('reminder_sent')->default(false); // Se o lembrete já foi enviado
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverte a migration - remove a tabela de tarefas
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
