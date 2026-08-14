<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->json('modelo1460_dados')->nullable()->after('modelo9_dados');
        });
    }

    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn('modelo1460_dados');
        });
    }
};
