@extends('layouts.admin-v2')

@section('title', 'Legalização — ' . $legalization->marca . ' ' . $legalization->modelo)

@section('content')

@php
    $progress     = $legalization->progressPercent();
    $stepsTotal   = count($passos);
    $stepsDone    = count($legalization->steps_completed ?? []);
    $docsTotal    = count($documentos);
    $docsUploaded = $legalization->documents->count();
    $progressColor = $progress === 100 ? 'success' : ($progress > 0 ? 'info' : 'secondary');
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',          'label' => 'Dashboard',     'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-check',  'label' => 'Legalizações',  'href' => route('admin.legalizations.index')],
        ['icon' => 'bi bi-car-front',           'label' => $legalization->marca . ' ' . $legalization->modelo, 'href' => ''],
    ],
    'title'    => $legalization->marca . ' ' . $legalization->modelo,
    'subtitle' => ($legalization->client?->name ?? 'Sem cliente') . ' · ' . $legalization->combustivel,
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Erro ao carregar documento:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ================================================================
     HEADER CARDS
     ================================================================ --}}
<div class="row g-3 mb-4">
    {{-- Progresso --}}
    <div class="col-md-4">
        <div class="modern-card h-100 mb-0">
            <div class="modern-card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Progresso</span>
                    <span class="badge bg-{{ $progressColor }} bg-opacity-15 text-{{ $progressColor }} border border-{{ $progressColor }} border-opacity-25 fw-semibold">
                        {{ $progress }}%
                    </span>
                </div>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar bg-{{ $progressColor }}" style="width:{{ $progress }}%"></div>
                </div>
                <div class="text-muted small">{{ $stepsDone }} de {{ $stepsTotal }} passos concluídos</div>
            </div>
        </div>
    </div>

    {{-- Documentos --}}
    <div class="col-md-4">
        <div class="modern-card h-100 mb-0">
            <div class="modern-card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Documentos</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold">
                        {{ $docsUploaded }} / {{ $docsTotal }}
                    </span>
                </div>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar bg-primary"
                         style="width:{{ $docsTotal > 0 ? round($docsUploaded / $docsTotal * 100) : 0 }}%"></div>
                </div>
                <div class="text-muted small">{{ $docsTotal - $docsUploaded }} documento(s) em falta</div>
            </div>
        </div>
    </div>

    {{-- Acções rápidas --}}
    <div class="col-md-4">
        <div class="modern-card h-100 mb-0">
            <div class="modern-card-body d-flex flex-column justify-content-center gap-2">
                <a href="{{ route('admin.legalizations.edit', $legalization) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Editar dados
                </a>
                <form action="{{ route('admin.legalizations.destroy', $legalization) }}" method="POST"
                      onsubmit="return confirm('Eliminar esta legalização e todos os documentos?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i> Eliminar legalização
                    </button>
                </form>

                @if($legalization->matricula || $legalization->num_homologacao)
                <div class="border rounded p-2 small bg-light mt-1">
                    @if($legalization->matricula)
                    <div class="d-flex align-items-center gap-2 {{ $legalization->num_homologacao ? 'mb-1' : '' }}">
                        <i class="bi bi-card-text text-muted"></i>
                        <span class="text-muted">Matrícula:</span>
                        <span class="fw-semibold font-monospace">{{ $legalization->matricula }}</span>
                    </div>
                    @endif
                    @if($legalization->num_homologacao)
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-hash text-muted"></i>
                        <span class="text-muted">Homologação:</span>
                        <span class="fw-semibold font-monospace" style="font-size:.8rem">{{ $legalization->num_homologacao }}</span>
                    </div>
                    @endif
                </div>
                @endif

                @if($legalization->notas)
                <div class="border rounded p-2 small text-muted bg-light">
                    <i class="bi bi-sticky me-1"></i>{{ $legalization->notas }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     LAYOUT PRINCIPAL: PASSOS + DOCUMENTOS
     ================================================================ --}}
