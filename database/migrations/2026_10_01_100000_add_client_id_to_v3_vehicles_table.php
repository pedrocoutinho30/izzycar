<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cliente de uma viatura que não é vendida pelo stand — numa importação, o
 * carro é do cliente desde o início (não há venda).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('is_imported')->constrained('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
