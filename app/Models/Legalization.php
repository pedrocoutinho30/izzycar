<?php

namespace App\Models;

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
        'steps_completed',
    ];

    protected $casts = [
        'steps_completed'    => 'array',
        'regime_especial_isv' => 'boolean',
        'email_enviado'       => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Documentos standard (obrigatórios em todos os processos)
    // ---------------------------------------------------------------
    const DOCUMENTOS = [
        'CMATR'                   => 'CMATR — Documento estrangeiro equiv. DUA (Livrete/título de registo)',
        'DHTEC'                   => 'DHTEC — Formulário Modelo 9 IMT',
        'FATDV'                   => 'FATDV — Fatura de compra ou declaração de venda',
        'CCEUR'                   => 'CCEUR — Certificado de conformidade (COC)',
        'guia_transporte'         => 'Guia de transporte',
        'CCIDA'                   => 'CCIDA — Cartão de cidadão do proprietário',
        'CINSP'                   => 'CINSP — Certificado de inspeção (Modelo 112)',
        'MD1460'                  => 'MD1460 — DAV (Declaração Aduaneira de Veículo)',
        'autorizacao'             => 'Documento de autorização de utilização de dados',
        'declaracao_homologacao'  => 'Declaração de Homologação',
        // PRODH aplica-se sempre (regime normal ou especial) — caso a DAV não seja submetida pelo próprio proprietário
        'PRODH'                   => 'PRODH — Procuração ou documento de habilitação',
    ];

    // ---------------------------------------------------------------
    // Documentos adicionais — apenas quando regime especial ISV ativo
    // ---------------------------------------------------------------
    const DOCUMENTOS_REGIME_ESPECIAL = [
        'CRESI' => 'CRESI — Certificado de residência oficial',
        'DVIDQ' => 'DVIDQ — Documentos de vida quotidiana',
        'CSTRI' => 'CSTRI — Certidão de situação tributária',
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
            'docs'       => ['CCEUR', 'CMATR', 'DHTEC', 'FATDV', 'guia_transporte', 'CCIDA', 'autorizacao'],
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
            'docs'       => ['CCEUR', 'DHTEC', 'CMATR', 'MD1460', 'CINSP', 'CCIDA', 'declaracao_homologacao'],
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

    public function documents(): HasMany
    {
        return $this->hasMany(LegalizationDocument::class);
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

    public function trackingUrl(): string
    {
        return route('frontend.legalization.status', $this->token);
    }
}
