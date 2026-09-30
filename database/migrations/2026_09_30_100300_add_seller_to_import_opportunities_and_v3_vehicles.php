<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oportunidades e veículos passam a apontar para o Vendedor/Contacto em vez de
 * guardarem cópias do nome/contacto. Os campos de texto livre das
 * oportunidades (seller_name/seller_contact) saem — só existiam oportunidades
 * de teste. O vendedor não pode ser eliminado enquanto tiver histórico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->dropColumn(['seller_name', 'seller_contact']);
        });

        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('vehicle_notes')->constrained()->restrictOnDelete();
            $table->foreignId('seller_contact_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();
        });

        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_contact_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_contact_id');
            $table->dropConstrainedForeignId('seller_id');
        });

        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_contact_id');
            $table->dropConstrainedForeignId('seller_id');
        });

        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->string('seller_name')->nullable()->after('vehicle_notes');
            $table->string('seller_contact')->nullable()->after('seller_name');
        });
    }
};
