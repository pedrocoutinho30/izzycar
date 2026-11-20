<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipo, estado e formato são strings (não ENUM) para evoluir sem migrations.
        Schema::create('marketing_posts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('template', 40);
            $table->string('format', 30);
            $table->string('status', 20)->default('rascunho');
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->text('hashtags')->nullable();
            $table->string('cta')->nullable();
            $table->string('tone', 30)->nullable();
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('data')->nullable();
            $table->json('config')->nullable();
            $table->dateTime('scheduled_for')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('channel', 30)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('status', 'mkt_posts_status_idx');
            $table->index('scheduled_for', 'mkt_posts_sched_idx');
            $table->index(['source_type', 'source_id'], 'mkt_posts_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_posts');
    }
};
