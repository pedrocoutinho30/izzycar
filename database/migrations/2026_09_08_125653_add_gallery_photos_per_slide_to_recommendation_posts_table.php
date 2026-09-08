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
            $table->unsignedTinyInteger('gallery_photos_per_slide')->default(3)->after('gallery_layouts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendation_posts', function (Blueprint $table) {
            $table->dropColumn('gallery_photos_per_slide');
        });
    }
};
