<?php

namespace App\Exports;

use App\Enums\LeadSource;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Exportação de clientes (clients com is_lead = false). */
class ClientExport extends ExportDefinition
{
    public function type(): string
    {
        return 'clients';
    }

    public function filenamePrefix(): string
    {
        return 'clientes';
    }

    public function title(): string
    {
        return 'Clientes';
    }

    public function filterKeys(): array
    {
        return ['search', 'client_type'];
    }

    protected function baseQuery(array $filters): Builder
    {
        return Client::query()
            ->where('is_lead', false)
            ->filtrarClientes($filters);
    }

    protected function defineFields(): array
    {
        $i = 'Identificação';
        $c = 'Contacto';
        $r = 'Relação e origem';
        $k = 'Consentimentos';
        $a = 'Atividade';
        $d = 'Datas';

        return [
            ExportField::make('name', 'Nome', $i, default: true, width: 30),
            ExportField::make('client_type', 'Tipo de cliente', $i, reader: fn (Model $m) => self::normalizarTipo($m->client_type), default: true),
            ExportField::make('vat_number', 'NIF', $i, default: true, sensitive: true),
            ExportField::make('identification_number', 'Nº documento', $i, sensitive: true),
            ExportField::make('validate_identification_number', 'Validade do documento', $i, 'date', sensitive: true),
            ExportField::make('birth_date', 'Data de nascimento', $i, 'date', sensitive: true),
            ExportField::make('gender', 'Género', $i, reader: fn (Model $m) => self::normalizarGenero($m->gender), sensitive: true),
            ExportField::make('language', 'Idioma', $i),

            ExportField::make('phone', 'Telefone', $c, default: true),
            ExportField::make('email', 'Email', $c, default: true, width: 30),
            ExportField::make('city', 'Localidade', $c, default: true),
            ExportField::make('address', 'Morada', $c, width: 35),
            ExportField::make('postal_code', 'Código postal', $c),

            ExportField::make('owner', 'Angariador', $r, reader: 'owner.name', default: true, with: ['owner:id,name']),
            ExportField::make('angariador_code', 'Código de angariador', $r),
            ExportField::make('origin', 'Como conheceu', $r),
            ExportField::make('lead_source', 'Origem do registo', $r, reader: fn (Model $m) => LeadSource::labelOf($m->lead_source)),

            ExportField::make('data_processing_consent', 'Consentimento tratamento de dados', $k, 'bool', width: 20),
            ExportField::make('newsletter_consent', 'Consentimento newsletter', $k, 'bool', width: 20),

            ExportField::make('proposals_count', 'Nº de cotações', $a, 'int', fn (Model $m) => $m->proposals_count, counts: ['proposals']),
            ExportField::make('converted_proposals_count', 'Nº de cotações convertidas', $a, 'int', fn (Model $m) => $m->converted_proposals_count, counts: ['convertedProposals']),
            ExportField::make('vehicles_count', 'Nº de viaturas', $a, 'int', fn (Model $m) => $m->vehicles_count, counts: ['vehicles']),
            ExportField::make('sales_count', 'Nº de compras', $a, 'int', fn (Model $m) => $m->sale_count, counts: ['sale']),
            ExportField::make('last_activity', 'Última atividade', $a, 'datetime', 'latestActivity.created_at', with: ['latestActivity']),

            ExportField::make('created_at', 'Criado em', $d, 'datetime', default: true),
            ExportField::make('converted_at', 'Convertido em', $d, 'date'),

            ExportField::make('observation', 'Observações', 'Observações', width: 50),
        ];
    }

    /** "particular"/"EMPRESA" → "Particular"/"Empresa"; outros valores saem como estão. */
    public static function normalizarTipo(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return match (mb_strtolower($valor)) {
            '' => null,
            'particular' => 'Particular',
            'empresa' => 'Empresa',
            default => $valor,
        };
    }

    /** A BD tem "masculino/feminino" (formulário) e "Homem/Mulher/Outro" (esquema antigo). */
    public static function normalizarGenero(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return match (mb_strtolower($valor)) {
            '' => null,
            'masculino', 'homem' => 'Masculino',
            'feminino', 'mulher' => 'Feminino',
            'outro' => 'Outro',
            default => $valor,
        };
    }
}
