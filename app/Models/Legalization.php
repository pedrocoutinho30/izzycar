<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Legalization extends Model
{
    protected $fillable = [
        'client_id',
        'marca',
        'modelo',
        'combustivel',
        'matricula',
        'num_homologacao',
        'notas',
        'steps_completed',
    ];

    protected $casts = [
        'steps_completed' => 'array',
    ];

    // ---------------------------------------------------------------
    // Definição centralizada de documentos
    // ---------------------------------------------------------------
    const DOCUMENTOS = [
        'dua'             => 'Documento estrangeiro equiv. DUA (Livrete/título de registo)',
        'modelo9'         => 'Formulário Modelo 9 IMT',
        'fatura_compra'   => 'Fatura de compra ou declaração de venda',
        'coc'             => 'Certificado de conformidade (COC)',
        'guia_transporte' => 'Guia de transporte',
        'cartao_cidadao'  => 'Cartão de cidadão do proprietário',
        'modelo112'       => 'Certificado de inspeção (Modelo 112)',
        'dav'             => 'DAV (Declaração Aduaneira de Veículo)',
        'autorizacao'     => 'Documento de autorização de utilização de dados',
    ];

    // ---------------------------------------------------------------
    // Definição dos passos de legalização
    // ---------------------------------------------------------------
    const PASSOS = [
        1 => [
            'titulo'   => 'Obter número de homologação nacional no IMT',
            'link'     => 'https://chnac.imt-ip.pt/',
            'link_label' => 'Abrir portal CHNAC',
            'docs'     => ['coc', 'dua'],
            'info'     => null,
        ],
        2 => [
            'titulo'   => 'Inspeção B e obtenção do Modelo 112',
            'link'     => null,
            'link_label' => null,
            'docs'     => ['modelo9'],
            'info'     => null,
        ],
        3 => [
            'titulo'   => 'Preenchimento da DAV no portal das Finanças',
            'link'     => null,
            'link_label' => null,
            'docs'     => ['coc', 'dua', 'modelo9', 'fatura_compra', 'guia_transporte', 'cartao_cidadao', 'autorizacao'],
            'info'     => null,
        ],
        4 => [
            'titulo'   => 'Fazer o pagamento do ISV',
            'link'     => null,
            'link_label' => null,
            'docs'     => [],
            'info'     => 'Não são necessários documentos específicos.',
        ],
        5 => [
            'titulo'   => 'Fazer chapas de matrícula e contratar seguro',
            'link'     => null,
            'link_label' => null,
            'docs'     => [],
            'info'     => 'Não são necessários documentos específicos.',
        ],
        6 => [
            'titulo'   => 'Entregar Modelo 9 no IMT',
            'link'     => null,
            'link_label' => null,
            'docs'     => ['coc', 'modelo9', 'dua', 'dav', 'modelo112', 'cartao_cidadao'],
            'info'     => null,
        ],
        7 => [
            'titulo'   => 'Registo inicial na Conservatória do Registo Automóvel',
            'link'     => null,
            'link_label' => null,
            'docs'     => [],
            'info'     => 'Aguardar o registo na conservatória (após emissão do DUA).',
        ],
        8 => [
            'titulo'   => 'Pagar IUC',
            'link'     => null,
            'link_label' => null,
            'docs'     => [],
            'info'     => 'Não são necessários documentos específicos.',
        ],
    ];

    // ---------------------------------------------------------------
    // Relações
    // ---------------------------------------------------------------
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LegalizationDocument::class);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    public function hasDocument(string $tipo): bool
    {
        return $this->documents->where('tipo', $tipo)->isNotEmpty();
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
}
