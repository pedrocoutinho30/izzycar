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
        Schema::create('vehicle_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('type'); // number, boolean, text, select, etc
            $table->json('options')->nullable(); // only used for select
            $table->integer('order')->default(0); // order of display
            $table->string('attribute_group')->nullable(); // group for categorization (e.g
            $table->string('field_name_autoscout')->nullable(); // field name in AutoScout24
            $table->string('field_name_mobile')->nullable(); // field name in Mobile.de
            $table->timestamps();
        });

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_attributes');
    }
};
