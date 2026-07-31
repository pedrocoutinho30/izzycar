<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->boolean('email_enviado')->default(false)->after('invoice_path');
        });

        // Legalizações já existentes não devem disparar o email retroativamente
        DB::table('legalizations')->update(['email_enviado' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn('email_enviado');
        });
    }
};
