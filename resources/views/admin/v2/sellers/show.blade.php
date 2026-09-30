@extends('layouts.admin-v2')

@section('title', 'Vendedor: ' . $seller->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-shop', 'label' => 'Vendedores', 'href' => route('admin.v2.sellers.index')],
        ['icon' => '', 'label' => $seller->name, 'href' => ''],
    ],
    'title' => $seller->name,
    'subtitle' => implode(' · ', array_filter([$seller->country_label, $seller->is_active ? null : 'Inativo'])) ?: 'Detalhe do vendedor',
    'actionHref' => route('admin.v2.sellers.edit', $seller->id),
    'actionLabel' => 'Editar vendedor',
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>Verifique os campos assinalados.
    <ul class="mb-0 mt-1 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@unless($seller->is_active)
<div class="alert alert-secondary mb-3">
    <i class="bi bi-slash-circle me-2"></i>Este vendedor está <strong>inativo</strong>: não aparece na pesquisa das Oportunidades, mas o histórico mantém-se.
</div>
@endunless

@php
    $sellerCountry = $seller->country ?? '';
    $contactFormId = old('_form');
@endphp

<div class="row g-4">

    {{-- ─── Sidebar esquerda ─── --}}
    <div class="col-lg-4">
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-shop"></i>
                    Dados do vendedor
                </h5>
                <span class="badge {{ $seller->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $seller->is_active ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <div class="modern-card-body">
                <ul class="list-unstyled mb-0">
                    @if($seller->website_url)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-globe text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Website</small>
                            <a href="{{ $seller->website_url }}" target="_blank" rel="noopener">{{ $seller->website }}</a>
                        </div>
                    </li>
                    @endif
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-flag text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">País</small>
                            {{ $seller->country_label ?? '—' }}
                        </div>
                    </li>
                    @if($seller->address)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Morada</small>
                            <span style="white-space: pre-line">{{ $seller->address }}</span>
                        </div>
                    </li>
                    @endif
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-at text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Domínio(s) de email</small>
                            @forelse($seller->domains as $domain)
                                <span class="badge bg-light text-dark border">{{ $domain->domain }}</span>
                            @empty
                                —
                            @endforelse
                        </div>
                    </li>
                    <li class="mb-0 d-flex align-items-start gap-2">
                        <i class="bi bi-calendar text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Registado em</small>
                            {{ $seller->created_at->format('d/m/Y') }}@if($seller->creator) por {{ $seller->creator->name }}@endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        @if($seller->notes)
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-chat-left-text"></i>
                    Notas
                </h5>
            </div>
            <div class="modern-card-body">
                <p class="mb-0" style="white-space: pre-line">{{ $seller->notes }}</p>
            </div>
        </div>
        @endif

        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-toggle-on"></i>
                    Estado
                </h5>
            </div>
            <div class="d-grid gap-2">
                <form action="{{ route('admin.v2.sellers.toggle-active', $seller->id) }}" method="POST" class="d-grid"
                      @if($seller->is_active) onsubmit="return confirm('Desativar este vendedor? Deixa de aparecer na pesquisa das Oportunidades, mas o histórico mantém-se.')" @endif>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary-modern">
                        <i class="bi {{ $seller->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                        {{ $seller->is_active ? 'Desativar vendedor' : 'Reativar vendedor' }}
                    </button>
                </form>
                @if($canDelete)
                <form action="{{ route('admin.v2.sellers.destroy', $seller->id) }}" method="POST" class="d-grid"
                      onsubmit="return confirm('Eliminar este vendedor? Esta ação não pode ser desfeita.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Eliminar vendedor</button>
                </form>
                @else
                <small class="text-muted">Com contactos, veículos ou oportunidades associados, o vendedor não pode ser eliminado — só desativado.</small>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Coluna principal ─── --}}
    <div class="col-lg-8">

        {{-- Contactos --}}
        <div class="modern-card mb-4" id="contactos">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-people"></i>
                    Contactos
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary rounded-pill">{{ $seller->contacts->count() }}</span>
                    <button type="button" class="btn btn-sm btn-primary-modern" data-bs-toggle="modal" data-bs-target="#contactCreateModal">
                        <i class="bi bi-plus"></i> Novo contacto
                    </button>
                </div>
            </div>

            @if($seller->contacts->isEmpty())
                <div class="modern-card-body text-center py-4">
                    <i class="bi bi-people text-muted" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-0">Ainda sem contactos.</p>
                </div>
            @else
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone / WhatsApp</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->contacts as $contact)
                            <tr id="contacto-{{ $contact->id }}" class="seller-contact-row {{ $contact->is_active ? '' : 'text-muted' }}">
                                <td>
                                    <strong>{{ $contact->name }}</strong>
                                    @if($contact->is_primary)<span class="badge bg-primary ms-1">Principal</span>@endif
                                    @unless($contact->is_active)<span class="badge bg-secondary ms-1">Inativo</span>@endunless
                                    @if($contact->role)<br><small class="text-muted">{{ $contact->role }}</small>@endif
                                </td>
                                <td>
                                    @if($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@else — @endif
                                </td>
                                <td>
                                    @if($contact->phone)
                                        <a href="tel:+{{ $contact->phone_normalized }}"><i class="bi bi-telephone"></i> {{ $contact->phone }}</a><br>
                                    @endif
                                    @if($contact->whatsapp_normalized)
                                        <a href="https://wa.me/{{ $contact->whatsapp_normalized }}" target="_blank" rel="noopener" class="text-success">
                                            <i class="bi bi-whatsapp"></i> {{ $contact->whatsapp }}
                                        </a>
                                    @endif
                                    @if(!$contact->phone && !$contact->whatsapp) — @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Editar" data-bs-toggle="modal" data-bs-target="#contactEditModal-{{ $contact->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($contact->is_active && !$contact->is_primary)
                                    <form action="{{ route('admin.v2.sellers.contacts.primary', [$seller->id, $contact->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Definir como principal"><i class="bi bi-star"></i></button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.v2.sellers.contacts.toggle-active', [$seller->id, $contact->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $contact->is_active ? 'Desativar' : 'Reativar' }}">
                                            <i class="bi {{ $contact->is_active ? 'bi-slash-circle' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @if($contact->notes)
                            <tr class="{{ $contact->is_active ? '' : 'text-muted' }}">
                                <td colspan="4" class="small pt-0 border-top-0" style="white-space: pre-line"><i class="bi bi-chat-left-text me-1"></i>{{ $contact->notes }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Veículos comprados a este vendedor --}}
        <div class="modern-card mb-4" id="veiculos">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-car-front"></i>
                    Veículos
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $seller->vehicles->count() }}</span>
            </div>

            @if($seller->vehicles->isEmpty())
                <div class="modern-card-body text-center py-4">
                    <i class="bi bi-car-front text-muted" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-0">Ainda sem veículos comprados a este vendedor.</p>
                </div>
            @else
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Veículo</th>
                                <th>VIN</th>
                                <th>Data</th>
                                <th class="text-end">Preço</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->vehicles as $vehicle)
                            <tr>
                                <td>
                                    <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong>
                                    @if($vehicle->reference)<br><small class="text-muted">Ref: {{ $vehicle->reference }}</small>@endif
                                    @if($vehicle->sellerContact)<br><small class="text-muted"><i class="bi bi-person"></i> {{ $vehicle->sellerContact->name }}</small>@endif
                                </td>
                                <td class="font-monospace small">{{ $vehicle->vin ?: '—' }}</td>
                                <td>{{ $vehicle->purchase_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">{{ $vehicle->purchase_price ? number_format($vehicle->purchase_price, 0, ',', '.') . ' €' : '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.v3.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Oportunidades (inclui negócios que não se concretizaram) --}}
        <div class="modern-card mb-4" id="oportunidades">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-search"></i>
                    Oportunidades
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $seller->opportunities->count() }}</span>
            </div>

            @if($seller->opportunities->isEmpty())
                <div class="modern-card-body text-center py-4">
                    <i class="bi bi-search text-muted" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-0">Ainda sem oportunidades com este vendedor.</p>
                </div>
            @else
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Oportunidade</th>
                                <th>Pedido</th>
                                <th>Estado</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->opportunities as $opportunity)
                            <tr>
                                <td>
                                    <strong>{{ $opportunity->title }}</strong>
                                    @if($opportunity->formatted_price)<span class="text-muted"> · {{ $opportunity->formatted_price }}</span>@endif
                                    @if($opportunity->sellerContact)<br><small class="text-muted"><i class="bi bi-person"></i> {{ $opportunity->sellerContact->name }}</small>@endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.v2.form-proposals.show', $opportunity->form_proposal_id) }}#oportunidades">{{ $opportunity->formProposal?->name ?? '—' }}</a>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $opportunity->status->color() }}"><i class="bi {{ $opportunity->status->icon() }}"></i> {{ $opportunity->status->label() }}</span>
                                </td>
                                <td>{{ $opportunity->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.v2.form-proposals.opportunities.show', [$opportunity->form_proposal_id, $opportunity->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: novo contacto --}}
@php $isCreate = $contactFormId === 'contact-create'; @endphp
<div class="modal fade" id="contactCreateModal" tabindex="-1" aria-hidden="true" @if($isCreate && $errors->any()) data-seller-open-on-load @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.sellers.contacts.store', $seller->id) }}" method="POST"
                  data-seller-form="contact" data-seller-id="{{ $seller->id }}" data-seller-country="{{ $sellerCountry }}"
                  @if($isCreate && $errors->has('matches')) data-recheck @endif>
                @csrf
                <input type="hidden" name="_form" value="contact-create">
                <input type="hidden" name="confirm_matches" value="0">
                <div class="modal-header">
                    <h5 class="modal-title">Novo contacto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div data-dup-alert></div>
                    @include('admin.v2.sellers._contact-fields', ['contact' => null, 'useOld' => $isCreate, 'idPrefix' => 'newContact'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-plus"></i> Adicionar contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modais: editar contacto --}}
@foreach($seller->contacts as $contact)
@php $isEdit = $contactFormId === 'contact-edit-' . $contact->id; @endphp
<div class="modal fade" id="contactEditModal-{{ $contact->id }}" tabindex="-1" aria-hidden="true" @if($isEdit && $errors->any()) data-seller-open-on-load @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.sellers.contacts.update', [$seller->id, $contact->id]) }}" method="POST"
                  data-seller-form="contact" data-seller-id="{{ $seller->id }}" data-seller-country="{{ $sellerCountry }}" data-contact-id="{{ $contact->id }}"
                  @if($isEdit && $errors->has('matches')) data-recheck @endif>
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="contact-edit-{{ $contact->id }}">
                <input type="hidden" name="confirm_matches" value="0">
                <div class="modal-header">
                    <h5 class="modal-title">Editar contacto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div data-dup-alert></div>
                    @include('admin.v2.sellers._contact-fields', ['contact' => $contact, 'useOld' => $isEdit, 'idPrefix' => 'editContact' . $contact->id])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-check"></i> Guardar contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
    .seller-contact-row:target, .seller-contact-row:target td { background: var(--bs-warning-bg-subtle, #fff3cd); }
</style>
@endpush

@include('admin.v2.sellers._scripts')
@endsection