<div class="row g-4">

    {{-- ============================================================
         COLUNA ESQUERDA — PASSOS DE LEGALIZAÇÃO
         ============================================================ --}}
    <div class="col-lg-7">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-list-check me-1"></i> Passos do Processo</h5>
            </div>
            <div class="modern-card-body p-0">
                @foreach($passos as $num => $passo)
                @php
                    $done       = $legalization->isStepCompleted($num);
                    $hasDocs    = !empty($passo['docs']);
                    $allDocsOk  = $hasDocs && collect($passo['docs'])->every(fn($d) => $legalization->hasDocument($d));
                    $someDocsOk = $hasDocs && collect($passo['docs'])->some(fn($d) => $legalization->hasDocument($d));
                @endphp
                <div class="step-item d-flex gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ $done ? 'step-done' : '' }}"
                     id="step-row-{{ $num }}">

                    {{-- Checkbox --}}
                    <div class="pt-1">
                        <div class="step-check form-check" style="min-width:20px">
                            @php $bloqueado = $num > 1 && !$legalization->isStepCompleted($num - 1); @endphp
                            <input class="form-check-input step-checkbox"
                                   type="checkbox"
                                   id="step-{{ $num }}"
                                   data-step="{{ $num }}"
                                   {{ $done ? 'checked' : '' }}
                                   {{ $bloqueado ? 'disabled' : '' }}
                                   style="width:20px;height:20px;cursor:{{ $bloqueado ? 'not-allowed' : 'pointer' }};opacity:{{ $bloqueado ? '.35' : '1' }}">
                        </div>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-1">
                                    Passo {{ $num }}
                                </span>
                                <span class="fw-semibold {{ $done ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $passo['titulo'] }}
                                </span>
                            </div>
                            @if($passo['link'])
                            <a href="{{ $passo['link'] }}" target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-primary text-nowrap">
                                <i class="bi bi-box-arrow-up-right me-1"></i>{{ $passo['link_label'] }}
                            </a>
                            @endif
                        </div>

                        {{-- Info extra --}}
                        @if($passo['info'])
                        <div class="mt-1 small text-muted">
                            <i class="bi bi-info-circle me-1"></i>{{ $passo['info'] }}
                        </div>
                        @endif

                        {{-- Nº de homologação (passo 1) --}}
                        @if($num === 1)
                        <div class="mt-2">
                            @if($legalization->num_homologacao)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <i class="bi bi-hash me-1"></i>Homologação: <span class="font-monospace">{{ $legalization->num_homologacao }}</span>
                                </span>
                            @else
                                <a href="{{ route('admin.legalizations.edit', $legalization) }}" class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 text-decoration-none">
                                    <i class="bi bi-pencil me-1"></i>Preencher Nº de homologação
                                </a>
                            @endif
                        </div>
                        @endif

                        {{-- Matrícula (passo 5) --}}
                        @if($num === 5)
                        <div class="mt-2">
                            @if($legalization->matricula)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <i class="bi bi-card-text me-1"></i>Matrícula: <span class="font-monospace">{{ $legalization->matricula }}</span>
                                </span>
                            @else
                                <a href="{{ route('admin.legalizations.edit', $legalization) }}" class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 text-decoration-none">
                                    <i class="bi bi-pencil me-1"></i>Preencher matrícula
                                </a>
                            @endif
                        </div>
                        @endif

                        {{-- Documentos necessários --}}
                        @if($hasDocs)
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            @foreach($passo['docs'] as $docSlug)
                            @php $uploaded = $legalization->hasDocument($docSlug); @endphp
                            <span class="badge {{ $uploaded ? 'bg-transparent text-success border border-success' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' }}"
                                  title="{{ $documentos[$docSlug] ?? $docSlug }}">
                                <i class="bi {{ $uploaded ? 'bi-check-circle-fill' : 'bi-x-circle' }} me-1"></i>
                                {{ Str::limit($documentos[$docSlug] ?? $docSlug, 35) }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============================================================
         COLUNA DIREITA — DOCUMENTOS
         ============================================================ --}}
    <div class="col-lg-5">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-paperclip me-1"></i> Documentos</h5>
            </div>
            <div class="modern-card-body p-0">
                @foreach($documentos as $slug => $label)
                @php
                    $doc = $legalization->documents->firstWhere('tipo', $slug);
                @endphp
                <div class="doc-item d-flex align-items-center gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">

                    {{-- Ícone estado --}}
                    <div style="min-width:24px">
                        @if($doc)
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        @else
                            <i class="bi bi-circle text-muted fs-5 opacity-50"></i>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-grow-1 small">
                        <div class="fw-semibold {{ $doc ? '' : 'text-muted' }}">{{ $label }}</div>
                        @if($doc)
                        <div class="text-muted" style="font-size:.78rem">
                            <i class="bi bi-file-earmark me-1"></i>{{ $doc->nome_original }}
                        </div>
                        @endif
                    </div>

                    {{-- Acções --}}
                    <div class="d-flex gap-1 flex-shrink-0">
                        @if($doc)
                            <a href="{{ route('admin.legalizations.download-document', [$legalization, $doc]) }}"
                               class="btn btn-sm btn-outline-primary" title="Descarregar">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="{{ route('admin.legalizations.delete-document', [$legalization, $doc]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Remover este documento?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="openUpload('{{ $slug }}', '{{ addslashes($label) }}')"
                                    title="Carregar documento">
                                <i class="bi bi-upload"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ================================================================
     MODAL DE UPLOAD
     ================================================================ --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload me-1"></i> Carregar documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.legalizations.upload-document', $legalization) }}"
                  method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <input type="hidden" name="tipo" id="uploadTipo" value="{{ old('tipo') }}">
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="uploadLabel"></p>

                    @if($errors->has('ficheiro'))
                    <div class="alert alert-danger py-2 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first('ficheiro') }}
                    </div>
                    @endif
                    @if($errors->has('tipo'))
                    <div class="alert alert-danger py-2 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first('tipo') }}
                    </div>
                    @endif

                    <label class="form-label fw-semibold">Ficheiro <span class="text-danger">*</span></label>
                    <input type="file" name="ficheiro" class="form-control {{ $errors->has('ficheiro') ? 'is-invalid' : '' }}"
                           accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <div class="form-text">Formatos aceites: PDF, JPG, PNG, WebP · Máximo 10 MB</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-1"></i> Carregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .step-item { transition: border-left .2s, background .2s; border-left: 3px solid transparent; }
    .step-item:hover { background: rgba(0,0,0,.02); }
    .step-item.step-done { border-left-color: var(--admin-success); }
    .doc-item { transition: background .2s; }
    .doc-item:hover { background: rgba(0,0,0,.02); }
    .step-checkbox:checked { background-color: var(--admin-success); border-color: var(--admin-success); }
    .tracking-wide { letter-spacing: .05em; }
