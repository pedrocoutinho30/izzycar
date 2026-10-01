<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema de produção que as migrations não reproduzem (ambientes novos e
 * os testes ficavam diferentes). Em produção não faz nada:
 * - colunas da comissão do angariador em converted_proposals (ver
 *   2026_08_05_140000, que já as dava como existentes);
 * - status_proposal_history.old_status aceita nulo (a primeira entrada do
 *   histórico, "Iniciada", não tem estado anterior).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('converted_proposals', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('converted_proposals', 'comissao_paga')) {
                $table->boolean('comissao_paga')->default(false);
            }
            if (!Schema::hasColumn('converted_proposals', 'comissao_paga_em')) {
                $table->date('comissao_paga_em')->nullable();
            }
        });

        $oldStatus = collect(Schema::getColumns('status_proposal_history'))->firstWhere('name', 'old_status');
        if ($oldStatus && !$oldStatus['nullable']) {
            Schema::table('status_proposal_history', function (Blueprint $table) {
                $table->string('old_status')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Não remover: em produção as colunas são anteriores a esta migration.
    }
};
