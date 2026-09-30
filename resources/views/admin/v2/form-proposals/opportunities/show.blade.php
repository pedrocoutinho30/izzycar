@extends('layouts.admin-v2')

@section('title', 'Oportunidade — ' . $opportunity->title)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-text', 'label' => 'Formulários', 'href' => route('admin.v2.form-proposals.index')],
        ['icon' => '', 'label' => 'Pedido de ' . $formProposal->name, 'href' => route('admin.v2.form-proposals.show', $formProposal->id) . '#oportunidades'],
        ['icon' => '', 'label' => $opportunity->title],
    ],
    'title' => $opportunity->title,
    'subtitle' => 'Oportunidade do pedido de ' . $formProposal->name
        . ($formProposal->brand ? ' · procura ' . trim($formProposal->brand . ' ' . $formProposal->model) : '')
        . ($formProposal->budget ? ' · orçamento ' . number_format($formProposal->budget, 0, ',', '.') . ' €' : ''),
    'extraActions' => [
        ['href' => route('admin.v2.form-proposals.show', $formProposal->id) . '#oportunidades', 'icon' => 'bi-arrow-left', 'label' => 'Voltar às oportunidades'],
    ],
])

@include('admin.v2.form-proposals.opportunities._styles')
@include('admin.v2.form-proposals.opportunities._flash')

@if($errors->any())
<div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>Verifique os campos assinalados.
    <ul class="mb-0 mt-1 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@php
    $updateUrl = route('admin.v2.form-proposals.opportunities.update', [$formProposal->id, $opportunity->id]);
    $section = old('section');
@endphp

