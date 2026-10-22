<?php

namespace App\Exports;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Exportação de leads (clients com is_lead = true). */
class LeadExport extends ExportDefinition
{
    public function type(): string
    {
        return 'leads';
    }

    public function filenamePrefix(): string
    {
        return 'leads';
    }

    public function title(): string
    {
        return 'Leads';
    }

    public function filterKeys(): array
    {
        return ['search', 'lead_source', 'lead_status', 'owner_id'];
    }

    protected function baseQuery(array $filters): Builder
    {
        return Client::query()
            ->where('is_lead', true)
            ->filtrarLeads($filters);
    }

    protected function defineFields(): array
    {
        $g = 'Lead';
        $f = 'Seguimento';
        $o = 'Outros dados';

        return [
            ExportField::make('name', 'Nome', $g, default: true, width: 30),
            ExportField::make('phone', 'Telefone', $g, default: true),
            ExportField::make('email', 'Email', $g, default: true, width: 30),
            ExportField::make('lead_status', 'Estado', $g, reader: fn (Model $m) => LeadStatus::labelOf($m->lead_status), default: true),
            ExportField::make('lead_source', 'Origem', $g, reader: fn (Model $m) => LeadSource::labelOf($m->lead_source), default: true, width: 24),
            ExportField::make('owner', 'Angariador', $g, reader: 'owner.name', default: true, with: ['owner:id,name']),

            ExportField::make('next_followup_at', 'Próximo follow-up', $f, 'datetime', default: true),
            ExportField::make('followup_note', 'Nota de follow-up', $f, width: 35),
            ExportField::make('created_at', 'Criada em', $f, 'datetime', default: true),
            ExportField::make('last_activity', 'Última atividade', $f, 'datetime', 'latestActivity.created_at', with: ['latestActivity']),
            ExportField::make('form_proposals_count', 'Nº de pedidos de formulário', $f, 'int', fn (Model $m) => $m->form_proposals_count, counts: ['formProposals']),

            ExportField::make('origin', 'Como conheceu', $o),
            ExportField::make('language', 'Idioma', $o),
            ExportField::make('data_processing_consent', 'Consentimento tratamento de dados', $o, 'bool', width: 20),
            ExportField::make('newsletter_consent', 'Consentimento newsletter', $o, 'bool', width: 20),
            ExportField::make('observation', 'Observações', $o, width: 50),
        ];
    }
}
