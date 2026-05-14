@extends('layouts.admin-v2')

@section('title', 'Enviar Newsletter — ' . $newsletter->title)

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-newspaper', 'label' => 'Newsletter', 'href' => route('admin.v2.newsletter-management.index')],
        ['icon' => 'bi bi-eye', 'label' => $newsletter->title, 'href' => route('admin.v2.newsletter-management.show', $newsletter->id)],
        ['icon' => 'bi bi-send', 'label' => 'Enviar', 'href' => ''],
    ],
    'title' => 'Enviar Newsletter',
    'subtitle' => $newsletter->title,
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.v2.newsletter-management.doSend', $newsletter->id) }}" method="POST" id="sendForm">
    @csrf
    <div class="row g-3">

        {{-- Client selection panel --}}
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-people"></i>
                        Selecionar Clientes
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary" id="selectedCount">0 / 20 selecionados</span>
                    </div>
                </div>
                <div class="modern-card-body">

                    {{-- Search --}}
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="clientSearch" class="form-control" placeholder="Pesquisar por nome ou email…">
                        </div>
                    </div>

                    {{-- Quick actions --}}
                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllVisible">
                            <i class="bi bi-check2-all"></i> Selecionar visíveis
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                            <i class="bi bi-x-lg"></i> Limpar seleção
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="selectUnsent">
                            <i class="bi bi-envelope"></i> Selecionar só os que não receberam
                        </button>
                    </div>

                    {{-- Client list --}}
                    <div id="clientList" style="max-height: 480px; overflow-y: auto;">
                        @forelse($clients as $client)
                            @php
                                $alreadySent = in_array($client->id, $sentClientIds);
                            @endphp
                            <div class="client-row d-flex align-items-center gap-3 p-2 rounded mb-1 border {{ $alreadySent ? 'bg-light' : '' }}"
                                 data-name="{{ strtolower($client->name) }}"
                                 data-email="{{ strtolower($client->email) }}"
                                 data-sent="{{ $alreadySent ? '1' : '0' }}"
                                 style="cursor: {{ $alreadySent ? 'default' : 'pointer' }}; {{ $alreadySent ? 'opacity:.6;' : '' }}">
                                <input type="checkbox"
                                       name="client_ids[]"
                                       value="{{ $client->id }}"
                                       class="form-check-input client-checkbox flex-shrink-0"
                                       id="client_{{ $client->id }}"
                                       {{ $alreadySent ? 'disabled checked style="pointer-events:none;opacity:.45;"' : '' }}>
                                <label for="client_{{ $client->id }}" class="flex-grow-1 mb-0" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                        <div>
                                            <span class="fw-semibold">{{ $client->name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $client->email }}</small>
                                        </div>
                                        <div>
                                            @if($alreadySent)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Já enviado
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-envelope"></i> Não enviado
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Nenhum cliente com email encontrado.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary + send button --}}
        <div class="col-lg-4">
            <div class="modern-card sticky-top" style="top: 80px;">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-send"></i>
                        Resumo
                    </h5>
                </div>
                <div class="modern-card-body">
                    <ul class="list-unstyled mb-3">
                        <li class="mb-2">
                            <i class="bi bi-newspaper text-primary me-2"></i>
                            <strong>{{ $newsletter->title }}</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope-paper text-info me-2"></i>
                            {{ $newsletter->offers->count() }} oferta(s)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-people text-secondary me-2"></i>
                            {{ $clients->count() }} clientes disponíveis
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            {{ count($sentClientIds) }} já receberam
                        </li>
                    </ul>

                    <hr>

                    <div class="mb-3 text-center">
                        <span class="fs-5 fw-bold text-primary" id="summarySelected">0</span>
                        <span class="text-muted"> cliente(s) selecionado(s)</span>
                        <div class="small text-muted">(máximo 20 por envio)</div>
                    </div>

                    <div id="limitWarning" class="alert alert-warning py-2 small d-none">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Limite de 20 clientes por envio atingido.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn-primary-modern" id="sendBtn" disabled>
                            <i class="bi bi-send me-1"></i>
                            <span>Enviar Newsletter</span>
                        </button>
                        <a href="{{ route('admin.v2.newsletter-management.show', $newsletter->id) }}"
                           class="btn-secondary-modern text-center">
                            <i class="bi bi-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const MAX = 20;
    const checkboxes = document.querySelectorAll('.client-checkbox');
    const countBadge   = document.getElementById('selectedCount');
    const summaryCount = document.getElementById('summarySelected');
    const sendBtn      = document.getElementById('sendBtn');
    const limitWarning = document.getElementById('limitWarning');
    const searchInput  = document.getElementById('clientSearch');

    function getSelected() {
        return document.querySelectorAll('.client-checkbox:not([disabled]):checked').length;
    }

    function updateCounter() {
        const n = getSelected();
        countBadge.textContent   = n + ' / ' + MAX + ' selecionados';
        summaryCount.textContent = n;
        sendBtn.disabled = n === 0;
        limitWarning.classList.toggle('d-none', n < MAX);

        // Disable unchecked boxes when limit reached
        checkboxes.forEach(cb => {
            if (!cb.checked) {
                cb.disabled = n >= MAX;
                cb.closest('.client-row').style.opacity = n >= MAX ? '0.5' : '';
            }
        });
    }

    checkboxes.forEach(cb => {
        if (cb.disabled) return; // skip already-sent
        cb.addEventListener('change', updateCounter);
        // Row click toggles checkbox
        cb.closest('.client-row').addEventListener('click', function (e) {
            if (e.target === cb || e.target.tagName === 'LABEL') return;
            if (cb.disabled) return;
            cb.checked = !cb.checked;
            updateCounter();
        });
    });

    // Search
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.client-row').forEach(row => {
            const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
            row.style.display = match ? '' : 'none';
        });
    });

    // Select all visible
    document.getElementById('selectAllVisible').addEventListener('click', function () {
        let added = getSelected();
        document.querySelectorAll('.client-row:not([style*="display: none"]) .client-checkbox').forEach(cb => {
            if (!cb.checked && added < MAX) {
                cb.checked = true;
                added++;
            }
        });
        updateCounter();
    });

    // Deselect all
    document.getElementById('deselectAll').addEventListener('click', function () {
        checkboxes.forEach(cb => { if (!cb.disabled) cb.checked = false; });
        updateCounter();
    });

    // Select only unsent
    document.getElementById('selectUnsent').addEventListener('click', function () {
        checkboxes.forEach(cb => cb.checked = false);
        let added = 0;
        document.querySelectorAll('.client-row[data-sent="0"]:not([style*="display: none"]) .client-checkbox').forEach(cb => {
            if (added < MAX) {
                cb.checked = true;
                added++;
            }
        });
        updateCounter();
    });

    updateCounter();
})();
</script>
@endpush
@endsection
