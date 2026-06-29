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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_lead')->default(false)->after('observation');
            $table->timestamp('converted_at')->nullable()->after('is_lead');
            $table->string('lead_source')->nullable()->after('converted_at'); // simulador, importacao, retoma, manual
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['is_lead', 'converted_at', 'lead_source']);
        });
    }
};
