<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow-ups de amanhã</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #b30000; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0 0 4px; font-size: 1.3rem; font-weight: 700; }
        .header p { margin: 0; font-size: .9rem; opacity: .85; }
        .body { padding: 28px 32px; }
        .section-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #999; margin: 0 0 12px; }
        .lead-row { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
        .lead-row:last-child { border-bottom: none; }
        .lead-time { background: #fff3cd; color: #664d03; border-radius: 6px; padding: 4px 10px; font-size: .8rem; font-weight: 700; white-space: nowrap; min-width: 52px; text-align: center; }
        .lead-info { flex: 1; }
        .lead-name { font-weight: 600; font-size: .95rem; color: #111; }
        .lead-note { font-size: .82rem; color: #666; margin-top: 2px; }
        .lead-phone { font-size: .8rem; color: #999; margin-top: 2px; }
        .lead-link { display: inline-block; margin-top: 4px; font-size: .78rem; color: #b30000; text-decoration: none; }
        .empty { color: #999; font-size: .9rem; text-align: center; padding: 24px 0; }
        .footer { padding: 20px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: .78rem; color: #aaa; text-align: center; }
        .footer a { color: #b30000; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Follow-ups de amanhã</h1>
            <p>{{ $data }} — {{ $followups->count() }} agendado{{ $followups->count() != 1 ? 's' : '' }}</p>
        </div>
        <div class="body">
            <div class="section-title">Leads para contactar amanhã</div>

            @forelse($followups as $lead)
            <div class="lead-row">
                <div class="lead-time">
                    {{ $lead->next_followup_at->format('H:i') }}
                </div>
                <div class="lead-info">
                    <div class="lead-name">{{ $lead->name }}</div>
                    @if($lead->followup_note)
                        <div class="lead-note">{{ $lead->followup_note }}</div>
                    @endif
                    @if($lead->phone)
                        <div class="lead-phone">📞 {{ $lead->phone }}</div>
                    @endif
                    @if($lead->email)
                        <div class="lead-phone">✉️ {{ $lead->email }}</div>
                    @endif
                    <a href="{{ route('admin.v2.leads.show', $lead->id) }}" class="lead-link">Ver no BO →</a>
                </div>
            </div>
            @empty
            <div class="empty">Nenhum follow-up agendado para amanhã.</div>
            @endforelse
        </div>
        <div class="footer">
            Izzycar · <a href="{{ url('/admin') }}">Backoffice</a>
        </div>
    </div>
</body>
</html>
