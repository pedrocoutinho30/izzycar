@extends('layouts.admin-mobile')

@section('title', $opportunity->title)

@section('header_actions')
    <a href="{{ route('admin.mobile.form-proposals.show', $formProposal->id) }}#oportunidades" class="order-first m-topbar-back" aria-label="Voltar ao pedido"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@php
    $rota = fn (string $nome, ...$extra) => route("admin.mobile.form-proposals.opportunities.{$nome}", [$formProposal->id, $opportunity->id, ...$extra]);
    $status = $opportunity->status;
    $sellerContact = $opportunity->sellerContact;
    $isContactForm = old('_form') === 'contact-log';
@endphp

@section('content')
    {{-- Resumo --}}
    <div class="m-card">
        @if($opportunity->photo_url)
            <img src="{{ $opportunity->photo_url }}" alt="{{ $opportunity->title }}" class="w-100 mb-2" style="max-height:200px;object-fit:cover;border-radius:8px">
        @endif
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">{{ $opportunity->title }}</div>
                @if($opportunity->version)<div class="text-muted small">{{ $opportunity->version }}</div>@endif
                <div class="mt-1">
                    {{ $opportunity->mileage !== null ? number_format($opportunity->mileage, 0, ',', '.') . ' km' : '— km' }}
                    · <strong>{{ $opportunity->formatted_price ?? 'Sem preço' }}</strong>
                </div>
            </div>
            <span class="badge text-bg-{{ $status->color() }} flex-shrink-0"><i class="bi {{ $status->icon() }}"></i> {{ $status->label() }}</span>
        </div>
        <div class="small text-muted mt-1">Pedido: {{ $formProposal->label }} · {{ $formProposal->name }}</div>

        @if($opportunity->client_quote_requested_at)
            <div class="alert alert-primary py-2 mt-2 mb-0 small">
                <i class="bi bi-hand-index-thumb me-1"></i> O cliente pediu cotação para esta viatura em {{ $opportunity->client_quote_requested_at->format('d/m/Y \à\s H:i') }}.
            </div>
        @endif

        <dl class="m-dl mt-3 mb-0">
            <dt>Vendedor</dt><dd class="text-break">{{ $opportunity->seller?->name ?? '—' }}@if($sellerContact)<div class="small text-muted">{{ $sellerContact->name }}</div>@endif</dd>
            <dt>Contacto</dt><dd>{{ $opportunity->contact_method?->label() ?? '—' }} · {{ $opportunity->contact_status->label() }}</dd>
            <dt>Último contacto</dt><dd>{{ $opportunity->last_contacted_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            <dt>Follow-up</dt><dd class="{{ $opportunity->next_followup_at?->isPast() ? 'text-danger' : '' }}">{{ $opportunity->next_followup_at?->format('d/m/Y') ?? '—' }}</dd>
            <dt>País</dt><dd>{{ \App\Models\ImportOpportunity::COUNTRIES[$opportunity->country] ?? '—' }}</dd>
            <dt>Combustível</dt><dd>{{ $opportunity->fuel?->label() ?? '—' }}</dd>
            <dt>Nº de oferta</dt><dd class="text-break">{{ $opportunity->offer_number ?: '—' }}</dd>
            <dt>VIN</dt><dd class="font-monospace text-break">{{ $opportunity->vin ?: '—' }}</dd>
        </dl>

        @if($opportunity->listing_url || $sellerContact?->whatsapp_digits || $sellerContact?->phone_normalized || $sellerContact?->email)
            <div class="m-contact-actions">
                @if($opportunity->listing_url)
                    <a href="{{ $opportunity->listing_url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary" aria-label="Ver anúncio"><i class="bi bi-box-arrow-up-right"></i></a>
                @endif
                @if($sellerContact?->whatsapp_digits)
                    <a href="https://wa.me/{{ $sellerContact->whatsapp_digits }}" target="_blank" rel="noopener" class="btn btn-outline-success" aria-label="WhatsApp do vendedor"><i class="bi bi-whatsapp"></i></a>
                @endif
                @if($sellerContact?->phone_normalized)
                    <a href="tel:+{{ $sellerContact->phone_normalized }}" class="btn btn-outline-success" aria-label="Ligar ao vendedor"><i class="bi bi-telephone"></i></a>
                @endif
                @if($sellerContact?->email)
                    <a href="mailto:{{ $sellerContact->email }}" class="btn btn-outline-primary" aria-label="Email do vendedor"><i class="bi bi-envelope"></i></a>
                @endif
            </div>
        @endif

        @if($canUpdate)
            <form action="{{ $rota('status') }}" method="POST" class="d-flex gap-2 mt-3">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select" aria-label="Estado da oportunidade">
                    @foreach(\App\Enums\OpportunityStatus::cases() as $opcao)
                        <option value="{{ $opcao->value }}" @selected($opcao === $status)>{{ $opcao->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary flex-shrink-0">Mudar</button>
            </form>
        @endif
    </div>

    {{-- Checklists aplicáveis (base + específicas do combustível) --}}
    <div id="checklist">
        <div class="m-card">
            <div class="d-flex justify-content-between small">
                <span>Informação: <strong>{{ $progress->confirmed }}</strong>/{{ $progress->total }} confirmados</span>
                <span>{{ $progress->percent() }}%</span>
            </div>
            <div class="progress mt-1" style="height:8px" role="progressbar" aria-label="Progresso da checklist" aria-valuenow="{{ $progress->percent() }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: {{ $progress->confirmedWidth() }}%"></div>
                <div class="progress-bar bg-secondary" style="width: {{ $progress->notApplicableWidth() }}%"></div>
            </div>
            <div class="small text-muted mt-1">{{ $progress->pending() }} por confirmar @if($progress->notApplicable) · {{ $progress->notApplicable }} N/A @endif</div>
        </div>

        @foreach($checklists as $checklist)
            <div class="m-card">
                <div class="fw-semibold mb-1"><i class="bi bi-list-check me-1"></i> {{ $checklist->name }}</div>
                @foreach($checklist->items as $item)
                    @php $atual = $checklistStatuses[$item->id] ?? \App\Enums\ChecklistItemStatus::Pending; @endphp
                    <div class="m-list-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $atual->icon() }} text-{{ $atual->color() }}"></i>
                            <span class="flex-grow-1">{{ $item->label }}</span>
                            @unless($canUpdate)<span class="small text-muted">{{ $atual->label() }}</span>@endunless
                        </div>
                        @if($canUpdate)
                            <form action="{{ $rota('checklist') }}" method="POST" class="btn-group w-100 mt-2" role="group" aria-label="{{ $item->label }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                @foreach(\App\Enums\ChecklistItemStatus::cases() as $opcao)
                                    <button type="submit" name="status" value="{{ $opcao->value }}"
                                            class="btn btn-sm {{ $opcao === $atual ? 'btn-' . $opcao->color() : 'btn-outline-' . $opcao->color() }}"
                                            @if($opcao === $atual) aria-pressed="true" @endif>{{ $opcao->label() }}</button>
                                @endforeach
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
        @if(!$opportunity->fuel)
            <p class="small text-muted"><i class="bi bi-info-circle"></i> Indique o combustível nos dados do veículo (desktop) para ativar checklists específicas (ex.: elétrico).</p>
        @endif
    </div>

    {{-- Notas livres --}}
    <div class="m-card" id="notas">
        <div class="fw-semibold mb-2"><i class="bi bi-journal-text me-1"></i> Notas</div>
        @if($canUpdate)
            <form action="{{ $rota('notes') }}" method="POST" class="d-grid gap-2">
                @csrf
                @method('PATCH')
                <textarea name="notes" rows="5" maxlength="20000" class="form-control" aria-label="Notas da oportunidade"
                          placeholder="Ex.: Vendedor diz que o carro está impecável. Pedi SOH e histórico oficial.">{{ old('notes', $opportunity->notes) }}</textarea>
                <button type="submit" class="btn btn-outline-primary">Guardar notas</button>
            </form>
        @elseif($opportunity->notes)
            <div style="white-space: pre-line">{{ $opportunity->notes }}</div>
        @else
            <div class="text-muted small">Sem notas.</div>
        @endif
    </div>

    {{-- Histórico de contactos com o vendedor --}}
    <div class="m-card" id="historico">
        <div class="fw-semibold mb-2">
            <i class="bi bi-clock-history me-1"></i> Histórico de contactos
            <span class="badge bg-secondary rounded-pill ms-1">{{ $opportunity->contacts->count() }}</span>
        </div>

        @if($canUpdate)
            <form action="{{ $rota('contacts.store') }}" method="POST" class="d-grid gap-2 mb-3">
                @csrf
                <input type="hidden" name="_form" value="contact-log">
                <input type="datetime-local" name="contacted_at" class="form-control" required aria-label="Data e hora do contacto"
                       value="{{ $isContactForm ? old('contacted_at') : now()->format('Y-m-d\TH:i') }}">
                <select name="method" class="form-select" required aria-label="Método de contacto">
                    @foreach(\App\Enums\ContactMethod::options() as $valor => $label)
                        <option value="{{ $valor }}" @selected(($isContactForm ? old('method') : ($opportunity->contact_method?->value ?? 'whatsapp')) === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-select" required aria-label="Tipo de contacto">
                    @foreach(\App\Enums\ContactType::options() as $valor => $label)
                        <option value="{{ $valor }}" @selected($isContactForm && old('type') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="2" maxlength="5000" class="form-control" aria-label="Mensagem"
                          placeholder="Ex.: Pedido VIN + SOH + histórico de manutenção.">{{ $isContactForm ? old('message') : '' }}</textarea>
                <button type="submit" class="btn btn-primary">Registar contacto</button>
            </form>
        @endif

        @forelse($opportunity->contacts as $contacto)
            <div class="m-timeline-item">
                <span class="m-timeline-icon bg-secondary"><i class="bi {{ $contacto->type->icon() }}"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold">{{ $contacto->contacted_at->format('d/m/Y H:i') }}</div>
                    <div class="small">
                        <i class="bi {{ $contacto->method->icon() }}"></i> {{ $contacto->method->label() }} · {{ $contacto->type->label() }}
                        @if($contacto->user) · {{ $contacto->user->name }} @endif
                    </div>
                    @if($contacto->message)<div class="small text-break" style="white-space: pre-line">{{ $contacto->message }}</div>@endif
                </div>
                @if($canUpdate)
                    <form action="{{ $rota('contacts.destroy', $contacto->id) }}" method="POST" onsubmit="return confirm('Remover este registo do histórico?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-muted" aria-label="Remover registo" style="min-width:44px"><i class="bi bi-trash"></i></button>
                    </form>
                @endif
            </div>
        @empty
            <div class="text-muted small">Ainda sem contactos registados.</div>
        @endforelse
    </div>

    <div class="d-grid gap-2 mt-3">
        @canroute('admin.v2.form-proposals.opportunities.show')
        <a href="{{ route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        @endcanroute
    </div>
@endsection
