<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Relatório Mensal — {{ $period->locale('pt_PT')->translatedFormat('F Y') }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#222; background:#fff; }
.page { padding: 28px 32px; }

/* ── Section titles ── */
.section-title {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .08em; color: #8B0000;
    border-bottom: 2px solid #8B0000;
    padding-bottom: 4px; margin: 20px 0 10px;
}

/* ── KPI card ── */
.kpi-card {
    background: #f9f9f9;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    padding: 10px 11px;
}
.kpi-label { font-size: 7.5px; color: #777; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.kpi-value { font-size: 17px; font-weight: bold; color: #8B0000; line-height: 1; }
.kpi-value-sm { font-size: 13px; font-weight: bold; color: #8B0000; line-height: 1; }

/* ── Comparison rows inside KPI card ── */
.cmp-tbl { width: 100%; border-collapse: collapse; margin-top: 6px; }
.cmp-tbl td { font-size: 7.5px; padding: 1px 0; }
.cmp-tbl td.cmp-lbl { color: #aaa; }
.cmp-tbl td.cmp-val { text-align: right; }

/* ── Delta badges ── */
.delta { font-weight: bold; padding: 1px 4px; border-radius: 2px; font-size: 7px; }
.delta-up   { background: #d4edda; color: #155724; }
.delta-down { background: #f8d7da; color: #721c24; }
.delta-flat { background: #e9ecef; color: #6c757d; }

/* ── Comparison detail table ── */
.detail-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.detail-table th { background: #f3f3f3; font-size: 7.5px; text-transform: uppercase; letter-spacing: .05em; padding: 6px 7px; text-align: left; color: #555; border-bottom: 1px solid #e0e0e0; }
.detail-table th.r, .detail-table td.r { text-align: right; }
.detail-table td { padding: 5px 7px; border-bottom: 1px solid #f0f0f0; font-size: 8.5px; color: #333; vertical-align: middle; }
.detail-table tr:last-child td { border-bottom: none; }
.detail-table td.metric { font-weight: bold; color: #1a1a1a; }
.detail-table td.current { font-weight: bold; font-size: 9.5px; }

/* ── Footer ── */
.footer-tbl { width: 100%; border-collapse: collapse; margin-top: 22px; border-top: 1px solid #e0e0e0; padding-top: 8px; }
.footer-tbl td { font-size: 7px; color: #aaa; padding-top: 8px; }
.footer-tbl td.right { text-align: right; }
</style>
</head>
<body>
<div class="page">

@php
use App\Services\MonthlyReportService as R;
Carbon\Carbon::setLocale('pt_PT');
$c   = $current;
$pm  = $prev_month;
$sly = $same_last_year;
$avg = $ytd_avg;

function delta_badge_pdf($cur, $cmp) {
    $d = R::delta((float)$cur, (float)$cmp);
    if ($d === null) return '<span class="delta delta-flat">—</span>';
    $cls = $d >= 0 ? 'delta-up' : 'delta-down';
    $sign = $d >= 0 ? '+' : '';
    return '<span class="delta '.$cls.'">'.$sign.$d.'%</span>';
}

function fe_pdf($v) { return number_format($v, 0, ',', '.') . ' €'; }
function fp_pdf($v) { return number_format($v, 1, ',', '.') . '%'; }
function fn_pdf($v) { return number_format($v, 0, ',', '.'); }

$activityLabels = [
    'note'      => 'Nota',
    'call'      => 'Chamada',
    'email'     => 'Email',
    'whatsapp'  => 'WhatsApp',
    'facebook'  => 'Facebook',
    'meeting'   => 'Reunião',
];

$statusLabels = [
    'nova'          => 'Nova',
    'em_contacto'   => 'Em Contacto',
    'fria'          => 'Fria',
    'perdida'       => 'Perdida',
];

$funnelColors = [
    'Aprovada'    => '#198754',
    'aprovada'    => '#198754',
    'Pendente'    => '#fd7e14',
    'pendente'    => '#fd7e14',
    'Reprovada'   => '#dc3545',
    'reprovada'   => '#dc3545',
    'Sem resposta'=> '#6c757d',
];

$originLabels = [
    'simulador'  => 'Simulador de Custos',
    'importacao' => 'Formulário Importação',
    'retoma'     => 'Retoma',
    'manual'     => 'Manual (BO)',
    'outro'      => 'Outro',
];
@endphp

{{-- ── HEADER ── --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#8B0000;border-radius:4px;margin-bottom:22px;">
<tr>
    @if(file_exists(public_path('img/logo-arredondado.png')))
    <td width="64" style="padding:18px 0 18px 22px;vertical-align:middle;">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-arredondado.png'))) }}"
             width="48" height="48" style="border-radius:50%;display:block;" alt="">
    </td>
    @endif
    <td style="padding:18px 16px;vertical-align:middle;">
        <div style="font-size:15px;font-weight:bold;color:#fff;letter-spacing:-.3px;">
            Relatório Mensal — {{ $period->locale('pt_PT')->translatedFormat('F Y') }}
        </div>
        <div style="font-size:9px;color:rgba(255,255,255,.75);margin-top:3px;">
            Desempenho comercial e análise comparativa · Izzycar
        </div>
    </td>
    <td width="120" style="padding:18px 22px 18px 0;vertical-align:middle;text-align:right;">
        <div style="font-size:8px;color:rgba(255,255,255,.65);">
            Gerado em {{ now()->format('d/m/Y') }}<br>
            às {{ now()->format('H:i') }}<br>
            izzycar.pt
        </div>
    </td>
</tr>
</table>

{{-- ── RESUMO EXECUTIVO (4 KPIs) ── --}}
<div class="section-title">Resumo Executivo</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:6px;">
<tr>
    @php
    $topKpis = [
        ['label' => 'Vendas realizadas',  'val' => fn_pdf($c['sales_count']),  'pm' => $pm['sales_count'],  'sly' => $sly['sales_count'],  'ytd' => $avg['sales_count'],  'cur' => $c['sales_count']],
        ['label' => 'Volume de vendas',   'val' => fe_pdf($c['sales_volume']), 'pm' => $pm['sales_volume'], 'sly' => $sly['sales_volume'], 'ytd' => $avg['sales_volume'], 'cur' => $c['sales_volume']],
        ['label' => 'Margem bruta',       'val' => fe_pdf($c['gross_margin']), 'pm' => $pm['gross_margin'], 'sly' => $sly['gross_margin'], 'ytd' => $avg['gross_margin'], 'cur' => $c['gross_margin']],
        ['label' => 'Novas leads',        'val' => fn_pdf($c['new_leads']),    'pm' => $pm['new_leads'],    'sly' => $sly['new_leads'],    'ytd' => $avg['new_leads'],    'cur' => $c['new_leads']],
    ];
    @endphp
    @foreach($topKpis as $i => $kpi)
    <td width="25%" style="vertical-align:top;padding-right:{{ $i < 3 ? '8px' : '0' }}">
        <div class="kpi-card" style="margin-bottom:8px;">
            <div class="kpi-label">{{ $kpi['label'] }}</div>
            <div class="{{ strlen($kpi['val']) > 9 ? 'kpi-value-sm' : 'kpi-value' }}">{{ $kpi['val'] }}</div>
            <table class="cmp-tbl">
                <tr>
                    <td class="cmp-lbl">Mês anterior</td>
                    <td class="cmp-val">{!! delta_badge_pdf($kpi['cur'], $kpi['pm']) !!}</td>
                </tr>
                <tr>
                    <td class="cmp-lbl">Ano anterior</td>
                    <td class="cmp-val">{!! delta_badge_pdf($kpi['cur'], $kpi['sly']) !!}</td>
                </tr>
                <tr>
                    <td class="cmp-lbl">Média YTD</td>
                    <td class="cmp-val">{!! delta_badge_pdf($kpi['cur'], $kpi['ytd']) !!}</td>
                </tr>
            </table>
        </div>
    </td>
    @endforeach
</tr>
</table>

{{-- ── TABELA DE COMPARAÇÃO DETALHADA ── --}}
<div class="section-title">Análise Comparativa Detalhada</div>
@php
$detailRows = [
    ['label' => 'Vendas realizadas',     'key' => 'sales_count',     'fmt' => 'n'],
    ['label' => 'Volume de vendas (€)',   'key' => 'sales_volume',    'fmt' => 'eur'],
    ['label' => 'Margem bruta (€)',       'key' => 'gross_margin',    'fmt' => 'eur'],
    ['label' => 'Margem líquida (€)',     'key' => 'net_margin',      'fmt' => 'eur'],
    ['label' => 'Preço médio venda (€)',  'key' => 'avg_sale_price',  'fmt' => 'eur'],
    ['label' => 'Propostas enviadas',     'key' => 'proposals_sent',  'fmt' => 'n'],
    ['label' => 'Propostas aprovadas',    'key' => 'proposals_won',   'fmt' => 'n'],
    ['label' => 'Taxa de conversão',      'key' => 'conversion_rate', 'fmt' => 'pct'],
    ['label' => 'Novas leads',            'key' => 'new_leads',       'fmt' => 'n'],
    ['label' => 'Clientes convertidos',   'key' => 'new_clients',     'fmt' => 'n'],
    ['label' => 'Taxa lead → cliente',    'key' => 'lead_to_client',  'fmt' => 'pct'],
    ['label' => 'Simulações de custo',    'key' => 'simulators',      'fmt' => 'n'],
    ['label' => 'Atividades registadas',  'key' => 'activities',      'fmt' => 'n'],
    ['label' => 'Follow-ups agendados',   'key' => 'followups_set',   'fmt' => 'n'],
];
@endphp
<table class="detail-table">
    <thead>
        <tr>
            <th style="width:28%">Indicador</th>
            <th class="r" style="width:15%">{{ $period->locale('pt_PT')->translatedFormat('F Y') }}</th>
            <th class="r" style="width:15%">{{ $period->copy()->subMonthNoOverflow()->locale('pt_PT')->translatedFormat('F Y') }}</th>
            <th class="r" style="width:5%">Δ</th>
            <th class="r" style="width:15%">{{ $period->copy()->subYear()->locale('pt_PT')->translatedFormat('F Y') }}</th>
            <th class="r" style="width:5%">Δ</th>
            <th class="r" style="width:13%">Média {{ $period->year }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detailRows as $row)
        @php
            $cv   = $c[$row['key']];
            $pmv  = $pm[$row['key']];
            $slyv = $sly[$row['key']];
            $avgv = $avg[$row['key']];
            $f    = $row['fmt'];
            $fmt  = fn($v) => $f === 'eur' ? fe_pdf($v) : ($f === 'pct' ? fp_pdf($v) : fn_pdf($v));
        @endphp
        <tr>
            <td class="metric">{{ $row['label'] }}</td>
            <td class="r current">{{ $fmt($cv) }}</td>
            <td class="r" style="color:#666">{{ $fmt($pmv) }}</td>
            <td class="r">{!! delta_badge_pdf($cv, $pmv) !!}</td>
            <td class="r" style="color:#666">{{ $fmt($slyv) }}</td>
            <td class="r">{!! delta_badge_pdf($cv, $slyv) !!}</td>
            <td class="r" style="color:#888">{{ $fmt($avgv) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── FUNIL + ORIGENS ── --}}
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    {{-- Funil de Propostas --}}
    <td width="48%" style="vertical-align:top;padding-right:16px;">
        <div class="section-title" style="margin-top:6px;">Funil de Propostas</div>
        @php $funnelTotal = array_sum($proposal_funnel ?? []); @endphp
        @if(!empty($proposal_funnel) && $funnelTotal > 0)
            @foreach($proposal_funnel as $status => $count)
            @php
                $pct   = $funnelTotal > 0 ? round($count / $funnelTotal * 100) : 0;
                $color = $funnelColors[$status] ?? '#8B0000';
                $pctBar = max(1, $pct);
            @endphp
            <div style="margin-bottom:8px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;">
                <tr>
                    <td style="font-size:8px;color:#444;">{{ ucfirst($status) }}</td>
                    <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">
                        {{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span>
                    </td>
                </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;">
                <tr>
                    <td width="{{ $pctBar }}%" style="background:{{ $color }};height:6px;border-radius:2px 0 0 2px;"></td>
                    @if($pctBar < 100)<td style="height:6px;"></td>@endif
                </tr>
                </table>
            </div>
            @endforeach
        @else
            <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem propostas neste mês.</div>
        @endif
    </td>

    {{-- Origem das Leads --}}
    <td width="52%" style="vertical-align:top;">
        <div class="section-title" style="margin-top:6px;">Origem das Leads</div>
        @if($lead_origins->isNotEmpty())
            @php $originsTotal = $lead_origins->sum('total'); @endphp
            @foreach($lead_origins as $origin)
            @php
                $pct = $originsTotal > 0 ? round($origin->total / $originsTotal * 100) : 0;
                $pctBar = max(1, $pct);
            @endphp
            <div style="margin-bottom:8px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;">
                <tr>
                    <td style="font-size:8px;color:#444;">{{ $originLabels[$origin->lead_source] ?? ucfirst($origin->lead_source) }}</td>
                    <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">
                        {{ $origin->total }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span>
                    </td>
                </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;">
                <tr>
                    <td width="{{ $pctBar }}%" style="background:#8B0000;height:6px;border-radius:2px 0 0 2px;"></td>
                    @if($pctBar < 100)<td style="height:6px;"></td>@endif
                </tr>
                </table>
            </div>
            @endforeach
        @else
            <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem novas leads neste mês.</div>
        @endif
    </td>
</tr>
</table>

{{-- ── BREAKDOWN DE ATIVIDADES + ESTADO DAS LEADS ── --}}
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    {{-- Tipos de atividade --}}
    <td width="48%" style="vertical-align:top;padding-right:16px;">
        <div class="section-title">Atividades por Tipo</div>
        @if(!empty($activity_types))
            @php $actTotal = array_sum($activity_types); @endphp
            @foreach($activity_types as $type => $count)
            @php
                $pct = $actTotal > 0 ? round($count / $actTotal * 100) : 0;
                $pctBar = max(1, $pct);
            @endphp
            <div style="margin-bottom:7px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;">
                <tr>
                    <td style="font-size:8px;color:#444;">{{ $activityLabels[$type] ?? ucfirst($type) }}</td>
                    <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">
                        {{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span>
                    </td>
                </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;">
                <tr>
                    <td width="{{ $pctBar }}%" style="background:#0d6efd;height:6px;border-radius:2px 0 0 2px;"></td>
                    @if($pctBar < 100)<td style="height:6px;"></td>@endif
                </tr>
                </table>
            </div>
            @endforeach
        @else
            <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem atividades registadas.</div>
        @endif
    </td>

    {{-- Estado das leads --}}
    <td width="52%" style="vertical-align:top;">
        <div class="section-title">Estado das Leads</div>
        @if(!empty($lead_statuses))
            @php $statusTotal = array_sum($lead_statuses); @endphp
            @foreach($lead_statuses as $status => $count)
            @php
                $pct = $statusTotal > 0 ? round($count / $statusTotal * 100) : 0;
                $pctBar = max(1, $pct);
                $statusColor = match($status) {
                    'nova'        => '#0d6efd',
                    'em_contacto' => '#198754',
                    'fria'        => '#6c757d',
                    'perdida'     => '#dc3545',
                    default       => '#8B0000',
                };
            @endphp
            <div style="margin-bottom:7px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;">
                <tr>
                    <td style="font-size:8px;color:#444;">{{ $statusLabels[$status] ?? ucfirst($status) }}</td>
                    <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">
                        {{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span>
                    </td>
                </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;">
                <tr>
                    <td width="{{ $pctBar }}%" style="background:{{ $statusColor }};height:6px;border-radius:2px 0 0 2px;"></td>
                    @if($pctBar < 100)<td style="height:6px;"></td>@endif
                </tr>
                </table>
            </div>
            @endforeach
        @else
            <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem leads registadas.</div>
        @endif
    </td>
</tr>
</table>

{{-- ── FOOTER ── --}}
<table class="footer-tbl">
<tr>
    <td>Izzycar · Relatório gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i') }}</td>
    <td class="right">{{ $period->locale('pt_PT')->translatedFormat('F Y') }} · Confidencial</td>
</tr>
</table>

</div>
</body>
</html>
