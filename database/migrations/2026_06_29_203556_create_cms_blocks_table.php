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
        Schema::create('cms_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_id')->constrained()->cascadeOnDelete();
            // tipo do bloco: hero, badges, text, cards, steps, cta, costs, faq
            $table->string('type', 30);
            $table->string('name');           // nome interno/label do bloco
            $table->string('title')->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->text('body')->nullable();  // texto rico (HTML)
            $table->json('data')->nullable();  // itens do repeater (cards, steps, faq, etc.)
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('button2_text')->nullable();
            $table->string('button2_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_blocks');
    }
};
