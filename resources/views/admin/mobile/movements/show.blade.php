@extends('layouts.admin-mobile')

@php
    $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    $entrada = $movement->isIncome();
    $statusColors = ['paid' => 'success', 'pending' => 'warning', 'partially_paid' => 'info', 'cancelled' => 'secondary'];
    $client = $movement->client;
    $fichaCliente = match (true) {
        ! $canViewClient => null,
        (bool) $client->is_lead => route('admin.mobile.leads.show', $client->id),
        default => route('admin.mobile.clients.show', $client->id),
    };
    $origem = match ($source[0] ?? null) {
        'converted' => 'uma cotação convertida (pagamentos da importação)',
        'sale' => 'uma venda',
        'v3_vehicle', 'vehicle' => 'a compra de uma viatura',
        default => 'outro registo',
    };
    $leg = $movement->legalization;
@endphp

@section('title', 'Movimento #' . $movement->id)

@section('header_actions')
    <a href="{{ route('admin.mobile.movements.index') }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    {{-- Resumo --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="fw-semibold fs-5 text-break min-w-0">{{ $movement->title }}</div>
            <span class="badge bg-{{ $statusColors[$movement->status] ?? 'secondary' }} flex-shrink-0">{{ $movement->status_label }}</span>
        </div>
        <div class="small text-muted mt-1">
            {{ $movement->movement_type_label }} · {{ $movement->category_label }} · {{ $movement->expense_date?->format('d/m/Y') ?? '—' }}
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded" style="background:#fbf1f1; border:1px solid var(--admin-primary)">
            <span class="fw-semibold">Valor bruto</span>
            <span class="fs-4 fw-bold {{ $entrada ? 'text-success' : 'text-danger' }}">{{ $entrada ? '+' : '−' }}{{ $euros($movement->amount_gross ?? $movement->amount) }}</span>
        </div>
        <dl class="m-dl small mt-3 mb-0">
            <dt>IVA</dt>
            <dd>{{ $movement->vat_rate ? number_format($movement->vat_rate, 0) . '%' : '—' }}@if($movement->vat_amount) · {{ $euros($movement->vat_amount) }}@endif</dd>
            <dt>Líquido</dt>
            <dd>{{ $euros($movement->amount_net ?? $movement->amount) }}</dd>
            <dt>Pagamento</dt>
            <dd>{{ \App\Models\Expense::paymentMethods()[$movement->payment_method] ?? ($movement->payment_method ?: '—') }}</dd>
        </dl>
    </div>

    @if($automatic)
        <div class="alert alert-secondary d-flex gap-2">
            <i class="bi bi-lock-fill"></i>
            <div>
                Gerado automaticamente a partir de {{ $origem }}: só se altera na origem.
                @if($converted)
                    <a href="{{ route('admin.mobile.converted-proposals.show', $converted->id) }}" class="btn btn-outline-primary w-100 mt-2">
                        <i class="bi bi-arrow-repeat me-1"></i> Cotação convertida #{{ $converted->id }}
                    </a>
                @endif
                @if($sourceDesktopUrl)
                    <a href="{{ $sourceDesktopUrl }}" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-display me-1"></i> Origem no desktop
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Ligações --}}
    @if($movement->v3Vehicle || $client || $movement->partner || $leg)
        <div class="m-card">
            <div class="fw-semibold mb-2"><i class="bi bi-link-45deg me-1"></i> Associado a</div>
            <dl class="m-dl small mb-0">
                @if($movement->v3Vehicle)
                    <dt>Viatura</dt>
                    <dd class="text-break">
                        {{ $movement->v3Vehicle->reference }} – {{ $movement->v3Vehicle->brand }} {{ $movement->v3Vehicle->model }}
                        @canroute('admin.v3.vehicles.edit')
                            <a href="{{ route('admin.v3.vehicles.edit', $movement->v3Vehicle->id) }}" class="d-block" style="min-height:44px; line-height:44px">Viatura no desktop</a>
                        @endcanroute
                    </dd>
                @endif
                @if($client)
                    <dt>Cliente</dt>
                    <dd class="text-break">
                        @if($fichaCliente)
                            <a href="{{ $fichaCliente }}" class="d-inline-flex align-items-center" style="min-height:44px">{{ $client->name }}</a>
                        @else
                            {{ $client->name }}
                        @endif
                    </dd>
                @endif
                @if($movement->partner)
                    <dt>Parceiro</dt>
                    <dd class="text-break">{{ $movement->partner->name }}</dd>
                @endif
                @if($leg)
                    <dt>Legalização</dt>
                    <dd class="text-break">#{{ $leg->id }} — {{ $leg->marca }} {{ $leg->modelo }}@if($leg->matricula) — {{ $leg->matricula }}@endif</dd>
                @endif
            </dl>
        </div>
    @endif

    @if($movement->observations)
        <div class="m-card">
            <div class="fw-semibold mb-1">Observações</div>
            <div class="small text-break" style="white-space: pre-line">{{ $movement->observations }}</div>
        </div>
    @endif

    @if($attachmentUrl)
        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary w-100 mb-3">
            <i class="bi bi-paperclip me-1"></i> Ver comprovativo
        </a>
    @endif

    <div class="d-grid gap-2">
        @if($canUpdate)
            <a href="{{ route('admin.mobile.movements.edit', $movement->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
        @endif
        @if($canDelete)
            <form method="POST" action="{{ route('admin.mobile.movements.destroy', $movement->id) }}" class="d-grid"
                  onsubmit="return confirm('Eliminar este movimento?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Eliminar</button>
            </form>
        @endif
        @if($canUpdate)
            @canroute('admin.v2.movements.edit')
                <a href="{{ route('admin.v2.movements.edit', $movement->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-display me-1"></i> Abrir no desktop
                </a>
                <div class="small text-muted text-center">O comprovativo envia-se e remove-se no desktop.</div>
            @endcanroute
        @endif
        <div class="small text-muted text-center">
            Criado a {{ $movement->created_at?->format('d/m/Y H:i') }}@if($movement->updated_at && $movement->updated_at->ne($movement->created_at)) · atualizado a {{ $movement->updated_at->format('d/m/Y H:i') }}@endif
        </div>
    </div>
@endsection
