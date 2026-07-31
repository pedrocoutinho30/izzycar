<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->unique()->after('id');
        });

        // Backfill tokens para legalizações já existentes (via query builder — 'token' não é mass-assignable no model)
        foreach (\Illuminate\Support\Facades\DB::table('legalizations')->whereNull('token')->pluck('id') as $id) {
            \Illuminate\Support\Facades\DB::table('legalizations')->where('id', $id)->update(['token' => Str::random(32)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
