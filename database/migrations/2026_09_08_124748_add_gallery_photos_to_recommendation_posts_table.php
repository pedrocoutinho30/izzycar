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
        Schema::table('recommendation_posts', function (Blueprint $table) {
            $table->json('gallery_photos')->nullable()->after('image');
            $table->json('gallery_layouts')->nullable()->after('gallery_photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendation_posts', function (Blueprint $table) {
            $table->dropColumn(['gallery_photos', 'gallery_layouts']);
        });
    }
};
