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
        Schema::table('newsletter_offers', function (Blueprint $table) {
            $table->foreignId('newsletter_id')->nullable()->after('id')->constrained('newsletters')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_offers', function (Blueprint $table) {
            $table->dropForeign(['newsletter_id']);
            $table->dropColumn('newsletter_id');
        });
    }
};
