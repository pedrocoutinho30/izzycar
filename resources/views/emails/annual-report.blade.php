<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório Anual Izzycar</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f5f5f5; margin:0; padding:20px; color:#333; }
        .container { max-width:580px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#8B0000; color:#fff; padding:22px 32px; }
        .header h1 { margin:0 0 4px; font-size:1.1rem; font-weight:700; }
        .header p { margin:0; font-size:.82rem; opacity:.8; }
        .body { padding:24px 32px; }
        .greeting { font-size:.9rem; margin-bottom:14px; }
        .section-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8B0000; margin:18px 0 8px; }
        .kpi-row { display:table; width:100%; border-spacing:8px 0; margin-bottom:10px; }
        .kpi { display:table-cell; background:#f9f9f9; border:1px solid #eee; border-radius:7px; padding:10px 12px; }
        .kpi-label { font-size:.68rem; color:#999; text-transform:uppercase; letter-spacing:.06em; margin-bottom:3px; }
        .kpi-value { font-size:1.25rem; font-weight:800; color:#8B0000; }
        .kpi-sub { font-size:.7rem; color:#aaa; margin-top:2px; }
        .delta-up   { color:#198754; font-weight:700; }
        .delta-down { color:#dc3545; font-weight:700; }
        .attachment-note { font-size:.82rem; color:#666; background:#f0f0f0; border-radius:6px; padding:10px 14px; margin-top:14px; }
        .footer { padding:16px 32px; background:#fafafa; border-top:1px solid #eee; font-size:.7rem; color:#aaa; text-align:center; }
        hr { border:none; border-top:1px solid #f0f0f0; margin:16px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Relatório Anual</h1>
        <p>{{ $data['year'] }} · Análise completa do ano · Izzycar</p>
    </div>
    <div class="body">
        @php
            $c    = $data['current'];
            $prev = $data['prev_year'];
            function ae_email($v) { return number_format($v, 0, ',', '.') . ' €'; }
            function adelta($cur, $cmp, $prevY) {
                if ($cmp == 0) return '';
                $d = round(($cur - $cmp) / abs($cmp) * 100, 1);
                $cls = $d >= 0 ? 'delta-up' : 'delta-down';
                return ' <span class="'.$cls.'">vs '.$prevY.': '.($d >= 0 ? '+' : '').$d.'%</span>';
            }
        @endphp

        <p class="greeting">Olá Pedro,</p>
        <p style="font-size:.85rem;color:#555;">Aqui está o balanço completo do ano <strong>{{ $data['year'] }}</strong>, com comparação face a {{ $data['year'] - 1 }}. O relatório detalhado segue em anexo.</p>

        <div class="section-label">Vendas</div>
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-label">Vendas</div>
                <div class="kpi-value">{{ $c['sales_count'] }}</div>
                <div class="kpi-sub">{!! adelta($c['sales_count'], $prev['sales_count'], $data['year']-1) !!}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Volume</div>
                <div class="kpi-value" style="font-size:1rem">{{ ae_email($c['sales_volume']) }}</div>
                <div class="kpi-sub">{!! adelta($c['sales_volume'], $prev['sales_volume'], $data['year']-1) !!}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Margem bruta</div>
                <div class="kpi-value" style="font-size:1rem">{{ ae_email($c['gross_margin']) }}</div>
                <div class="kpi-sub">{!! adelta($c['gross_margin'], $prev['gross_margin'], $data['year']-1) !!}</div>
            </div>
        </div>

        <div class="section-label">Pipeline & Leads</div>
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-label">Propostas</div>
                <div class="kpi-value">{{ $c['proposals_sent'] }}</div>
                <div class="kpi-sub">{{ $c['proposals_won'] }} aprovadas · {{ $c['conversion_rate'] }}%</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Novas leads</div>
                <div class="kpi-value">{{ $c['new_leads'] }}</div>
                <div class="kpi-sub">{!! adelta($c['new_leads'], $prev['new_leads'], $data['year']-1) !!}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Clientes novos</div>
                <div class="kpi-value">{{ $c['new_clients'] }}</div>
                <div class="kpi-sub">{!! adelta($c['new_clients'], $prev['new_clients'], $data['year']-1) !!}</div>
            </div>
        </div>

        <div class="section-label">Financeiro</div>
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-label">Receitas</div>
                <div class="kpi-value" style="color:#198754;font-size:1rem">{{ ae_email($c['mov_income']) }}</div>
                <div class="kpi-sub">{!! adelta($c['mov_income'], $prev['mov_income'], $data['year']-1) !!}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Despesas</div>
                <div class="kpi-value" style="color:#dc3545;font-size:1rem">{{ ae_email($c['mov_expenses']) }}</div>
                <div class="kpi-sub">{!! adelta($c['mov_expenses'], $prev['mov_expenses'], $data['year']-1) !!}</div>
            </div>
            <div class="kpi">
                @php $nc = $c['mov_net'] >= 0 ? '#198754' : '#dc3545'; @endphp
                <div class="kpi-label">Resultado</div>
                <div class="kpi-value" style="color:{{ $nc }};font-size:1rem">{{ ($c['mov_net'] >= 0 ? '+' : '') . ae_email($c['mov_net']) }}</div>
                <div class="kpi-sub">receitas − despesas</div>
            </div>
        </div>

        <div class="section-label">Legalizações</div>
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-label">Novas em {{ $data['year'] }}</div>
                <div class="kpi-value">{{ $c['legalizations_new'] }}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Total histórico</div>
                <div class="kpi-value">{{ $data['legalizations']['total'] }}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Concluídas</div>
                <div class="kpi-value" style="color:#198754">{{ $data['legalizations']['completed'] }}</div>
                <div class="kpi-sub">{{ $data['legalizations']['in_progress'] }} em progresso</div>
            </div>
        </div>

        <hr>
        <div class="attachment-note">📎 O relatório completo com evolução mensal e todas as comparações está em anexo em PDF.</div>
    </div>
    <div class="footer">Izzycar · <a href="{{ url('/gestao') }}" style="color:#8B0000;text-decoration:none;">Backoffice</a> · Gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i') }}</div>
</div>
</body>
</html>
