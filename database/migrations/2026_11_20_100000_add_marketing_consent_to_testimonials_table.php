<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // Autorização do cliente para usar o testemunho em publicações (Marketing Studio).
            // Sem backfill: nenhum testemunho existente é elegível até ser marcado.
            $table->boolean('marketing_consent')->default(false)->after('published');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn('marketing_consent');
        });
    }
};