{{-- Resumo --}}
<div class="modern-card">
    <div class="row g-4">
        <div class="col-md-4 col-lg-3">
            @if($opportunity->photo_url)
                <a href="{{ $opportunity->photo_url }}" target="_blank"><img src="{{ $opportunity->photo_url }}" alt="{{ $opportunity->title }}" class="opp-summary-photo"></a>
            @else
                <div class="opp-summary-photo"><i class="bi bi-car-front"></i></div>
            @endif
        </div>
        <div class="col-md-8 col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <h4 class="mb-0 fw-bold">{{ $opportunity->title }}</h4>
                    @if($opportunity->version)<div class="text-muted">{{ $opportunity->version }}</div>@endif
                    <div class="fs-6 mt-1">
                        {{ $opportunity->mileage !== null ? number_format($opportunity->mileage, 0, ',', '.') . ' km' : '— km' }}
                        · <strong>{{ $opportunity->formatted_price ?? 'Sem preço' }}</strong>
                    </div>
                </div>
                @include('admin.v2.form-proposals.opportunities._status-dropdown')
            </div>

            <div class="opp-summary-grid my-3">
                <div><div class="label">Vendedor</div><div class="value">
                    @if($opportunity->seller)
                        <a href="{{ route('admin.v2.sellers.show', $opportunity->seller_id) }}">{{ $opportunity->seller->name }}</a>
                        @if($opportunity->sellerContact)<div class="small text-muted">{{ $opportunity->sellerContact->name }}</div>@endif
                    @else
                        —
                    @endif
                </div></div>
                <div><div class="label">Contacto</div><div class="value">{{ $opportunity->contact_method?->label() ?? '—' }} · <span class="badge text-bg-{{ $opportunity->contact_status->color() }} fw-normal">{{ $opportunity->contact_status->label() }}</span></div></div>
                <div><div class="label">Último contacto</div><div class="value">{{ $opportunity->last_contacted_at?->format('d/m/Y H:i') ?? '—' }}</div></div>
                <div><div class="label">Próximo follow-up</div><div class="value {{ $opportunity->next_followup_at?->isPast() ? 'text-danger' : '' }}">{{ $opportunity->next_followup_at?->format('d/m/Y') ?? '—' }}</div></div>
                <div><div class="label">País</div><div class="value">{{ \App\Models\ImportOpportunity::COUNTRIES[$opportunity->country] ?? '—' }}</div></div>
                <div><div class="label">Combustível</div><div class="value">{{ $opportunity->fuel?->label() ?? '—' }}</div></div>
                <div><div class="label">VIN</div><div class="value font-monospace">{{ $opportunity->vin ?: '—' }}</div></div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-7">@include('admin.v2.form-proposals.opportunities._progress')</div>
                <div class="col-md-5 d-flex flex-wrap gap-2 justify-content-md-end">
                    @include('admin.v2.form-proposals.opportunities._proposal-button', ['compact' => false])
                    @if($opportunity->listing_url)
                    <a href="{{ $opportunity->listing_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> Anúncio</a>
                    @endif
                    @php $sellerContact = $opportunity->sellerContact; @endphp
                    @if($sellerContact?->whatsapp_digits)
                    <a href="https://wa.me/{{ $sellerContact->whatsapp_digits }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    @endif
                    @if($sellerContact?->phone_normalized)
                    <a href="tel:+{{ $sellerContact->phone_normalized }}" class="btn btn-sm btn-outline-success" title="Ligar"><i class="bi bi-telephone"></i></a>
                    @endif
                    @if($sellerContact?->email)
                    <a href="mailto:{{ $sellerContact->email }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-envelope"></i> Email</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        {{-- Checklists aplicáveis (base + específicas do combustível) --}}
        <div id="checklist">
            @foreach($checklists as $checklist)
                @include('admin.v2.form-proposals.opportunities._checklist', [
                    'url' => route('admin.v2.form-proposals.opportunities.update-checklist', [$formProposal->id, $opportunity->id]),
                ])
            @endforeach
            @if(!$opportunity->fuel)
            <p class="small text-muted mt-n2 mb-4"><i class="bi bi-info-circle"></i> Indique o combustível nos dados do veículo para ativar checklists específicas (ex.: elétrico).</p>
            @endif
        </div>

        {{-- Notas livres (gravação automática) --}}
        <div class="modern-card" id="notas">
            <div class="modern-card-header">
                <h5 class="modern-card-title mb-0"><i class="bi bi-journal-text"></i> Notas</h5>
                <span class="small text-muted" id="oppNotesIndicator">{{ $opportunity->notes ? 'Guardado' : 'Gravação automática' }}</span>
            </div>
            <textarea class="form-control opp-notes" data-opp-notes data-indicator="#oppNotesIndicator"
                      data-url="{{ route('admin.v2.form-proposals.opportunities.update-notes', [$formProposal->id, $opportunity->id]) }}"
                      placeholder="Ex.: Vendedor diz que o carro está impecável. Pedi SOH e histórico oficial.">{{ $opportunity->notes }}</textarea>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Acompanhamento do contacto --}}
        <div class="modern-card" id="contacto">
            <div class="modern-card-header">
                <h5 class="modern-card-title mb-0"><i class="bi bi-headset"></i> Contacto com o vendedor</h5>
            </div>
            <form action="{{ $updateUrl }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="contacto">
                @php $cv = fn ($f, $d = null) => $section === 'contacto' ? old($f, $d) : $d; @endphp
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label">Método</label>
                        <select name="contact_method" class="form-select">
                            <option value="">—</option>
                            @foreach(\App\Enums\ContactMethod::options() as $value => $label)
                            <option value="{{ $value }}" @selected($cv('contact_method', $opportunity->contact_method?->value) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Estado do contacto</label>
                        <select name="contact_status" class="form-select">
                            @foreach(\App\Enums\ContactStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected($cv('contact_status', $opportunity->contact_status->value) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Contacto do vendedor</label>
                        @if($sellerContact)
                        <div class="small">
                            <strong>{{ $sellerContact->name }}</strong>@if($sellerContact->role) · {{ $sellerContact->role }}@endif
                            @if($sellerContact->email)<br><i class="bi bi-envelope text-muted"></i> {{ $sellerContact->email }}@endif
                            @if($sellerContact->phone)<br><i class="bi bi-telephone text-muted"></i> {{ $sellerContact->phone }}@endif
                            @if($sellerContact->whatsapp)<br><i class="bi bi-whatsapp text-muted"></i> {{ $sellerContact->whatsapp }}@endif
                        </div>
                        @else
                        <div class="small text-muted">Escolha o vendedor e o contacto em <a href="#veiculo">Dados do veículo</a>.</div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label">Contacto utilizado</label>
                        <input type="text" name="contact_used" class="form-control" value="{{ $cv('contact_used', $opportunity->contact_used) }}" maxlength="255" placeholder="Ex.: WhatsApp da empresa, email geral@...">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Último contacto</label>
                        <input type="datetime-local" name="last_contacted_at" class="form-control" value="{{ $cv('last_contacted_at', $opportunity->last_contacted_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Próximo follow-up</label>
                        <input type="date" name="next_followup_at" class="form-control" value="{{ $cv('next_followup_at', $opportunity->next_followup_at?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notas do contacto</label>
                        <textarea name="contact_notes" rows="3" class="form-control">{{ $cv('contact_notes', $opportunity->contact_notes) }}</textarea>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-sm btn-primary-modern"><i class="bi bi-check"></i> Guardar contacto</button>
                </div>
            </form>
        </div>

        {{-- Histórico de contactos --}}
        <div class="modern-card" id="historico">
            <div class="modern-card-header">
                <h5 class="modern-card-title mb-0"><i class="bi bi-clock-history"></i> Histórico de contactos</h5>
                <span class="badge bg-light text-dark fw-normal">{{ $opportunity->contacts->count() }}</span>
            </div>

            @php $isContactForm = old('_form') === 'contact-log'; @endphp
            <form action="{{ route('admin.v2.form-proposals.opportunities.contacts.store', [$formProposal->id, $opportunity->id]) }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="_form" value="contact-log">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <input type="datetime-local" name="contacted_at" class="form-control form-control-sm" required
                               value="{{ $isContactForm ? old('contacted_at') : now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-sm-6">
                        <select name="method" class="form-select form-select-sm" required>
                            @foreach(\App\Enums\ContactMethod::options() as $value => $label)
                            <option value="{{ $value }}" @selected(($isContactForm ? old('method') : ($opportunity->contact_method?->value ?? 'whatsapp')) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <select name="type" class="form-select form-select-sm" required>
                            @foreach(\App\Enums\ContactType::options() as $value => $label)
                            <option value="{{ $value }}" @selected($isContactForm && old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <textarea name="message" rows="2" class="form-control form-control-sm" placeholder="Ex.: Pedido VIN + SOH + histórico de manutenção.">{{ $isContactForm ? old('message') : '' }}</textarea>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <button type="submit" class="btn btn-sm btn-primary-modern"><i class="bi bi-plus"></i> Registar contacto</button>
                </div>
            </form>

            @if($opportunity->contacts->isEmpty())
                <p class="text-muted small mb-0">Ainda sem contactos registados.</p>
            @else
            <ul class="opp-timeline">
                @foreach($opportunity->contacts as $contact)
                <li>
                    <span class="dot"><i class="bi {{ $contact->type->icon() }}"></i></span>
                    <div class="d-flex justify-content-between gap-2">
                        <div class="when">
                            <strong>{{ $contact->contacted_at->format('d/m/Y H:i') }}</strong>
                            — <i class="bi {{ $contact->method->icon() }}"></i> {{ $contact->method->label() }}
                            · {{ $contact->type->label() }}
                            @if($contact->user) · {{ $contact->user->name }} @endif
                        </div>
                        <form action="{{ route('admin.v2.form-proposals.opportunities.contacts.destroy', [$formProposal->id, $opportunity->id, $contact->id]) }}" method="POST"
                              onsubmit="return confirm('Remover este registo do histórico?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link btn-sm p-0 text-muted" title="Remover"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @if($contact->message)<div class="msg">{{ $contact->message }}</div>@endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>

{{-- Dados do veículo --}}
<div class="modern-card" id="veiculo">
    <div class="modern-card-header">
        <h5 class="modern-card-title mb-0"><i class="bi bi-car-front"></i> Dados do veículo</h5>
    </div>
    <form action="{{ $updateUrl }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="veiculo">
        @include('admin.v2.form-proposals.opportunities._vehicle-fields', ['quick' => false])
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="small text-muted">
                Criada {{ $opportunity->created_at->format('d/m/Y H:i') }}@if($opportunity->creator) por {{ $opportunity->creator->name }}@endif
            </span>
            <button type="submit" class="btn btn-primary-modern"><i class="bi bi-check"></i> Guardar dados do veículo</button>
        </div>
    </form>
</div>

<div class="text-end mb-4">
    <form action="{{ route('admin.v2.form-proposals.opportunities.destroy', [$formProposal->id, $opportunity->id]) }}" method="POST"
          onsubmit="return confirm('Eliminar esta oportunidade, a checklist e o histórico de contactos?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar oportunidade</button>
    </form>
</div>

@include('admin.v2.form-proposals.opportunities._scripts')
@endsection
