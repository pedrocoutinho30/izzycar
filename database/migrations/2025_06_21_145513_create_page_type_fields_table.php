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
        Schema::create('page_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_type_id')->constrained()->onDelete('cascade');
            $table->string('name'); // ex: title, image, description
            $table->string('label'); // ex: Título, Imagem de capa
            $table->enum('type', ['text', 'textarea', 'image', 'file', 'boolean', 'select', 'repeater', 'gallery', 'wysiwyg', 'radio', 'page', 'date', 'datetime'])
                ->default('text'); // Tipos de campo disponíveis
            $table->json('options')->nullable(); // para selects, repeters, etc.
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_type_fields');
    }
};
