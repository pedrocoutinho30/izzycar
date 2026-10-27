{{-- Tudo o que o pedido traz (formulário do site ou pedido manual), em pares
     rótulo/valor. Os campos e os rótulos são os da ficha do desktop
     (admin/v2/form-proposals/show) e de _request-fields. Espera $formProposal e $phones. --}}
@php
    $fp = $formProposal;
    $km = fn ($valor) => $valor ? number_format($valor, 0, ',', '.') . ' km' : null;
    // budget é texto na tabela: só se formata quando é um número.
    $euros = fn ($valor) => ! $valor ? null : (is_numeric($valor) ? number_format((float) $valor, 0, ',', '.') . ' €' : $valor);

    $seccoes = [
        ['icon' => 'bi-person', 'titulo' => 'Dados do cliente', 'campos' => [
            'Nome' => $fp->name,
            'Email' => $fp->email,
            'Telefone' => $fp->phone,
            'Origem' => $fp->isManual() ? 'Criado no backoffice' : 'Site' . ($fp->source ? ' · ' . $fp->source : ''),
            'Particular ou Empresa' => ['particular' => 'Particular', 'empresa' => 'Empresa'][$fp->buyer_type] ?? null,
            'Tipo de pagamento' => ['pronto_pagamento' => 'Pronto pagamento', 'financiamento' => 'Financiamento'][$fp->payment_type] ?? null,
            'Tempo expectável de compra' => ['imediato' => 'Imediato (até 30 dias)', '1_3_meses' => '1-3 meses', '3_6_meses' => '3-6 meses', 'pesquisar' => 'Apenas a pesquisar'][$fp->estimated_purchase_date] ?? null,
        ]],
        ['icon' => 'bi-car-front', 'titulo' => 'Especificações pretendidas', 'campos' => [
            'Nome do pedido' => $fp->title,
            'Marca' => $fp->brand,
            'Modelo' => $fp->model,
            'Versão' => $fp->version,
            'Combustível' => $fp->fuel,
            'Caixa' => $fp->gearbox,
            'Ano mínimo' => $fp->year_min,
            'KM máximo' => $km($fp->km_max),
            'Cor' => $fp->color,
            'Orçamento' => $euros($fp->budget),
            'Extras pretendidos' => $fp->extras,
        ]],
    ];

    if ($fp->retoma_option === 'sim') {
        $seccoes[] = ['icon' => 'bi-arrow-left-right', 'titulo' => 'Retoma', 'campos' => [
            'Marca' => $fp->retoma_brand,
            'Modelo' => $fp->retoma_model,
            'Ano' => $fp->retoma_year,
            'KM' => $km($fp->retoma_km),
            'Combustível' => $fp->retoma_fuel,
            'Mais informações' => $fp->retoma_info,
        ]];
    }

    $links = $fp->ad_option === 'sim' && $fp->ad_links
        ? array_values(array_filter(array_map('trim', explode("\n", $fp->ad_links))))
        : [];
    $tel = $phones->telUrl($fp->phone);
    $whatsapp = $phones->whatsappUrl($fp->phone);
@endphp

@foreach($seccoes as $seccao)
    <div class="m-card">
        <div class="fw-semibold mb-2"><i class="bi {{ $seccao['icon'] }} me-1"></i> {{ $seccao['titulo'] }}</div>
        <dl class="m-dl mb-0">
            @foreach($seccao['campos'] as $rotulo => $valor)
                <dt>{{ $rotulo }}</dt>
                <dd class="text-break">
                    @if(blank($valor))
                        <span class="text-muted">—</span>
                    @elseif($rotulo === 'Email' && $seccao['icon'] === 'bi-person')
                        <a href="mailto:{{ $valor }}">{{ $valor }}</a>
                    @elseif($rotulo === 'Telefone' && $tel)
                        <a href="{{ $tel }}">{{ $valor }}</a>
                    @else
                        {{ $valor }}
                    @endif
                </dd>
            @endforeach
        </dl>
        @if($seccao['icon'] === 'bi-person' && ($tel || $fp->email))
            <div class="m-contact-actions">
                @if($tel)<a href="{{ $tel }}" class="btn btn-outline-secondary" aria-label="Ligar"><i class="bi bi-telephone"></i></a>@endif
                @if($whatsapp)<a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>@endif
                @if($fp->email)<a href="mailto:{{ $fp->email }}" class="btn btn-outline-primary" aria-label="Email"><i class="bi bi-envelope"></i></a>@endif
            </div>
        @endif
        @if($seccao['titulo'] === 'Retoma' && !empty($fp->retoma_photos))
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($fp->retoma_photos as $foto)
                    <a href="{{ Storage::disk('public')->url($foto) }}" target="_blank" rel="noopener">
                        <img src="{{ Storage::disk('public')->url($foto) }}" alt="Foto da retoma" width="88" height="88" style="object-fit:cover;border-radius:8px;border:1px solid var(--admin-border)">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endforeach

@if($links)
    <div class="m-card">
        <div class="fw-semibold mb-2"><i class="bi bi-link-45deg me-1"></i> Anúncio identificado</div>
        <div class="d-grid gap-2">
            @foreach($links as $link)
                {{-- ad_links é texto livre do formulário público: só http(s) vira ligação. --}}
                @if(\App\Support\SafeUrl::isHttp($link))
                    <a href="{{ trim($link) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary text-start text-break">
                        <i class="bi bi-box-arrow-up-right me-1"></i> {{ $link }}
                    </a>
                @else
                    <div class="text-break text-muted small px-1">{{ $link }}</div>
                @endif
            @endforeach
        </div>
    </div>
@endif

@if($fp->message)
    <div class="m-card">
        <div class="fw-semibold mb-2"><i class="bi bi-chat-text me-1"></i> {{ $fp->isManual() ? 'Notas' : 'Mensagem' }}</div>
        <div style="white-space: pre-line">{{ $fp->message }}</div>
    </div>
@endif

<div class="m-card">
    <dl class="m-dl mb-0">
        <dt>{{ $fp->isManual() ? 'Criado em' : 'Recebido em' }}</dt><dd>{{ $fp->created_at->format('d/m/Y H:i') }} ({{ $fp->created_at->diffForHumans() }})</dd>
        @if($fp->creator)
            <dt>Criado por</dt><dd>{{ $fp->creator->name }}</dd>
        @endif
    </dl>
</div>
