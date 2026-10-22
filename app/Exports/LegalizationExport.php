<?php

namespace App\Exports;

use App\Models\Legalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Exportação de legalizações. Não inclui custos nem margens, o token público,
 * caminhos de ficheiros, dados dos modelos 9/1460, o estado do email nem o
 * JSON bruto dos passos.
 */
class LegalizationExport extends ExportDefinition
{
    public function type(): string
    {
        return 'legalizations';
    }

    public function filenamePrefix(): string
    {
        return 'legalizacoes';
    }

    public function title(): string
    {
        return 'Legalizações';
    }

    public function filterKeys(): array
    {
        return ['date_from', 'date_to', 'estado'];
    }

    protected function baseQuery(array $filters): Builder
    {
        return Legalization::query()->filtrar($filters);
    }

    protected function defineFields(): array
    {
        $p = 'Processo';
        $c = 'Cliente';
        $v = 'Viatura';
        $i = 'Identificadores IMT';
        $l = 'Viatura V3';

        $fields = [
            ExportField::make('id', 'Nº do processo', $p, 'int', default: true),
            ExportField::make('created_at', 'Criado em', $p, 'date', default: true),
            ExportField::make('current_step', 'Passo atual', $p, reader: function (Model $m) {
                $step = $m->currentStep();

                return $step === null ? 'Concluída' : Legalization::PASSOS_CURTOS[$step];
            }, default: true, width: 20),
            // progressPercent() já devolve 0-100, como o tipo "percent" espera
            ExportField::make('progress', 'Progresso', $p, 'percent', fn (Model $m) => $m->progressPercent(), default: true),
            ExportField::make('steps_done', 'Passos concluídos', $p, reader: function (Model $m) {
                $done = count(array_intersect(array_keys(Legalization::PASSOS), $m->steps_completed ?? []));

                return $done . '/' . count(Legalization::PASSOS);
            }),
        ];

        foreach (Legalization::PASSOS_CURTOS as $step => $label) {
            $fields[] = ExportField::make("step_{$step}", $label, $p, 'bool', fn (Model $m) => $m->isStepCompleted($step));
        }

        return array_merge($fields, [
            ExportField::make('regime_especial_isv', 'Regime especial ISV', $p, 'bool', fn (Model $m) => (bool) $m->regime_especial_isv, width: 20),
            ExportField::make('documents_count', 'Nº de documentos', $p, 'int', fn (Model $m) => $m->documents_count, default: true, counts: ['documents']),
            ExportField::make('missing_documents', 'Documentos em falta', $p, reader: fn (Model $m) => $this->missingDocuments($m), with: ['documents:id,legalization_id,tipo'], width: 40),
            ExportField::make('notas', 'Notas', $p, width: 50),

            ExportField::make('client', 'Cliente', $c, reader: 'client.name', default: true, with: ['client:id,name'], width: 30),

            ExportField::make('marca', 'Marca', $v, default: true),
            ExportField::make('modelo', 'Modelo', $v, default: true),
            ExportField::make('matricula', 'Matrícula', $v, default: true),
            ExportField::make('combustivel', 'Combustível', $v),

            ExportField::make('num_homologacao', 'Nº homologação', $i, default: true, width: 24),
            ExportField::make('num_processo_imt', 'Nº processo IMT', $i, width: 24),

            ExportField::make('v3_reference', 'Referência V3', $l, reader: 'v3Vehicle.reference', with: ['v3Vehicle:id,reference,vin']),
            ExportField::make('v3_vin', 'VIN', $l, reader: 'v3Vehicle.vin', with: ['v3Vehicle:id,reference,vin'], width: 22),
        ]);
    }

    /** Documentos do processo (incl. regime especial) sem ficheiro carregado, por código/nome curto. */
    private function missingDocuments(Model $m): string
    {
        $uploaded = $m->documents->pluck('tipo')->all();
        $missing = [];
        foreach ($m->allDocumentos() as $tipo => $label) {
            if (! in_array($tipo, $uploaded, true)) {
                $missing[] = trim(preg_split('/ [—(] ?/u', $label)[0]);
            }
        }

        return $missing ? implode(', ', $missing) : 'Nenhum';
    }
}
