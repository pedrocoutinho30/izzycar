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
        Schema::create('form_proposals', function (Blueprint $table) {
            $table->id();

            // Dados principais
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('client_id')->nullable(); // se existir na BD
            $table->string('status')->default('novo'); // novo, em_processo, concluido
            // Como conheceu
            $table->string('source')->nullable();

            // Mensagem geral
            $table->text('message')->nullable();

            // Anúncio identificado
            $table->enum('ad_option', ['sim', 'nao_sei', 'nao_nao'])->nullable();
            $table->text('ad_links')->nullable();

            // Preferências caso "nao_sei"
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('version')->nullable();
            $table->string('fuel')->nullable();
            $table->integer('year_min')->nullable();
            $table->integer('km_max')->nullable();
            $table->string('color')->nullable();
            $table->string('budget')->nullable();
            $table->enum('gearbox', ['indiferente', 'automatica', 'manual'])->nullable();
            $table->text('extras')->nullable();
            $table->integer('proposal_id')->nullable(); // FK para proposals.id se convertido

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_proposals');
    }
};
