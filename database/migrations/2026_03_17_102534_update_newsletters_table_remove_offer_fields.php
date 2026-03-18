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
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropForeign(['offer_1_id']);
            $table->dropForeign(['offer_2_id']);
            $table->dropForeign(['offer_3_id']);
            $table->dropColumn(['offer_1_id', 'offer_2_id', 'offer_3_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->foreignId('offer_1_id')->nullable()->constrained('newsletter_offers')->nullOnDelete();
            $table->foreignId('offer_2_id')->nullable()->constrained('newsletter_offers')->nullOnDelete();
            $table->foreignId('offer_3_id')->nullable()->constrained('newsletter_offers')->nullOnDelete();
        });
    }
};
