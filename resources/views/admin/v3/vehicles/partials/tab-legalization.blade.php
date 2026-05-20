@php
    $legalization = $vehicle->legalization;
    $passos       = \App\Models\Legalization::PASSOS;
    $documentos   = \App\Models\Legalization::DOCUMENTOS;
@endphp

{{-- ── No legalization yet ─────────────────────────────────────────── --}}
@if(!$legalization)
<div class="text-center py-5">
    <i class="bi bi-globe2 fs-1 text-muted opacity-50 d-block mb-3"></i>
    <h5 class="fw-semibold">Processo de legalização não iniciado</h5>
    <p class="text-muted mb-4">Crie o processo de legalização para este veículo importado.</p>
    <form action="{{ route('admin.v3.vehicles.legalization.create', $vehicle->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Iniciar processo de legalização
        </button>
    </form>
</div>

{{-- ── Legalization exists ──────────────────────────────────────────── --}}
@else
@php
    $progress      = $legalization->progressPercent();
    $stepsDone     = count($legalization->steps_completed ?? []);
    $stepsTotal    = count($passos);
    $docsUploaded  = $vehicle->documents->whereIn('tipo', array_keys($documentos))->count();
    $docsTotal     = count($documentos);
    $progressColor = $progress === 100 ? 'success' : ($progress > 0 ? 'info' : 'secondary');
@endphp

