<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pessoas de um vendedor. As colunas *_normalized guardam email/telefones num
 * formato comparável (ver ContactNormalizer) para a deteção de duplicados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);

            $table->string('email_normalized')->nullable()->index();
            $table->string('email_domain')->nullable()->index();
            $table->string('phone_normalized', 20)->nullable()->index();
            $table->string('whatsapp_normalized', 20)->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_contacts');
    }
};