</style>
@endpush

@push('scripts')
<script>
const TOGGLE_URL  = "{{ route('admin.legalizations.toggle-step', $legalization) }}";
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));

// Re-abrir modal se houve erro de validação no upload
@if($errors->any() && old('tipo'))
document.addEventListener('DOMContentLoaded', function () {
    const tipoAnterior = '{{ old('tipo') }}';
    const labels = @json(collect($documentos));
    document.getElementById('uploadTipo').value  = tipoAnterior;
    document.getElementById('uploadLabel').textContent = labels[tipoAnterior] ?? tipoAnterior;
    uploadModal.show();
});
@endif

// ---------------------------------------------------------------
// Toggle passos via AJAX
// ---------------------------------------------------------------
document.querySelectorAll('.step-checkbox').forEach(cb => {
    cb.addEventListener('change', function () {
        const step = this.dataset.step;
        const row  = document.getElementById('step-row-' + step);

        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ step: parseInt(step) }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                this.checked = !this.checked; // reverte
                showToast('Passo bloqueado', data.error, 'danger');
                return;
            }

            const done = data.completed.includes(parseInt(step));
            row.classList.toggle('step-done', done);

            const label = row.querySelector('span.fw-semibold');
            if (label) {
                label.classList.toggle('text-decoration-line-through', done);
                label.classList.toggle('text-muted', done);
            }

            // Actualiza disabled nos checkboxes seguintes
            updateCheckboxLocks(data.completed);

            // Atualiza barra de progresso
            document.querySelectorAll('.progress-bar').forEach((b, i) => {
                if (i === 0) b.style.width = data.progress + '%';
            });

            // Toast quando tarefa é criada automaticamente
            if (data.task_created) {
                showToast('Tarefa criada automaticamente', 'Tarefa "Entregar Modelo 9 IMT" adicionada com prazo em 30 dias e lembrete em 15 dias.', 'success');
            }
        })
        .catch(() => {
            this.checked = !this.checked; // reverte em caso de erro
            alert('Erro ao actualizar o passo. Tente novamente.');
        });
    });
});

// ---------------------------------------------------------------
// Actualiza o estado locked/unlocked de todos os checkboxes
// ---------------------------------------------------------------
function updateCheckboxLocks(completed) {
    document.querySelectorAll('.step-checkbox').forEach(cb => {
        const s = parseInt(cb.dataset.step);
        if (s === 1) return;
        const prevDone = completed.includes(s - 1);
        cb.disabled = !prevDone && !completed.includes(s);
        cb.style.opacity  = cb.disabled ? '.35' : '1';
        cb.style.cursor   = cb.disabled ? 'not-allowed' : 'pointer';
    });
}

// ---------------------------------------------------------------
// Abrir modal de upload
// ---------------------------------------------------------------
function openUpload(slug, label) {
    document.getElementById('uploadTipo').value = slug;
    document.getElementById('uploadLabel').textContent = label;
    uploadModal.show();
}

// ---------------------------------------------------------------
// Toast helper
// ---------------------------------------------------------------
function showToast(title, message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
        document.body.appendChild(container);
    }

    const icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const colors = { success: 'text-success', danger: 'text-danger', info: 'text-info' };

    const el = document.createElement('div');
    el.className = 'toast show align-items-center border-0 shadow';
    el.style.minWidth = '320px';
    el.innerHTML = `
        <div class="toast-header">
            <i class="bi ${icons[type] ?? icons.info} ${colors[type] ?? ''} me-2"></i>
            <strong class="me-auto">${title}</strong>
            <button type="button" class="btn-close btn-close-sm ms-2" onclick="this.closest('.toast').remove()"></button>
        </div>
        <div class="toast-body small">${message}</div>
    `;
    container.appendChild(el);
    setTimeout(() => el.remove(), 6000);
}
</script>
@endpush