{{-- ── Summary cards ──────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2">
                <p class="text-muted small fw-semibold text-uppercase mb-2" style="font-size:.65rem;letter-spacing:.05em">Progresso</p>
                <div class="progress mb-1" style="height:8px">
                    <div class="progress-bar bg-{{ $progressColor }}" style="width:{{ $progress }}%"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">{{ $stepsDone }}/{{ $stepsTotal }} passos</small>
                    <small class="fw-bold text-{{ $progressColor }}">{{ $progress }}%</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2">
                <p class="text-muted small fw-semibold text-uppercase mb-2" style="font-size:.65rem;letter-spacing:.05em">Documentos</p>
                <div class="progress mb-1" style="height:8px">
                    <div class="progress-bar bg-primary" style="width:{{ $docsTotal > 0 ? round($docsUploaded / $docsTotal * 100) : 0 }}%"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">{{ $docsUploaded }}/{{ $docsTotal }}</small>
                    <small class="text-muted">{{ $docsTotal - $docsUploaded }} em falta</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2">
                <p class="text-muted small fw-semibold text-uppercase mb-2" style="font-size:.65rem;letter-spacing:.05em">Dados do Processo</p>
                <div class="row g-1 small">
                    <div class="col-6">
                        <span class="text-muted">Matrícula PT:</span>
                        <span class="fw-semibold font-monospace ms-1">{{ $legalization->matricula ?: '—' }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Homologação:</span>
                        <span class="fw-semibold font-monospace ms-1" style="font-size:.75rem">{{ $legalization->num_homologacao ?: '—' }}</span>
                    </div>
                </div>
                @if($legalization->notas)
                <p class="text-muted small mb-0 mt-1"><i class="bi bi-sticky me-1"></i>{{ $legalization->notas }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Edit legalization fields ────────────────────────────────────── --}}
<details class="mb-4">
    <summary class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-pencil me-1"></i> Editar dados do processo
    </summary>
    <div class="border rounded p-3 mt-2 bg-light">
        <form action="{{ route('admin.v3.vehicles.legalization.save', $vehicle->id) }}" method="POST">
            @csrf
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Matrícula Portuguesa</label>
                    <input type="text" name="matricula" class="form-control form-control-sm"
                           value="{{ $legalization->matricula }}" placeholder="ex: AA-00-BB">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Nº de Homologação Nacional</label>
                    <input type="text" name="num_homologacao" class="form-control form-control-sm"
                           value="{{ $legalization->num_homologacao }}" placeholder="ex: e1*2018/858*00123*00">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Notas</label>
                    <textarea name="notas" class="form-control form-control-sm" rows="2">{{ $legalization->notas }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</details>

<div class="row g-4">

    {{-- ── Passos ──────────────────────────────────────────────────── --}}
    <div class="col-lg-7">
        <h6 class="fw-semibold mb-3"><i class="bi bi-list-check me-1 text-primary"></i> Passos do Processo</h6>
        <div class="border rounded overflow-hidden">
            @foreach($passos as $num => $passo)
            @php
                $done      = $legalization->isStepCompleted($num);
                $hasDocs   = !empty($passo['docs']);
                $blocked   = $num > 1 && !$legalization->isStepCompleted($num - 1);
            @endphp
            <div class="d-flex gap-3 px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ $done ? 'bg-success bg-opacity-5' : '' }}"
                 id="v3-step-row-{{ $num }}">
                {{-- Checkbox --}}
                <div class="pt-1 flex-shrink-0">
                    <input class="form-check-input v3-step-checkbox"
                           type="checkbox"
                           data-step="{{ $num }}"
                           {{ $done ? 'checked' : '' }}
                           {{ $blocked ? 'disabled' : '' }}
                           style="width:20px;height:20px;cursor:{{ $blocked ? 'not-allowed' : 'pointer' }};opacity:{{ $blocked ? '.35' : '1' }}">
                </div>
                {{-- Content --}}
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

                    @if($passo['info'])
                    <div class="mt-1 small text-muted"><i class="bi bi-info-circle me-1"></i>{{ $passo['info'] }}</div>
                    @endif

                    @if($num === 1)
                    <div class="mt-2">
                        @if($legalization->num_homologacao)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                <i class="bi bi-hash me-1"></i>{{ $legalization->num_homologacao }}
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-pencil me-1"></i>Preencher Nº de homologação acima
                            </span>
                        @endif
                    </div>
                    @endif

                    @if($num === 5)
                    <div class="mt-2">
                        @if($legalization->matricula)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                <i class="bi bi-card-text me-1"></i>{{ $legalization->matricula }}
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-pencil me-1"></i>Preencher matrícula acima
                            </span>
                        @endif
                    </div>
                    @endif

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

    {{-- ── Documentos ──────────────────────────────────────────────── --}}
    <div class="col-lg-5">
        <h6 class="fw-semibold mb-3"><i class="bi bi-paperclip me-1 text-secondary"></i> Documentos</h6>
        <div class="border rounded overflow-hidden">
            @foreach($documentos as $slug => $label)
            @php $doc = $vehicle->documents->firstWhere('tipo', $slug); @endphp
            <div class="d-flex align-items-center gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div style="min-width:20px">
                    @if($doc)
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <i class="bi bi-circle text-muted opacity-50"></i>
                    @endif
                </div>
                <div class="flex-grow-1 small">
                    <div class="fw-semibold {{ $doc ? '' : 'text-muted' }}">{{ $label }}</div>
                    @if($doc)
                        <div class="text-muted" style="font-size:.7rem"><i class="bi bi-file-earmark me-1"></i>{{ $doc->nome_original }}</div>
                    @endif
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    @if($doc)
                        <a href="{{ route('admin.v3.vehicles.documents.download', [$vehicle->id, $doc->id]) }}"
                           class="btn btn-sm btn-outline-primary" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                        <form action="{{ route('admin.v3.vehicles.documents.destroy', [$vehicle->id, $doc->id]) }}"
                              method="POST" class="d-inline" onsubmit="return confirm('Remover este documento?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="return_tab" value="legalization">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @else
                        {{-- Auto-submit upload --}}
                        <form action="{{ route('admin.v3.vehicles.documents.upload', $vehicle->id) }}"
                              method="POST" enctype="multipart/form-data" class="d-inline">
                            @csrf
                            <input type="hidden" name="tipo" value="{{ $slug }}">
                            <input type="hidden" name="return_tab" value="legalization">
                            <input type="file" name="ficheiro" id="legdoc-{{ $slug }}"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none"
                                   onchange="this.closest('form').submit()">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="document.getElementById('legdoc-{{ $slug }}').click()"
                                    title="Carregar">
                                <i class="bi bi-upload"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Step toggle JS ──────────────────────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.v3-step-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const step = this.dataset.step;
            const checked = this.checked;

            fetch('{{ route("admin.v3.vehicles.legalization.toggle-step", $vehicle->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ step: parseInt(step) }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    checkbox.checked = !checked;
                    return;
                }
                // Reload to reflect new progress/step states
                window.location.reload();
            })
            .catch(() => { checkbox.checked = !checked; });
        });
    });
});
</script>
@endif
