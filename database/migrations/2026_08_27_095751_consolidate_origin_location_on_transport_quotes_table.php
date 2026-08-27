<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_quotes', function (Blueprint $table) {
            $table->string('origin_location')->nullable()->after('car_type');
        });

        // Preserva os dados já existentes juntando cidade + código postal + país
        // no novo campo único, antes de apagar as colunas antigas.
        DB::table('transport_quotes')->orderBy('id')->chunk(100, function ($quotes) {
            foreach ($quotes as $quote) {
                $parts = array_filter([
                    $quote->origin_city,
                    $quote->origin_postal_code,
                    $quote->origin_country,
                ]);

                DB::table('transport_quotes')
                    ->where('id', $quote->id)
                    ->update(['origin_location' => implode(', ', $parts)]);
            }
        });

        Schema::table('transport_quotes', function (Blueprint $table) {
            $table->dropColumn(['origin_city', 'origin_country', 'origin_postal_code']);
        });
    }

    public function down(): void
    {
        Schema::table('transport_quotes', function (Blueprint $table) {
            $table->string('origin_city')->nullable()->after('origin_location');
            $table->string('origin_country')->nullable()->after('origin_city');
            $table->string('origin_postal_code')->nullable()->after('origin_country');
        });

        // Nota: não é possível reconstruir os 3 campos separados a partir do
        // texto livre com fiabilidade — ficam vazios no rollback.
        Schema::table('transport_quotes', function (Blueprint $table) {
            $table->dropColumn('origin_location');
        });
    }
};
