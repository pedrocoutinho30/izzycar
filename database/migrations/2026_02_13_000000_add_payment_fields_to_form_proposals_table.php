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
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('message');
            $table->string('estimated_purchase_date')->nullable()->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'estimated_purchase_date']);
        });
    }
};
