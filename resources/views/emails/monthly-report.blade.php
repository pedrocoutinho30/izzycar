<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Mensal Izzycar</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #8B0000; color: #fff; padding: 28px 32px; display: flex; align-items: center; gap: 16px; }
        .header img { width: 52px; height: 52px; border-radius: 50%; }
        .header h1 { margin: 0 0 4px; font-size: 1.15rem; font-weight: 700; }
        .header p { margin: 0; font-size: .85rem; opacity: .8; }
        .body { padding: 28px 32px; }
        .greeting { font-size: .95rem; margin-bottom: 16px; }
        .section-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #8B0000; margin: 20px 0 8px; }
        .kpi-row { display: flex; gap: 10px; margin-bottom: 12px; }
        .kpi { flex: 1; background: #f9f9f9; border: 1px solid #eee; border-radius: 7px; padding: 12px 14px; }
        .kpi-label { font-size: .7rem; color: #999; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
        .kpi-value { font-size: 1.3rem; font-weight: 800; color: #8B0000; }
        .kpi-sub { font-size: .72rem; color: #aaa; margin-top: 2px; }
        .delta-up   { color: #198754; font-weight: 700; }
        .delta-down { color: #dc3545; font-weight: 700; }
        .note { background: #fff8e1; border-left: 3px solid #f9a825; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: .83rem; color: #555; margin: 16px 0; }
        .attachment-note { font-size: .82rem; color: #666; background: #f0f0f0; border-radius: 6px; padding: 10px 14px; margin-top: 16px; }
        .footer { padding: 18px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: .72rem; color: #aaa; text-align: center; }
        .footer a { color: #8B0000; text-decoration: none; }
        hr { border: none; border-top: 1px solid #f0f0f0; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/logo-arredondado.png') }}" alt="Izzycar">
            <div>
                <h1>Relatório Mensal</h1>
                <p>{{ $period->locale('pt_PT')->translatedFormat('F Y') }} · Izzycar</p>
            </div>
        </div>
        <div class="body">
            <p class="greeting">Olá Pedro,</p>
            <p style="font-size:.88rem;color:#555;margin-bottom:0">Aqui está o teu resumo de desempenho para <strong>{{ $period->locale('pt_PT')->translatedFormat('F Y') }}</strong>. O relatório completo está em anexo em PDF.</p>

            @php
                $c = $data['current'];
                $pm = $data['prev_month'];
                function delta_html($cur, $cmp) {
                    if ($cmp == 0) return '';
                    $d = round(($cur - $cmp) / abs($cmp) * 100, 1);
                    $cls = $d >= 0 ? 'delta-up' : 'delta-down';
                    $sign = $d >= 0 ? '+' : '';
                    return ' <span class="'.$cls.'">'.$sign.$d.'%</span>';
                }
                function fe($v) { return number_format($v, 0, ',', '.') . ' €'; }
            @endphp

            <div class="section-label">Vendas</div>
            <div class="kpi-row">
                <div class="kpi">
                    <div class="kpi-label">Vendas</div>
                    <div class="kpi-value">{{ $c['sales_count'] }}</div>
                    <div class="kpi-sub">vs mês ant.{!! delta_html($c['sales_count'], $pm['sales_count']) !!}</div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Volume</div>
                    <div class="kpi-value" style="font-size:1.05rem">{{ fe($c['sales_volume']) }}</div>
                    <div class="kpi-sub">vs mês ant.{!! delta_html($c['sales_volume'], $pm['sales_volume']) !!}</div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Margem bruta</div>
                    <div class="kpi-value" style="font-size:1.05rem">{{ fe($c['gross_margin']) }}</div>
                    <div class="kpi-sub">vs mês ant.{!! delta_html($c['gross_margin'], $pm['gross_margin']) !!}</div>
                </div>
            </div>

            <div class="section-label">Pipeline & Leads</div>
            <div class="kpi-row">
                <div class="kpi">
                    <div class="kpi-label">Propostas</div>
                    <div class="kpi-value">{{ $c['proposals_sent'] }}</div>
                    <div class="kpi-sub">{{ $c['proposals_won'] }} aprovadas · {{ $c['conversion_rate'] }}% conv.</div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Novas leads</div>
                    <div class="kpi-value">{{ $c['new_leads'] }}</div>
                    <div class="kpi-sub">vs mês ant.{!! delta_html($c['new_leads'], $pm['new_leads']) !!}</div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Simulações</div>
                    <div class="kpi-value">{{ $c['simulators'] }}</div>
                    <div class="kpi-sub">vs mês ant.{!! delta_html($c['simulators'], $pm['simulators']) !!}</div>
                </div>
            </div>

            <hr>
            <div class="attachment-note">
                📎 O relatório completo com todas as comparações (mês anterior, mesmo mês do ano anterior e média anual) está em anexo em PDF.
            </div>
        </div>
        <div class="footer">
            Izzycar · <a href="{{ url('/gestao') }}">Backoffice</a> · Gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i') }}
        </div>
    </div>
</body>
</html>
