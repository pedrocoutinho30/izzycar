<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comissões de angariadores em atraso</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #b30000; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0 0 4px; font-size: 1.3rem; font-weight: 700; }
        .header p { margin: 0; font-size: .9rem; opacity: .85; }
        .body { padding: 28px 32px; }
        .section-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #999; margin: 0 0 12px; }
        .row { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
        .row:last-child { border-bottom: none; }
        .row-badge { background: #f8d7da; color: #842029; border-radius: 6px; padding: 4px 10px; font-size: .78rem; font-weight: 700; white-space: nowrap; min-width: 70px; text-align: center; }
        .row-info { flex: 1; }
        .row-name { font-weight: 600; font-size: .95rem; color: #111; }
        .row-detail { font-size: .82rem; color: #666; margin-top: 2px; }
        .row-value { font-size: .82rem; color: #333; margin-top: 2px; font-weight: 600; }
        .row-link { display: inline-block; margin-top: 4px; font-size: .78rem; color: #b30000; text-decoration: none; }
        .footer { padding: 20px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: .78rem; color: #aaa; text-align: center; }
        .footer a { color: #b30000; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Comissões de angariadores em atraso</h1>
            <p>{{ $convertedProposals->count() }} comissão{{ $convertedProposals->count() != 1 ? 'ões' : '' }} há mais de 48h após a entrega, ainda não paga{{ $convertedProposals->count() != 1 ? 's' : '' }}</p>
        </div>
        <div class="body">
            <div class="section-title">Comissões em atraso</div>

            @forelse($convertedProposals as $convertedProposal)
            <div class="row">
                <div class="row-badge">
                    {{ $convertedProposal->deliveredAt()?->diffForHumans(null, true) }}
                </div>
                <div class="row-info">
                    <div class="row-name">{{ $convertedProposal->owner->name ?? 'Sem angariador' }} — {{ $convertedProposal->client->name ?? 'Cliente #' . $convertedProposal->client_id }}</div>
                    <div class="row-detail">{{ trim(($convertedProposal->brand ?? '') . ' ' . ($convertedProposal->modelCar ?? '')) }}</div>
                    <div class="row-value">{{ number_format($convertedProposal->angariadorCommissionAmount() ?? 0, 2, ',', '.') }} €</div>
                    <a href="{{ route('admin.v2.angariadores.comissoes') }}" class="row-link">Ver no BO →</a>
                </div>
            </div>
            @empty
            <div class="row-detail">Nenhuma comissão em atraso.</div>
            @endforelse
        </div>
        <div class="footer">
            Izzycar · <a href="{{ url('/admin') }}">Backoffice</a>
        </div>
    </div>
</body>
</html>
