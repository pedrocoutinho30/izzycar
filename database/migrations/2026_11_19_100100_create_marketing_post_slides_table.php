<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_post_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('layout', 40)->nullable();
            $table->json('content')->nullable();
            $table->timestamps();

            $table->foreign('post_id', 'mkt_slides_post_fk')->references('id')->on('marketing_posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_post_slides');
    }
};
