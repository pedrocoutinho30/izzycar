<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Legalization extends Model
{
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'v3_vehicle_id',
        'marca',
        'modelo',
        'combustivel',
        'matricula',
        'num_homologacao',
        'num_processo_imt',
        'notas',
        'regime_especial_isv',
        'invoice_path',
        'modelo9_dados',
        'modelo1460_dados',
        'steps_completed',
    ];

    protected $casts = [
        'steps_completed'    => 'array',
        'regime_especial_isv' => 'boolean',
        'email_enviado'       => 'boolean',
        'modelo9_dados'       => 'array',
        'modelo1460_dados'    => 'array',
    ];

    // ---------------------------------------------------------------
    // Documentos standard (obrigatórios em todos os processos)
    // ---------------------------------------------------------------
    const DOCUMENTOS = [
        'CMATR'                   => 'CMATR — Documento estrangeiro equiv. DUA (Livrete/título de registo)',
        'DHTEC'                   => 'DHTEC — Formulário Modelo 9 IMT',
        'FATDV'                   => 'FATDV — Fatura de compra ou declaração de venda',
        'CCEUR'                   => 'CCEUR — Certificado de conformidade (COC)',
        'guia_transporte'         => 'Guia de transporte (opcional, dependendo do tipo de transporte)',
        'CCIDA'                   => 'CCIDA — Cartão de cidadão do proprietário',
        'CINSP'                   => 'CINSP — Certificado de inspeção (Modelo 112)',
        // DAV é um documento diferente do MD1460 (pedido de isenção de ISV, ver DOCUMENTOS_REGIME_ESPECIAL)
        'dav'                     => 'DAV (Declaração Aduaneira de Veículo)',
        'declaracao_homologacao'  => 'Declaração de Homologação',
        // PRODH aplica-se sempre (regime normal ou especial) — caso a DAV não seja submetida pelo próprio proprietário
        'PRODH'                   => 'PRODH — Procuração ou documento de habilitação',
    ];

    // ---------------------------------------------------------------
    // Documentos adicionais — apenas quando regime especial ISV ativo
    // ---------------------------------------------------------------
    const DOCUMENTOS_REGIME_ESPECIAL = [
        'CRESI'  => 'CRESI — Certificado de residência oficial',
        'DVIDQ'  => 'DVIDQ — Documentos de vida quotidiana',
        'CSTRI'  => 'CSTRI — Certidão de situação tributária',
        'MD1460' => 'MD1460 — Pedido de isenção de ISV',
    ];

    // ---------------------------------------------------------------
    // Definição dos passos de legalização
    // ---------------------------------------------------------------
    const PASSOS = [
        1 => [
            'titulo'     => 'Obter número de homologação nacional no IMT',
            'link'       => 'https://chnac.imt-ip.pt/',
            'link_label' => 'Abrir portal CHNAC',
            'docs'       => ['CCEUR', 'CMATR'],
            'info'       => null,
        ],
        2 => [
            'titulo'     => 'Inspeção B e obtenção do Modelo 112',
            'link'       => null,
            'link_label' => null,
            'docs'       => ['DHTEC'],
            'info'       => null,
        ],
        3 => [
            'titulo'     => 'Preenchimento da DAV no portal das Finanças',
            'link'       => null,
            'link_label' => null,
            'docs'       => ['CCEUR', 'CMATR', 'DHTEC', 'FATDV', 'guia_transporte', 'CCIDA', 'PRODH'],
            'info'       => null,
        ],
        4 => [
            'titulo'     => 'Fazer o pagamento do ISV',
            'link'       => null,
            'link_label' => null,
            'docs'       => [],
            'info'       => 'Não são necessários documentos específicos.',
        ],
        5 => [
            'titulo'     => 'Fazer chapas de matrícula e contratar seguro',
            'link'       => null,
            'link_label' => null,
            'docs'       => [],
            'info'       => 'Não são necessários documentos específicos.',
        ],
        6 => [
            'titulo'     => 'Entregar Modelo 9 no IMT',
            'link'       => null,
            'link_label' => null,
            'docs'       => ['CCEUR', 'DHTEC', 'CMATR', 'dav', 'CINSP', 'CCIDA', 'declaracao_homologacao'],
            'info'       => null,
        ],
        7 => [
            'titulo'     => 'Registo inicial na Conservatória do Registo Automóvel',
            'link'       => null,
            'link_label' => null,
            'docs'       => [],
            'info'       => 'Aguardar o registo na conservatória (após emissão do DUA).',
        ],
    ];

    /** Nomes curtos dos passos, para colunas e etiquetas pequenas (ex.: kanban). */
    const PASSOS_CURTOS = [
        1 => 'Homologação IMT',
        2 => 'Inspeção B',
        3 => 'DAV',
        4 => 'Pagar ISV',
        5 => 'Chapas e seguro',
        6 => 'Modelo 9',
        7 => 'Registo',
    ];

    // ---------------------------------------------------------------
    // Boot — gera automaticamente o token público de acompanhamento
    // ---------------------------------------------------------------
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Legalization $legalization) {
            if (empty($legalization->token)) {
                $legalization->token = Str::random(32);
            }
        });
    }

    // ---------------------------------------------------------------
    // Relações
    // ---------------------------------------------------------------
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Viatura V3 ligada (a fonte de verdade); `vehicle()` é o legado V1. */
    public function v3Vehicle(): BelongsTo
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class)->orderByDesc('expense_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LegalizationDocument::class);
    }

    // ---------------------------------------------------------------
    // Filtros da listagem (partilhados com a exportação XLSX)
    // ---------------------------------------------------------------

    /** Estados da listagem, pelo nº de passos concluídos (como as estatísticas). */
    const ESTADOS = [
        'nova'      => 'Nova (0 passos)',
        'em_curso'  => 'Em curso (1 a 6 passos)',
        'concluida' => 'Concluída (7 passos)',
    ];

    /**
     * Intervalo de datas (sobre created_at, inclusivo) e/ou estado. Chaves
     * vazias ou desconhecidas são ignoradas. O estado conta os elementos do
     * JSON em SQL (JSON_LENGTH no MySQL, json_array_length no sqlite).
     *
     * @param  array{date_from?: ?string, date_to?: ?string, estado?: ?string}  $filters
     */
    public function scopeFiltrar(Builder $query, array $filters): Builder
    {
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->when($filters['estado'] ?? null, fn (Builder $q, string $estado) => $q->estado($estado));
    }

    /** Só o filtro de estado (nova, em_curso, concluida); outro valor não filtra. */
    public function scopeEstado(Builder $query, string $estado): Builder
    {
        $total = count(self::PASSOS);

        return match ($estado) {
            // sem passos: coluna nula ou lista vazia
            'nova' => $query->where(fn (Builder $q) => $q->whereNull('steps_completed')->orWhereJsonLength('steps_completed', 0)),
            'em_curso' => $query->whereJsonLength('steps_completed', '>', 0)->whereJsonLength('steps_completed', '<', $total),
            'concluida' => $query->whereJsonLength('steps_completed', '>=', $total),
            default => $query,
        };
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function allDocumentos(): array
    {
        $docs = self::DOCUMENTOS;
        if ($this->regime_especial_isv) {
            $docs = array_merge($docs, self::DOCUMENTOS_REGIME_ESPECIAL);
        }
        return $docs;
    }

    public function hasDocument(string $tipo): bool
    {
        return $this->documents->contains('tipo', $tipo);
    }

    public function isStepCompleted(int $step): bool
    {
        return in_array($step, $this->steps_completed ?? []);
    }

    public function progressPercent(): int
    {
        $completed = count($this->steps_completed ?? []);
        return (int) round($completed / count(self::PASSOS) * 100);
    }

    /**
     * Passo em que o processo está: o primeiro por concluir (os passos fazem-se
     * por ordem). Null quando os 7 estão concluídos.
     */
    public function currentStep(): ?int
    {
        foreach (array_keys(self::PASSOS) as $step) {
            if (! $this->isStepCompleted($step)) {
                return $step;
            }
        }

        return null;
    }

    public function trackingUrl(): string
    {
        return route('frontend.legalization.status', $this->token);
    }

    // ---------------------------------------------------------------
    // Versões traduzidas (para a página pública e o email de acompanhamento)
    // Usam o idioma atual da app (App::setLocale) — o BO continua a usar
    // PASSOS/DOCUMENTOS diretamente, sem tradução.
    // ---------------------------------------------------------------

    public function passosTranslated(): array
    {
        $passos = self::PASSOS;
        foreach ($passos as $num => &$passo) {
            $passo['titulo'] = __('legalization.passos.' . $num);
        }
        return $passos;
    }

    public function allDocumentosTranslated(): array
    {
        $translated = [];
        foreach ($this->allDocumentos() as $slug => $ptLabel) {
            $description = __('legalization.documentos.' . $slug);
            $translated[$slug] = preg_match('/^[A-Z0-9]+$/', $slug)
                ? "{$slug} — {$description}"
                : $description;
        }
        return $translated;
    }
}
