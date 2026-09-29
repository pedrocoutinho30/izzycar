<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tipos de checklist de informação/documentação das Oportunidades (Pedidos de
 * Importação). Configurável em base de dados: uma checklist com
 * applies_to_fuels = null aplica-se a todas as oportunidades; com uma lista
 * de combustíveis só aparece para esses (ex. "electric" → ["eletrico"]).
 *
 * Os itens base são inseridos aqui (e não num seeder) para existirem em
 * produção logo após o deploy.
 */
return new class extends Migration
{
    private const CHECKLISTS = [
        [
            'slug' => 'base',
            'name' => 'Informação e documentação',
            'applies_to_fuels' => null,
            'items' => [
                'vin' => 'VIN',
                'coc' => 'COC',
                'livro_revisoes' => 'Livro de revisões',
                'historico_manutencao' => 'Histórico de manutenção',
                'garantia' => 'Garantia',
                'certificado_soh' => 'Certificado SOH',
                'historico_acidentes' => 'Histórico de acidentes',
                'segunda_chave' => '2ª chave',
                'cabos_carregamento' => 'Cabos de carregamento',
                'manual' => 'Manual',
                'documentos_matricula' => 'Documentos de matrícula',
                'documentos_exportacao' => 'Documentos de exportação',
                'fatura' => 'Fatura',
                'preco_bruto_confirmado' => 'Preço bruto confirmado',
            ],
        ],
        [
            'slug' => 'electric',
            'name' => 'Veículo elétrico',
            'applies_to_fuels' => ['eletrico'],
            'items' => [
                'soh' => 'SOH',
                'capacidade_bateria' => 'Capacidade da bateria',
                'quimica_bateria' => 'Tipo/química da bateria',
                'teste_bateria' => 'Certificado/teste da bateria',
                'cabo_type2' => 'Cabo Type 2',
                'cabo_ccs' => 'Cabo CCS',
                'carregador_domestico' => 'Carregador doméstico',
                'heat_pump' => 'Heat Pump',
                'garantia_bateria' => 'Garantia da bateria',
                'garantia_veiculo' => 'Garantia do veículo',
                'historico_carregamento' => 'Histórico de carregamento',
            ],
        ],
    ];

    public function up(): void
    {
        Schema::create('opportunity_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->json('applies_to_fuels')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('opportunity_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['opportunity_checklist_id', 'slug']);
        });

        $now = now();

        foreach (self::CHECKLISTS as $checklistOrder => $checklist) {
            $checklistId = DB::table('opportunity_checklists')->insertGetId([
                'slug' => $checklist['slug'],
                'name' => $checklist['name'],
                'applies_to_fuels' => $checklist['applies_to_fuels'] ? json_encode($checklist['applies_to_fuels']) : null,
                'sort_order' => $checklistOrder,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('opportunity_checklist_items')->insert(
                collect(array_keys($checklist['items']))->map(fn ($slug, $index) => [
                    'opportunity_checklist_id' => $checklistId,
                    'slug' => $slug,
                    'label' => $checklist['items'][$slug],
                    'sort_order' => $index,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all()
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_checklist_items');
        Schema::dropIfExists('opportunity_checklists');
    }
};
