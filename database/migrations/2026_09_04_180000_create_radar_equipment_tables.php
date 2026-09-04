<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Item canónico de equipamento (ex.: "Bancos elétricos") - descoberto
        // automaticamente pelo scraper (ver radar_equipment_aliases), escondido dos
        // filtros por omissão até o utilizador decidir mostrá-lo.
        Schema::create('radar_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('slug');
            $table->boolean('show_in_filters')->default(false);
            $table->timestamps();
        });

        // Texto/chave em bruto tal como cada site o expõe (alemão na AutoScout24,
        // chave técnica na Standvirtual) - liga-se a um item canónico. Novo
        // (source, raw_key) nunca visto antes cria um item canónico novo; o
        // utilizador funde manualmente aliases equivalentes entre sites.
        Schema::create('radar_equipment_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radar_equipment_id')->constrained('radar_equipment')->cascadeOnDelete();
            $table->string('source');
            $table->string('raw_key');
            $table->timestamps();

            $table->unique(['source', 'raw_key']);
        });

        // Que equipamento cada anúncio tem - só preenchido para anúncios NOVOS a
        // partir de agora (sem backfill retroativo dos já existentes, por decisão
        // explícita - custaria demasiados pedidos HTTP de uma vez).
        Schema::create('radar_listing_equipment', function (Blueprint $table) {
            $table->foreignId('radar_listing_id')->constrained('radar_listings')->cascadeOnDelete();
            $table->foreignId('radar_equipment_id')->constrained('radar_equipment')->cascadeOnDelete();
            $table->primary(['radar_listing_id', 'radar_equipment_id']);
        });

        // Equipamento exigido por uma pesquisa (filtro "E" - o anúncio tem de ter
        // TODOS os selecionados). Só editável depois de a pesquisa já ter anúncios
        // com equipamento capturado (ver formulário de edição).
        Schema::create('radar_search_equipment', function (Blueprint $table) {
            $table->foreignId('radar_search_id')->constrained('radar_searches')->cascadeOnDelete();
            $table->foreignId('radar_equipment_id')->constrained('radar_equipment')->cascadeOnDelete();
            $table->primary(['radar_search_id', 'radar_equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_search_equipment');
        Schema::dropIfExists('radar_listing_equipment');
        Schema::dropIfExists('radar_equipment_aliases');
        Schema::dropIfExists('radar_equipment');
    }
};
