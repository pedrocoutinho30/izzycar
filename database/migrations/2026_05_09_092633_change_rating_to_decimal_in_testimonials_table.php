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
        Schema::table('testimonials', function (Blueprint $table) {
            DB::statement('ALTER TABLE testimonials MODIFY COLUMN rating DECIMAL(3,1) NOT NULL DEFAULT 5.0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            DB::statement('ALTER TABLE testimonials MODIFY COLUMN rating TINYINT UNSIGNED NOT NULL DEFAULT 5');
        });
    }
};
