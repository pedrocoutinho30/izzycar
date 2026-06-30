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
        Schema::create('cms_block_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('label');
            $table->string('layout', 50)->default('cards-grid');
            $table->json('fields')->nullable();
            $table->boolean('system')->default(false);
            $table->boolean('active')->default(true);
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('cms_blocks', function (Blueprint $table) {
            $table->string('layout', 50)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('cms_blocks', function (Blueprint $table) {
            $table->dropColumn('layout');
        });
        Schema::dropIfExists('cms_block_types');
    }
};
