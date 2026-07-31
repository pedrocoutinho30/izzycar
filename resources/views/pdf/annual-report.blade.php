<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Relatório Anual — {{ $year }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#222; background:#fff; }
.page { padding: 28px 32px; }
.page-break { page-break-after: always; }
.section-title { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:#8B0000; border-bottom:2px solid #8B0000; padding-bottom:4px; margin:20px 0 10px; }
.kpi-card { background:#f9f9f9; border:1px solid #e8e8e8; border-radius:4px; padding:10px 11px; }
.kpi-label { font-size:7.5px; color:#777; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
.kpi-value { font-size:17px; font-weight:bold; color:#8B0000; line-height:1; }
.kpi-value-sm { font-size:13px; font-weight:bold; color:#8B0000; line-height:1; }
.cmp-tbl { width:100%; border-collapse:collapse; margin-top:6px; }
.cmp-tbl td { font-size:7.5px; padding:1px 0; }
.cmp-tbl td.cmp-lbl { color:#aaa; }
.cmp-tbl td.cmp-val { text-align:right; }
.delta { font-weight:bold; padding:1px 4px; border-radius:2px; font-size:7px; }
.delta-up   { background:#d4edda; color:#155724; }
.delta-down { background:#f8d7da; color:#721c24; }
.delta-flat { background:#e9ecef; color:#6c757d; }
.detail-table { width:100%; border-collapse:collapse; margin-bottom:14px; }
.detail-table th { background:#f3f3f3; font-size:7.5px; text-transform:uppercase; letter-spacing:.05em; padding:6px 7px; text-align:left; color:#555; border-bottom:1px solid #e0e0e0; }
.detail-table th.r, .detail-table td.r { text-align:right; }
.detail-table td { padding:5px 7px; border-bottom:1px solid #f0f0f0; font-size:8.5px; color:#333; vertical-align:middle; }
.detail-table tr:last-child td { border-bottom:none; }
.detail-table td.metric { font-weight:bold; color:#1a1a1a; }
.detail-table td.current { font-weight:bold; font-size:9.5px; }
.monthly-table { width:100%; border-collapse:collapse; margin-bottom:14px; }
.monthly-table th { background:#f3f3f3; font-size:7px; text-transform:uppercase; padding:5px 5px; text-align:right; color:#555; border-bottom:1px solid #e0e0e0; }
.monthly-table th:first-child { text-align:left; }
.monthly-table td { padding:4px 5px; border-bottom:1px solid #f5f5f5; font-size:8px; text-align:right; color:#444; }
.monthly-table td:first-child { font-weight:bold; color:#222; text-align:left; }
.monthly-table tr:last-child td { border-bottom:none; }
.footer-tbl { width:100%; border-collapse:collapse; margin-top:22px; border-top:1px solid #e0e0e0; }
.footer-tbl td { font-size:7px; color:#aaa; padding-top:8px; }
.footer-tbl td.right { text-align:right; }
</style>
</head>
<body>
<div class="page">

@php
use App\Services\ReportDataService as R;
Carbon\Carbon::setLocale('pt_PT');
$c    = $current;
$prev = $prev_year;

function ad_badge($cur, $cmp) {
    $d = R::delta((float)$cur, (float)$cmp);
    if ($d === null) return '<span class="delta delta-flat">—</span>';
    $cls = $d >= 0 ? 'delta-up' : 'delta-down';
    return '<span class="delta '.$cls.'">'.($d >= 0 ? '+' : '').$d.'%</span>';
}
function ae($v) { return number_format($v, 0, ',', '.') . ' €'; }
function ap($v) { return number_format($v, 1, ',', '.') . '%'; }
function an($v) { return number_format($v, 0, ',', '.'); }

$monthNames = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'];
$activityLabels = ['note'=>'Nota','call'=>'Chamada','email'=>'Email','whatsapp'=>'WhatsApp','facebook'=>'Facebook','meeting'=>'Reunião'];
$statusLabels   = ['nova'=>'Nova','em_contacto'=>'Em Contacto','fria'=>'Fria','perdida'=>'Perdida'];
$originLabels   = ['simulador'=>'Simulador','importacao'=>'Formulário Importação','retoma'=>'Retoma','manual'=>'Manual (BO)','outro'=>'Outro'];
$funnelColors   = ['Aprovada'=>'#198754','aprovada'=>'#198754','Pendente'=>'#fd7e14','pendente'=>'#fd7e14','Reprovada'=>'#dc3545','reprovada'=>'#dc3545'];
@endphp

{{-- HEADER --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#8B0000;border-radius:4px;margin-bottom:22px;">
<tr>
    @if(file_exists(public_path('img/logo-arredondado.png')))
    <td width="64" style="padding:18px 0 18px 22px;vertical-align:middle;">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-arredondado.png'))) }}"
             width="48" height="48" style="border-radius:50%;display:block;" alt="">
    </td>
    @endif
    <td style="padding:18px 16px;vertical-align:middle;">
        <div style="font-size:15px;font-weight:bold;color:#fff;">Relatório Anual — {{ $year }}</div>
        <div style="font-size:9px;color:rgba(255,255,255,.75);margin-top:3px;">Análise completa do ano · Comparação com {{ $year - 1 }} · Izzycar</div>
    </td>
    <td width="120" style="padding:18px 22px 18px 0;vertical-align:middle;text-align:right;">
        <div style="font-size:8px;color:rgba(255,255,255,.65);">Gerado em {{ now()->format('d/m/Y') }}<br>às {{ now()->format('H:i') }}<br>izzycar.pt</div>
    </td>
</tr>
</table>

{{-- 4 KPIs principais --}}
<div class="section-title">Resumo do Ano</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:6px;">
<tr>
@php
$topKpis = [
    ['label'=>'Vendas realizadas','val'=>an($c['sales_count']),'prev'=>$prev['sales_count'],'cur'=>$c['sales_count']],
    ['label'=>'Volume de vendas','val'=>ae($c['sales_volume']),'prev'=>$prev['sales_volume'],'cur'=>$c['sales_volume']],
    ['label'=>'Margem bruta total','val'=>ae($c['gross_margin']),'prev'=>$prev['gross_margin'],'cur'=>$c['gross_margin']],
    ['label'=>'Novas leads','val'=>an($c['new_leads']),'prev'=>$prev['new_leads'],'cur'=>$c['new_leads']],
];
@endphp
@foreach($topKpis as $i => $kpi)
<td width="25%" style="vertical-align:top;padding-right:{{ $i < 3 ? '8px' : '0' }}">
    <div class="kpi-card" style="margin-bottom:8px;">
        <div class="kpi-label">{{ $kpi['label'] }}</div>
        <div class="{{ strlen($kpi['val']) > 9 ? 'kpi-value-sm' : 'kpi-value' }}">{{ $kpi['val'] }}</div>
        <table class="cmp-tbl">
            <tr><td class="cmp-lbl">vs {{ $year - 1 }}</td><td class="cmp-val">{!! ad_badge($kpi['cur'], $kpi['prev']) !!}</td></tr>
        </table>
    </div>
</td>
@endforeach
</tr>
</table>

{{-- Breakdown mensal --}}
<div class="section-title">Evolução Mensal {{ $year }}</div>
<table class="monthly-table">
<thead>
<tr>
    <th>Mês</th>
    <th>Vendas</th>
    <th>Volume (€)</th>
    <th>Margem (€)</th>
    <th>Leads</th>
    <th>Propostas</th>
    <th>Conv.%</th>
    <th>Receitas (€)</th>
    <th>Despesas (€)</th>
    <th>Result. (€)</th>
</tr>
</thead>
<tbody>
@php $totals = array_fill_keys(['sales_count','sales_volume','gross_margin','new_leads','proposals_sent','proposals_won','mov_income','mov_expenses','mov_net'], 0); @endphp
@foreach($monthly_breakdown as $m => $md)
@php
    foreach(array_keys($totals) as $k) { $totals[$k] += $md[$k]; }
    $resultColor = $md['mov_net'] >= 0 ? '#198754' : '#dc3545';
@endphp
<tr>
    <td>{{ $monthNames[$m] }}</td>
    <td>{{ an($md['sales_count']) }}</td>
    <td>{{ ae($md['sales_volume']) }}</td>
    <td>{{ ae($md['gross_margin']) }}</td>
    <td>{{ an($md['new_leads']) }}</td>
    <td>{{ an($md['proposals_sent']) }}</td>
    <td>{{ ap($md['conversion_rate']) }}</td>
    <td style="color:#198754;">{{ ae($md['mov_income']) }}</td>
    <td style="color:#dc3545;">{{ ae($md['mov_expenses']) }}</td>
    <td style="color:{{ $resultColor }};font-weight:bold;">{{ ($md['mov_net'] >= 0 ? '+' : '') . ae($md['mov_net']) }}</td>
</tr>
@endforeach
<tr style="background:#f3f3f3;font-weight:bold;">
    <td>TOTAL</td>
    <td>{{ an($totals['sales_count']) }}</td>
    <td>{{ ae($totals['sales_volume']) }}</td>
    <td>{{ ae($totals['gross_margin']) }}</td>
    <td>{{ an($totals['new_leads']) }}</td>
    <td>{{ an($totals['proposals_sent']) }}</td>
    <td>{{ $totals['proposals_sent'] > 0 ? ap(round($totals['proposals_won']/$totals['proposals_sent']*100,1)) : '—' }}</td>
    <td style="color:#198754;">{{ ae($totals['mov_income']) }}</td>
    <td style="color:#dc3545;">{{ ae($totals['mov_expenses']) }}</td>
    @php $netColor = $totals['mov_net'] >= 0 ? '#198754' : '#dc3545'; @endphp
    <td style="color:{{ $netColor }};">{{ ($totals['mov_net'] >= 0 ? '+' : '') . ae($totals['mov_net']) }}</td>
</tr>
</tbody>
</table>

{{-- Quebra de página antes das secções detalhadas --}}
<div class="page-break"></div>

{{-- Tabela comparativa anual --}}
<div class="section-title">Comparação {{ $year }} vs {{ $year - 1 }}</div>
@php
$detailRows = [
    ['label'=>'Vendas realizadas','key'=>'sales_count','fmt'=>'n'],
    ['label'=>'Volume de vendas (€)','key'=>'sales_volume','fmt'=>'eur'],
    ['label'=>'Margem bruta (€)','key'=>'gross_margin','fmt'=>'eur'],
    ['label'=>'Margem líquida (€)','key'=>'net_margin','fmt'=>'eur'],
    ['label'=>'Preço médio venda (€)','key'=>'avg_sale_price','fmt'=>'eur'],
    ['label'=>'Propostas enviadas','key'=>'proposals_sent','fmt'=>'n'],
    ['label'=>'Propostas aprovadas','key'=>'proposals_won','fmt'=>'n'],
    ['label'=>'Taxa de conversão','key'=>'conversion_rate','fmt'=>'pct'],
    ['label'=>'Novas leads','key'=>'new_leads','fmt'=>'n'],
    ['label'=>'Clientes convertidos','key'=>'new_clients','fmt'=>'n'],
    ['label'=>'Taxa lead → cliente','key'=>'lead_to_client','fmt'=>'pct'],
    ['label'=>'Simulações de custo','key'=>'simulators','fmt'=>'n'],
    ['label'=>'Atividades registadas','key'=>'activities','fmt'=>'n'],
    ['label'=>'Receitas (€)','key'=>'mov_income','fmt'=>'eur'],
    ['label'=>'Despesas (€)','key'=>'mov_expenses','fmt'=>'eur'],
    ['label'=>'Resultado (€)','key'=>'mov_net','fmt'=>'eur'],
    ['label'=>'Legalizações novas','key'=>'legalizations_new','fmt'=>'n'],
];
@endphp
<table class="detail-table">
<thead><tr>
    <th style="width:40%">Indicador</th>
    <th class="r" style="width:22%">{{ $year }}</th>
    <th class="r" style="width:22%">{{ $year - 1 }}</th>
    <th class="r" style="width:8%">Δ</th>
    <th class="r" style="width:8%">Δ%</th>
</tr></thead>
<tbody>
@foreach($detailRows as $row)
@php
$cv = $c[$row['key']]; $pv = $prev[$row['key']];
$f = $row['fmt'];
$fmt = fn($v) => $f === 'eur' ? ae($v) : ($f === 'pct' ? ap($v) : an($v));
$diff = $cv - $pv;
$diffFmt = $f === 'eur' ? (($diff >= 0 ? '+' : '') . ae(abs($diff))) : (($diff >= 0 ? '+' : '') . $fmt(abs($diff)));
@endphp
<tr>
    <td class="metric">{{ $row['label'] }}</td>
    <td class="r current">{{ $fmt($cv) }}</td>
    <td class="r" style="color:#666">{{ $fmt($pv) }}</td>
    <td class="r" style="color:{{ $diff >= 0 ? '#198754' : '#dc3545' }};font-size:8px;">{{ $diff >= 0 ? '+' : '' }}{{ $f === 'eur' ? ae($diff) : $fmt($diff) }}</td>
    <td class="r">{!! ad_badge($cv, $pv) !!}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- Funil + Origens --}}
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="48%" style="vertical-align:top;padding-right:16px;">
    <div class="section-title" style="margin-top:6px;">Funil de Propostas</div>
    @php $funnelTotal = array_sum($proposal_funnel ?? []); @endphp
    @if(!empty($proposal_funnel) && $funnelTotal > 0)
        @foreach($proposal_funnel as $status => $count)
        @php $pct=round($count/$funnelTotal*100); $pb=max(1,$pct); $color=$funnelColors[$status]??'#8B0000'; @endphp
        <div style="margin-bottom:7px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;"><tr>
                <td style="font-size:8px;color:#444;">{{ ucfirst($status) }}</td>
                <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">{{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span></td>
            </tr></table>
            <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;"><tr>
                <td width="{{ $pb }}%" style="background:{{ $color }};height:6px;border-radius:2px 0 0 2px;"></td>
                @if($pb < 100)<td style="height:6px;"></td>@endif
            </tr></table>
        </div>
        @endforeach
    @else
        <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem propostas neste ano.</div>
    @endif
</td>
<td width="52%" style="vertical-align:top;">
    <div class="section-title" style="margin-top:6px;">Origem das Leads</div>
    @if($lead_origins->isNotEmpty())
        @php $originsTotal = $lead_origins->sum('total'); @endphp
        @foreach($lead_origins as $origin)
        @php $pct=round($origin->total/$originsTotal*100); $pb=max(1,$pct); @endphp
        <div style="margin-bottom:7px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;"><tr>
                <td style="font-size:8px;color:#444;">{{ $originLabels[$origin->lead_source] ?? ucfirst($origin->lead_source) }}</td>
                <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">{{ $origin->total }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span></td>
            </tr></table>
            <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;"><tr>
                <td width="{{ $pb }}%" style="background:#8B0000;height:6px;border-radius:2px 0 0 2px;"></td>
                @if($pb < 100)<td style="height:6px;"></td>@endif
            </tr></table>
        </div>
        @endforeach
    @else
        <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem leads neste ano.</div>
    @endif
</td>
</tr>
</table>

{{-- Atividades + Estado Leads --}}
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="48%" style="vertical-align:top;padding-right:16px;">
    <div class="section-title">Atividades por Tipo</div>
    @if(!empty($activity_types))
        @php $actTotal = array_sum($activity_types); @endphp
        @foreach($activity_types as $type => $count)
        @php $pct=round($count/$actTotal*100); $pb=max(1,$pct); @endphp
        <div style="margin-bottom:7px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;"><tr>
                <td style="font-size:8px;color:#444;">{{ $activityLabels[$type] ?? ucfirst($type) }}</td>
                <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">{{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span></td>
            </tr></table>
            <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;"><tr>
                <td width="{{ $pb }}%" style="background:#0d6efd;height:6px;border-radius:2px 0 0 2px;"></td>
                @if($pb < 100)<td style="height:6px;"></td>@endif
            </tr></table>
        </div>
        @endforeach
    @else
        <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem atividades.</div>
    @endif
</td>
<td width="52%" style="vertical-align:top;">
    <div class="section-title">Estado das Leads</div>
    @if(!empty($lead_statuses))
        @php $statusTotal = array_sum($lead_statuses); @endphp
        @foreach($lead_statuses as $status => $count)
        @php
            $pct=round($count/$statusTotal*100); $pb=max(1,$pct);
            $sc = match($status) {'nova'=>'#0d6efd','em_contacto'=>'#198754','fria'=>'#6c757d','perdida'=>'#dc3545',default=>'#8B0000'};
        @endphp
        <div style="margin-bottom:7px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px;"><tr>
                <td style="font-size:8px;color:#444;">{{ $statusLabels[$status] ?? ucfirst($status) }}</td>
                <td style="text-align:right;font-size:8px;font-weight:bold;color:#222;">{{ $count }} <span style="color:#aaa;font-weight:normal;">({{ $pct }}%)</span></td>
            </tr></table>
            <table width="100%" cellpadding="0" cellspacing="0" style="height:6px;background:#eee;border-radius:2px;"><tr>
                <td width="{{ $pb }}%" style="background:{{ $sc }};height:6px;border-radius:2px 0 0 2px;"></td>
                @if($pb < 100)<td style="height:6px;"></td>@endif
            </tr></table>
        </div>
        @endforeach
    @else
        <div style="font-size:8px;color:#aaa;padding:8px 0;">Sem leads.</div>
    @endif
</td>
</tr>
</table>

{{-- Movimentos Financeiros --}}
<div class="section-title">Movimentos Financeiros</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
    <td width="33%" style="padding-right:8px;vertical-align:top;">
        <div class="kpi-card">
            <div class="kpi-label">Receitas do ano</div>
            <div class="kpi-value-sm" style="color:#198754;">{{ ae($c['mov_income']) }}</div>
            <table class="cmp-tbl"><tr><td class="cmp-lbl">vs {{ $year - 1 }}</td><td class="cmp-val">{!! ad_badge($c['mov_income'],$prev['mov_income']) !!}</td></tr></table>
        </div>
    </td>
    <td width="33%" style="padding-right:8px;vertical-align:top;">
        <div class="kpi-card">
            <div class="kpi-label">Despesas do ano</div>
            <div class="kpi-value-sm" style="color:#dc3545;">{{ ae($c['mov_expenses']) }}</div>
            <table class="cmp-tbl"><tr><td class="cmp-lbl">vs {{ $year - 1 }}</td><td class="cmp-val">{!! ad_badge($c['mov_expenses'],$prev['mov_expenses']) !!}</td></tr></table>
        </div>
    </td>
    <td width="34%" style="vertical-align:top;">
        <div class="kpi-card">
            <div class="kpi-label">Resultado do ano</div>
            @php $nc = $c['mov_net'] >= 0 ? '#198754' : '#dc3545'; @endphp
            <div class="kpi-value-sm" style="color:{{ $nc }};">{{ ($c['mov_net'] >= 0 ? '+' : '') . ae($c['mov_net']) }}</div>
            <table class="cmp-tbl"><tr><td class="cmp-lbl">vs {{ $year - 1 }}</td><td class="cmp-val">{!! ad_badge($c['mov_net'],$prev['mov_net']) !!}</td></tr></table>
        </div>
    </td>
</tr>
</table>
@if(!empty($movement_categories))
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
    <th style="font-size:7px;text-transform:uppercase;color:#555;background:#f3f3f3;padding:5px 6px;text-align:left;border-bottom:1px solid #e0e0e0;">Categoria</th>
    <th style="font-size:7px;text-transform:uppercase;color:#555;background:#f3f3f3;padding:5px 6px;text-align:center;border-bottom:1px solid #e0e0e0;">Mov.</th>
    <th style="font-size:7px;text-transform:uppercase;color:#555;background:#f3f3f3;padding:5px 6px;text-align:right;border-bottom:1px solid #e0e0e0;">Total (€)</th>
</tr>
@foreach(['income'=>['label'=>'Receitas','color'=>'#198754'],'expense'=>['label'=>'Despesas','color'=>'#dc3545']] as $type => $meta)
    @if(!empty($movement_categories[$type]))
    <tr><td colspan="3" style="font-size:7px;font-weight:bold;color:{{ $meta['color'] }};padding:4px 6px 2px;background:#fafafa;text-transform:uppercase;">{{ $meta['label'] }}</td></tr>
    @foreach($movement_categories[$type] as $cat)
    <tr>
        <td style="font-size:8px;color:#333;padding:4px 6px;border-bottom:1px solid #f5f5f5;">{{ $cat['category'] }}</td>
        <td style="font-size:8px;color:#888;padding:4px 6px;text-align:center;border-bottom:1px solid #f5f5f5;">{{ $cat['count'] }}</td>
        <td style="font-size:8px;font-weight:bold;color:{{ $meta['color'] }};padding:4px 6px;text-align:right;border-bottom:1px solid #f5f5f5;">{{ ae($cat['total']) }}</td>
    </tr>
    @endforeach
    @endif
@endforeach
</table>
@endif

{{-- Legalizações --}}
<div class="section-title">Legalizações</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
    <td width="25%" style="vertical-align:top;padding-right:8px;">
        <div class="kpi-card">
            <div class="kpi-label">Novas neste ano</div>
            <div class="kpi-value">{{ an($c['legalizations_new']) }}</div>
            <table class="cmp-tbl"><tr><td class="cmp-lbl">vs {{ $year - 1 }}</td><td class="cmp-val">{!! ad_badge($c['legalizations_new'],$prev['legalizations_new']) !!}</td></tr></table>
        </div>
    </td>
    <td width="25%" style="vertical-align:top;padding-right:8px;">
        <div class="kpi-card">
            <div class="kpi-label">Total realizadas</div>
            <div class="kpi-value">{{ an($legalizations['total']) }}</div>
            <div style="font-size:7.5px;color:#aaa;margin-top:4px;">desde o início</div>
        </div>
    </td>
    <td width="25%" style="vertical-align:top;padding-right:8px;">
        <div class="kpi-card">
            <div class="kpi-label">Concluídas</div>
            <div class="kpi-value" style="color:#198754;">{{ an($legalizations['completed']) }}</div>
            @if($legalizations['total'] > 0)
            <div style="font-size:7.5px;color:#aaa;margin-top:4px;">{{ round($legalizations['completed']/$legalizations['total']*100) }}% do total</div>
            @endif
        </div>
    </td>
    <td width="25%" style="vertical-align:top;">
        <div class="kpi-card">
            <div class="kpi-label">Em progresso</div>
            <div class="kpi-value" style="color:#fd7e14;">{{ an($legalizations['in_progress']) }}</div>
            @if($legalizations['total'] > 0)
            <div style="font-size:7.5px;color:#aaa;margin-top:4px;">{{ round($legalizations['in_progress']/$legalizations['total']*100) }}% do total</div>
            @endif
        </div>
    </td>
</tr>
</table>

<table class="footer-tbl">
<tr>
    <td>Izzycar · Relatório gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i') }}</td>
    <td class="right">Relatório Anual {{ $year }} · Confidencial</td>
</tr>
</table>
</div>
</body>
</html>
