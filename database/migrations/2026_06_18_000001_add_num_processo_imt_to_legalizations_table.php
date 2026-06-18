<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->string('num_processo_imt', 100)->nullable()->after('num_homologacao');
        });
    }

    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn('num_processo_imt');
        });
    }
};
