<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    // ── Exportar Leads ────────────────────────────────────────────────────────
    public function leads(Request $request): StreamedResponse
    {
        $query = Client::where('is_lead', true)->orderBy('created_at', 'desc');

        // Respeita os mesmos filtros da listagem
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"));
        }
        if ($request->filled('lead_source'))  $query->where('lead_source', $request->lead_source);
        if ($request->filled('lead_status'))  $query->where('lead_status', $request->lead_status);

        $leads = $query->get();

        $sourceLabels = [
            'simulador'  => 'Simulador de Custos',
            'importacao' => 'Formulário de Importação',
            'retoma'     => 'Retoma',
            'manual'     => 'Manual',
        ];
        $statusLabels = [
            'nova'        => 'Nova',
            'em_contacto' => 'Em Contacto',
            'fria'        => 'Fria',
            'perdida'     => 'Perdida',
        ];

        $filename = 'leads_' . now()->format('Ymd_His') . '.csv';

        return $this->streamCsv($filename, function () use ($leads, $sourceLabels, $statusLabels) {
            $headers = ['Nome', 'Email', 'Telefone', 'Origem', 'Estado', 'Follow-up', 'Nota Follow-up', 'Data de Entrada'];
            $this->csvRow($headers);

            foreach ($leads as $lead) {
                $this->csvRow([
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $sourceLabels[$lead->lead_source] ?? $lead->lead_source,
                    $statusLabels[$lead->lead_status ?? 'nova'] ?? $lead->lead_status,
                    $lead->next_followup_at?->format('d/m/Y H:i'),
                    $lead->followup_note,
                    $lead->created_at->format('d/m/Y H:i'),
                ]);
            }
        });
    }

    // ── Exportar Clientes ─────────────────────────────────────────────────────
    public function clients(Request $request): StreamedResponse
    {
        $query = Client::where('is_lead', false)->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('vat_number', 'like', "%{$s}%"));
        }
        if ($request->filled('client_type')) $query->where('client_type', $request->client_type);

        $clients = $query->get();
        $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';

        return $this->streamCsv($filename, function () use ($clients) {
            $this->csvRow(['Nome', 'Email', 'Telefone', 'NIF', 'Tipo', 'Origem', 'Morada', 'Código Postal', 'Cidade', 'Data Conversão', 'Data Registo']);

            foreach ($clients as $c) {
                $this->csvRow([
                    $c->name,
                    $c->email,
                    $c->phone,
                    $c->vat_number,
                    $c->client_type,
                    $c->origin,
                    $c->address,
                    $c->postal_code,
                    $c->city,
                    $c->converted_at?->format('d/m/Y'),
                    $c->created_at->format('d/m/Y'),
                ]);
            }
        });
    }

    // ── Exportar Cotações ─────────────────────────────────────────────────────
    public function proposals(Request $request): StreamedResponse
    {
        $query = Proposal::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('brand', 'like', "%{$s}%")
                ->orWhere('model', 'like', "%{$s}%")
                ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$s}%")));
        }
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $proposals = $query->get();
        $filename  = 'cotacoes_' . now()->format('Ymd_His') . '.csv';

        return $this->streamCsv($filename, function () use ($proposals) {
            $this->csvRow(['ID', 'Cliente', 'Email Cliente', 'Marca', 'Modelo', 'Versão', 'Combustível', 'Ano', 'Estado', 'Preço Total (€)', 'Data Criação']);

            foreach ($proposals as $p) {
                $this->csvRow([
                    $p->id,
                    $p->client?->name,
                    $p->client?->email,
                    $p->brand,
                    $p->model,
                    $p->version,
                    $p->fuel,
                    $p->proposed_car_year_month,
                    $p->status,
                    $p->total_price ? number_format($p->total_price, 2, ',', '.') : '',
                    $p->created_at->format('d/m/Y H:i'),
                ]);
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function streamCsv(string $filename, callable $callback): StreamedResponse
    {
        return response()->streamDownload(function () use ($callback) {
            // BOM UTF-8 para Excel abrir correctamente com acentos
            echo "\xEF\xBB\xBF";
            $callback();
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function csvRow(array $fields): void
    {
        $out = fopen('php://output', 'w');
        fputcsv($out, $fields, ';');
        fclose($out);
    }
}
