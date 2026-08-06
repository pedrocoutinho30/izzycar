<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('angariador_code')->nullable()->after('owner_id');
        });

        Schema::table('form_proposals', function (Blueprint $table) {
            $table->string('angariador_code')->nullable()->after('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('angariador_code');
        });

        Schema::table('form_proposals', function (Blueprint $table) {
            $table->dropColumn('angariador_code');
        });
    }
};
