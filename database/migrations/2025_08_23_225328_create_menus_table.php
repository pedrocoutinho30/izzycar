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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // nome do menu
            $table->string('url')->nullable(); // url do menu
            $table->unsignedBigInteger('parent_id')->nullable(); // FK para menus.id (submenu)
            $table->integer('order')->default(0); // ordem de exibição
            $table->timestamps();

            // chave estrangeira para submenu
            $table->foreign('parent_id')
                  ->references('id')->on('menus')
                  ->onDelete('cascade'); // se apagar menu pai, apaga submenus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
