<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // STEP 1 – Rename old 'type' (Empresa/Veiculo/Outros) to 'expense_category'
        //          so the new 'movement_type' field can take the 'type' concept.
        // ─────────────────────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('type', 'expense_category');
        });

        // ─────────────────────────────────────────────────────────────────────
        // STEP 2 – Convert vat_rate from enum/string → decimal(5,2)
        //          Old values: 'Sem iva', '6%', '19%', '23%', '25%'  or already numeric.
        // ─────────────────────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('vat_rate_decimal', 5, 2)->nullable()->after('vat_rate');
        });

        // Backfill using PHP to avoid DB-specific REGEXP syntax
        $rows = DB::table('expenses')->select('id', 'vat_rate')->get();
        foreach ($rows as $row) {
            $raw = $row->vat_rate ?? '';
            if (is_numeric($raw)) {
                $val = (float) $raw;
            } else {
                $stripped = trim(str_replace('%', '', $raw));
                $val      = is_numeric($stripped) ? (float) $stripped : 0.0;
            }
            DB::table('expenses')->where('id', $row->id)->update(['vat_rate_decimal' => $val]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vat_rate');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('vat_rate_decimal', 'vat_rate');
        });

        // ─────────────────────────────────────────────────────────────────────
        // STEP 3 – Add new columns for the extended movement model
        // ─────────────────────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            // New primary type classification
            $table->string('movement_type', 50)->default('expense')->after('id');
            // Richer category taxonomy (replaces old expense_category concept)
            $table->string('category', 100)->nullable()->after('expense_category');
            // Client association (for income movements)
            $table->unsignedBigInteger('client_id')->nullable()->after('vehicle_id');
            // Computed value breakdown
            $table->decimal('amount_gross', 12, 2)->nullable()->after('amount');
            $table->decimal('vat_amount',   12, 2)->nullable()->after('amount_gross');
            $table->decimal('amount_net',   12, 2)->nullable()->after('vat_amount');
            // Payment info
            $table->string('payment_method', 50)->nullable()->after('observations');
            $table->string('status', 50)->default('paid')->after('payment_method');
            // Source tracking (created from sale/vehicle/manual)
            $table->string('source_type', 100)->nullable()->after('attachment_path');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });

        // ─────────────────────────────────────────────────────────────────────
        // STEP 4 – Backfill computed columns from existing data
        // ─────────────────────────────────────────────────────────────────────
        $rows = DB::table('expenses')->select('id', 'amount', 'vat_rate', 'expense_category')->get();
        foreach ($rows as $row) {
            $gross   = (float) ($row->amount ?? 0);
            $vatRate = (float) ($row->vat_rate ?? 0);
            $vatAmt  = $vatRate > 0 ? round($gross - ($gross / (1 + $vatRate / 100)), 2) : 0.0;
            $net     = round($gross - $vatAmt, 2);

            // Map old expense_category to new category taxonomy
            $category = match ($row->expense_category) {
                'Transporte'          => 'transport',
                'Reparação'           => 'repair',
                'Seguro'              => 'insurance',
                'Inspeção Técnica',
                'IPO'                 => 'repair',
                'IMT', 'ISV', 'IUC'  => 'tax',
                'Documentação',
                'Matrículas',
                'Registo automóvel'   => 'legalization',
                'Manutenção', 'Peças' => 'repair',
                'Reboque'             => 'transport',
                default               => 'other',
            };

            DB::table('expenses')->where('id', $row->id)->update([
                'movement_type' => 'expense',
                'category'      => $category,
                'status'        => 'paid',
                'amount_gross'  => $gross,
                'vat_amount'    => $vatAmt,
                'amount_net'    => $net,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'movement_type',
                'category',
                'client_id',
                'amount_gross',
                'vat_amount',
                'amount_net',
                'payment_method',
                'status',
                'source_type',
                'source_id',
            ]);
        });

        // Restore vat_rate as string
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('vat_rate_str', 20)->nullable()->after('vat_rate');
        });
        $rows = DB::table('expenses')->select('id', 'vat_rate')->get();
        foreach ($rows as $row) {
            $str = ($row->vat_rate == 0 || $row->vat_rate === null) ? 'Sem iva' : ((int) $row->vat_rate . '%');
            DB::table('expenses')->where('id', $row->id)->update(['vat_rate_str' => $str]);
        }
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vat_rate');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('vat_rate_str', 'vat_rate');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('expense_category', 'type');
        });
    }
};
