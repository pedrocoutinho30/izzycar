<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_candidates', function (Blueprint $table) {
            $table->text('link')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('car_candidates', function (Blueprint $table) {
            $table->string('link')->nullable()->change();
        });
    }
};
