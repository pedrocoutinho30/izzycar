<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado de cada item de checklist numa Oportunidade. Só existe linha quando
 * o estado foi alterado — um item sem linha conta como "Por confirmar", o que
 * faz com que itens novos apareçam automaticamente em todas as oportunidades.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Uma primeira execução em MySQL falhou a meio (nomes de FK acima de
        // 64 caracteres) e deixou a tabela criada sem FKs nem registo em
        // `migrations`. A tabela é nova e ficou vazia, por isso é seguro
        // recriá-la.
        Schema::dropIfExists('import_opportunity_checklist_entries');

        // Nomes explícitos: os gerados pelo Laravel passam o limite de 64
        // caracteres do MySQL.
        Schema::create('import_opportunity_checklist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_opportunity_id')
                ->constrained(indexName: 'import_opp_checklist_entries_opportunity_fk')
                ->cascadeOnDelete();
            $table->foreignId('opportunity_checklist_item_id')
                ->constrained(indexName: 'import_opp_checklist_entries_item_fk')
                ->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['import_opportunity_id', 'opportunity_checklist_item_id'], 'import_opp_checklist_entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_opportunity_checklist_entries');
    }
};
