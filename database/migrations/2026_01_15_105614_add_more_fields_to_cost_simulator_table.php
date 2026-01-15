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
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->string('emissao_particulas')->nullable();
            $table->string('tipo_veiculo')->nullable();
            $table->string('autonomia')->nullable();
            $table->string('pais_matricula')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            //
            $table->dropColumn(['emissao_particulas', 'tipo_veiculo', 'autonomia', 'pais_matricula']);
        });
    }
};
