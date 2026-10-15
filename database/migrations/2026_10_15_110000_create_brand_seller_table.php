<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Pivot muitos-para-muitos entre marcas e vendedores (um vendedor trabalha várias marcas).
 * Backfill: associa cada vendedor às marcas das viaturas V3 e oportunidades onde já aparece.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_seller', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('seller_id');
            $table->timestamps();

            $table->foreign('brand_id', 'fk_brand_seller_brand')->references('id')->on('brands')->cascadeOnDelete();
            $table->foreign('seller_id', 'fk_brand_seller_seller')->references('id')->on('sellers')->cascadeOnDelete();
            $table->unique(['brand_id', 'seller_id'], 'uq_brand_seller');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_seller');
    }

    private function backfill(): void
    {
        // Mapa nome normalizado => brand_id (tabela pequena).
        $brands = [];
        foreach (DB::table('brands')->select('id', 'name')->orderBy('id')->get() as $brand) {
            $brands[$this->normalize($brand->name)] ??= $brand->id;
        }
        if ($brands === []) {
            return;
        }

        $pairs = [];
        foreach (['v3_vehicles', 'import_opportunities'] as $table) {
            // Pares distintos (vendedor, marca): uma só query por tabela.
            $rows = DB::table($table)
                ->whereNotNull('seller_id')
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->select('seller_id', 'brand')
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                $brandId = $brands[$this->normalize($row->brand)] ?? null;
                if ($brandId !== null) {
                    $pairs[$brandId . '-' . $row->seller_id] = [
                        'brand_id' => $brandId,
                        'seller_id' => $row->seller_id,
                    ];
                }
            }
        }

        if ($pairs === []) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now], array_values($pairs));

        // insertOrIgnore respeita o unique: idempotente e sem duplicados.
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('brand_seller')->insertOrIgnore($chunk);
        }
    }

    private function normalize(?string $value): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', Str::ascii((string) $value))));
    }
};
