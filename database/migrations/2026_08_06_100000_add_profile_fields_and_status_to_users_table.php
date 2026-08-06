<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('location')->nullable()->after('phone');
            $table->string('nif', 20)->nullable()->after('location');
            $table->string('iban', 40)->nullable()->after('nif');
            // 'pendente' (candidatura por aprovar), 'aprovado', 'rejeitado'.
            // Utilizadores criados diretamente pelo admin ficam sempre 'aprovado'.
            $table->string('status')->default('aprovado')->after('iban');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'location', 'nif', 'iban', 'status']);
        });
    }
};
