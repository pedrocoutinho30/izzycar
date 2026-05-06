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
        Schema::table('legalizations', function (Blueprint $table) {
            $table->string('matricula')->nullable()->after('combustivel');
            $table->string('num_homologacao')->nullable()->after('matricula');
        });
    }

    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn(['matricula', 'num_homologacao']);
        });
    }
};
