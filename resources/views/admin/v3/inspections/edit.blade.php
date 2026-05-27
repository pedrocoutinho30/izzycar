@extends('layouts.admin-v2')

@section('title', 'Inspeção #' . $inspection->id)

@section('content')
@include('components.admin.page-header', [
    'title' => 'Inspeção #' . $inspection->id,
    'subtitle' => 'Revise o estado do veículo, anexe media e finalize a inspeção.',
    'breadcrumbs' => [
        ['label' => 'Backoffice', 'href' => route('admin.v2.dashboard'), 'icon' => 'bi-house'],
        ['label' => 'Inspeções', 'href' => route('admin.v3.inspections.index'), 'icon' => 'bi-clipboard-check'],
        ['label' => 'Inspeção #' . $inspection->id, 'icon' => 'bi-pencil-square'],
    ],
])

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="modern-card h-100">
            <div class="modern-card-body text-center">
                <div class="text-muted small text-uppercase">Pontuação</div>
                <div class="display-6 fw-bold text-primary">{{ number_format($summary['percentage'] ?? 0, 0) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="modern-card h-100">
            <div class="modern-card-body text-center">
                <div class="text-muted small text-uppercase">Verificados</div>
                <div class="display-6 fw-bold">{{ $summary['verified_items'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="modern-card h-100">
            <div class="modern-card-body text-center">
                <div class="text-muted small text-uppercase">Problemas</div>
                <div class="display-6 fw-bold text-danger">{{ $summary['problem_items'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="modern-card h-100">
            <div class="modern-card-body text-center">
                <div class="text-muted small text-uppercase">Críticos</div>
                <div class="display-6 fw-bold text-danger">{{ $summary['critical_issues'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="modern-card h-100">
            <div class="modern-card-header">
                <h5 class="modern-card-title mb-0"><i class="bi bi-car-front me-1"></i> Dados da Viatura</h5>
            </div>
            <div class="modern-card-body">
                <form id="inspectionBaseForm" action="{{ route('admin.v3.inspections.update', $inspection) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="inspectionStatusInput" name="status" value="{{ old('status', $inspection->status) }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Marca</label>
                            <select id="inspectionBrandSelect" name="brand" class="form-select inspection-auto-input" onchange="inspectionLoadModels()">
                                <option value="">— Selecionar marca —</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->name }}" data-models='@json($brand->models->map(fn($m) => ['name' => $m->name, 'submodels' => $m->submodels->pluck('name')]))' @selected(old('brand', $inspection->brand) === $brand->name)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Modelo</label>
                            <select id="inspectionModelSelect" name="model" class="form-select inspection-auto-input" onchange="inspectionLoadSubmodels()">
                                <option value="">— Selecionar modelo —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Submodelo</label>
                            <select id="inspectionSubmodelSelect" name="sub_model" class="form-select inspection-auto-input">
                                <option value="">— Nenhum —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Versão</label>
                            <input type="text" name="version" class="form-control inspection-auto-input" value="{{ old('version', $inspection->version) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ano</label>
                            <input type="number" name="year" class="form-control inspection-auto-input" value="{{ old('year', $inspection->year) }}" min="1900" max="{{ now()->year + 2 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quilómetros</label>
                            <input type="number" name="kilometers" class="form-control inspection-auto-input" value="{{ old('kilometers', $inspection->kilometers) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Combustível</label>
                            <select id="inspectionFuelSelect" name="fuel" class="form-select inspection-auto-input">
                                <option value="">—</option>
                                @foreach(['Gasolina', 'Diesel', 'Híbrido', 'Elétrico'] as $fuel)
                                    <option value="{{ $fuel }}" @selected(old('fuel', $inspection->fuel) === $fuel)>{{ $fuel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Potência</label>
                            <input type="number" name="power" class="form-control inspection-auto-input" value="{{ old('power', $inspection->power) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Transmissão</label>
                            <select name="transmission" class="form-select inspection-auto-input">
                                <option value="">—</option>
                                <option value="Manual" @selected(old('transmission', $inspection->transmission) === 'Manual')>Manual</option>
                                <option value="Automática" @selected(old('transmission', $inspection->transmission) === 'Automática')>Automática</option>
                                <option value="CVT" @selected(old('transmission', $inspection->transmission) === 'CVT')>CVT</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tração</label>
                            <select name="traction" class="form-select inspection-auto-input">
                                <option value="">—</option>
                                <option value="FWD" @selected(old('traction', $inspection->traction) === 'FWD')>FWD</option>
                                <option value="RWD" @selected(old('traction', $inspection->traction) === 'RWD')>RWD</option>
                                <option value="AWD/4x4" @selected(old('traction', $inspection->traction) === 'AWD/4x4')>AWD/4x4</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">VIN</label>
                            <input type="text" name="vin" class="form-control inspection-auto-input" value="{{ old('vin', $inspection->vin) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Matrícula</label>
                            <input type="text" name="registration" class="form-control inspection-auto-input" value="{{ old('registration', $inspection->registration) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas</label>
                            <textarea name="notes" class="form-control inspection-auto-input" rows="4">{{ old('notes', $inspection->notes) }}</textarea>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="inspectionForceSave('draft')"><i class="bi bi-cloud-arrow-down me-1"></i>Guardar agora</button>
                            <button type="button" class="btn btn-primary" onclick="inspectionForceSave('completed')"><i class="bi bi-check2-circle me-1"></i>Concluir inspeção</button>
                            @if(in_array($inspection->status, ['completed', 'converted']))
                                <a href="{{ route('admin.v3.inspections.report', $inspection) }}" class="btn btn-outline-info">
                                    <i class="bi bi-file-earmark-text me-1"></i>Ver Relatório
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <form action="{{ route('admin.v3.inspections.convert', $inspection) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100" @disabled($inspection->v3_vehicle_id)>
                        <i class="bi bi-car-front-fill me-1"></i>{{ $inspection->v3_vehicle_id ? 'V3 criado' : 'Criar Veículo V3' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="modern-card h-100">
            <div class="modern-card-header">
                <h5 class="modern-card-title mb-0"><i class="bi bi-lightbulb me-1"></i> Recomendações Automáticas</h5>
            </div>
            <div class="modern-card-body">
                @if($criticalEntries->count())
                    <div class="alert alert-danger">
                        <strong>Críticos:</strong>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            @foreach($criticalEntries as $entry)
                                <span class="badge bg-danger rounded-pill">{{ $entry->item?->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($problemEntries->count())
                    <div class="alert alert-warning">
                        <strong>Problemas encontrados:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($problemEntries as $entry)
                                <li>{{ $entry->item?->name }} @if($entry->notes) - {{ $entry->notes }} @endif</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="alert alert-info mb-0">
                    {{ $summary['recommendations'] ?: 'Sem recomendações automáticas adicionais por agora.' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- General vehicle photos --}}
<div class="modern-card mb-4">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-images me-2"></i>Fotos do Veículo</div>
        <span class="badge bg-secondary">{{ $generalMedia->count() }}</span>
    </div>
    <div class="modern-card-body">
        {{-- Upload form --}}
        <form action="{{ route('admin.v3.inspections.general-media.store', $inspection) }}" method="POST" enctype="multipart/form-data" class="mb-3">
            @csrf
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <label class="btn btn-sm btn-outline-primary mb-0">
                    <i class="bi bi-upload me-1"></i>Selecionar fotos
                    <input type="file" name="files[]" multiple accept="image/*,video/*" class="d-none" onchange="this.closest('form').submit()">
                </label>
                <button type="button" class="btn btn-sm btn-outline-secondary inspection-camera-btn" data-upload-url="{{ route('admin.v3.inspections.general-media.store', $inspection) }}">
                    <i class="bi bi-camera me-1"></i>Câmara
                </button>
                <span class="text-muted small">Fotos gerais do veículo inspecionado</span>
            </div>
        </form>

        {{-- Photo grid --}}
        @if($generalMedia->count())
            <div class="row g-2" id="generalMediaGrid">
                @foreach($generalMedia as $gm)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="position-relative">
                            @if($gm->type === 'image')
                                <img src="{{ Storage::url($gm->path) }}" class="img-fluid rounded" style="aspect-ratio:4/3;object-fit:cover;width:100%" alt="">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="aspect-ratio:4/3">
                                    <i class="bi bi-camera-video fs-3 text-muted"></i>
                                </div>
                            @endif
                            <form action="{{ route('admin.v3.inspections.general-media.destroy', [$inspection, $gm]) }}" method="POST" class="position-absolute top-0 end-0 m-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm py-0 px-1" style="font-size:.7rem" onclick="return confirm('Remover foto?')">&times;</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-3 small"><i class="bi bi-camera me-1"></i>Nenhuma foto geral adicionada.</div>
        @endif
    </div>
</div>

<form id="inspectionAutosaveForm" action="{{ route('admin.v3.inspections.update', $inspection) }}" method="POST" class="d-none">
    @csrf
    @method('PUT')
</form>

<div class="inspection-categories">
    @foreach($template->categories as $category)
        @php
            $categoryItems = $category->items->filter(fn ($item) => $item->appliesToFuel($inspection->fuel));
            $visibleCount = $categoryItems->count();
        @endphp
        <div class="modern-card mb-3 inspection-category" data-category-card data-fuels='@json($category->applies_to_fuels)' {{ $visibleCount ? '' : 'style=display:none' }}>
            <button class="inspection-category-toggle w-100 text-start border-0 bg-transparent p-0" type="button" data-bs-toggle="collapse" data-bs-target="#inspectionCategory{{ $category->id }}" aria-expanded="true">
                <div class="modern-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi {{ $category->icon ?? 'bi-clipboard-check' }} fs-5 text-primary"></i>
                        <div>
                            <h5 class="modern-card-title mb-0">{{ $category->name }}</h5>
                            <div class="small text-muted">{{ $visibleCount }} itens aplicáveis</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down text-muted"></i>
                </div>
            </button>
            <div id="inspectionCategory{{ $category->id }}" class="collapse show">
                <div class="modern-card-body">
                    <div class="row g-3">
                        @foreach($category->items as $item)
                            @php
                                $entry = $entriesByItem->get($item->id);
                                $entryKey = $entry?->id;
                                $currentStatus = old('entries.' . $entryKey . '.status', $entry?->status ?? 'nao_verificado');
                                $currentPriority = old('entries.' . $entryKey . '.priority', $entry?->priority ?? 'baixa');
                            @endphp

                            <div class="col-12 col-xl-6 inspection-item" data-item-card data-fuels='@json($item->applies_to_fuels)' {{ $item->appliesToFuel($inspection->fuel) ? '' : 'style=display:none' }}>
                                <div class="border rounded-4 p-3 h-100 bg-white shadow-sm">
                                    <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                                        <div>
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <h6 class="mb-0">{{ $item->name }}</h6>
                                                @if($item->is_critical)
                                                    <span class="badge bg-danger rounded-pill">Crítico</span>
                                                @endif
                                            </div>
                                            @if($item->description)
                                                <div class="small text-muted">{{ $item->description }}</div>
                                            @endif
                                        </div>
                                        <span class="badge bg-{{ match($currentStatus) { 'ok' => 'success', 'atencao' => 'warning', 'problema' => 'danger', default => 'secondary' } }} rounded-pill">
                                            {{ \App\Services\VehicleInspectionService::STATUSES[$currentStatus] ?? 'Não verificado' }}
                                        </span>
                                    </div>

                                    <input type="hidden" class="inspection-auto-input" name="entries[{{ $entryKey }}][status]" value="{{ $currentStatus }}">
                                    <input type="hidden" class="inspection-auto-input" name="entries[{{ $entryKey }}][priority]" value="{{ $currentPriority }}">
                                    <input type="hidden" class="inspection-auto-input" name="entries[{{ $entryKey }}][notes]" value="{{ old('entries.' . $entryKey . '.notes', $entry?->notes) }}">

                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Estado</div>
                                        @php
                                            $statusColors = ['nao_verificado' => 'secondary', 'ok' => 'success', 'atencao' => 'warning', 'problema' => 'danger'];
                                        @endphp
                                        <div class="btn-group w-100 inspection-button-group" role="group" data-field="status" data-entry-id="{{ $entryKey }}">
                                            @foreach(\App\Services\VehicleInspectionService::STATUSES as $key => $label)
                                                @php
                                                    $sc = $statusColors[$key] ?? 'secondary';
                                                    $sa = $currentStatus === $key;
                                                    $scls = $sa ? "btn-{$sc}" : "btn-outline-{$sc}";
                                                    $stxt = ($sa && $sc === 'warning') ? ' text-dark' : '';
                                                @endphp
                                                <button type="button" class="btn btn-sm inspection-choice-btn {{ $scls }}{{ $stxt }}" data-choice-value="{{ $key }}">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Prioridade</div>
                                        @php
                                            $priorityColors = ['baixa' => 'success', 'media' => 'warning', 'alta' => 'danger'];
                                            $priorityGrey = in_array($currentStatus, ['ok', 'nao_verificado']);
                                        @endphp
                                        <div class="btn-group w-100 inspection-button-group" role="group" data-field="priority" data-entry-id="{{ $entryKey }}">
                                            @foreach(\App\Services\VehicleInspectionService::PRIORITIES as $key => $label)
                                                @php
                                                    $pa = $currentPriority === $key;
                                                    if ($priorityGrey) {
                                                        $pcls = $pa ? 'btn-secondary' : 'btn-outline-secondary';
                                                        $ptxt = '';
                                                    } else {
                                                        $pc = $priorityColors[$key] ?? 'secondary';
                                                        $pcls = $pa ? "btn-{$pc}" : "btn-outline-{$pc}";
                                                        $ptxt = ($pa && $pc === 'warning') ? ' text-dark' : '';
                                                    }
                                                @endphp
                                                <button type="button" class="btn btn-sm inspection-choice-btn {{ $pcls }}{{ $ptxt }}" data-choice-value="{{ $key }}">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="small text-muted">Pontos: <strong>{{ number_format($entry?->score() ?? 0, 1) }}</strong></div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#entryModal{{ $entryKey }}">
                                            <i class="bi bi-chat-left-text me-1"></i>Observações / Media
                                        </button>
                                    </div>
                                </div>

                                <div class="modal fade" id="entryModal{{ $entryKey }}" tabindex="-1" aria-labelledby="entryModalLabel{{ $entryKey }}" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title" id="entryModalLabel{{ $entryKey }}">{{ $item->name }}</h5>
                                                    @if($item->description)
                                                        <div class="small text-muted">{{ $item->description }}</div>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small text-muted">Observações</label>
                                                    <textarea class="form-control inspection-auto-input" rows="4" name="entries[{{ $entryKey }}][notes]" placeholder="Notas sobre este ponto…">{{ old('entries.' . $entryKey . '.notes', $entry?->notes) }}</textarea>
                                                </div>

                                                <div class="inspection-media-block">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <strong class="small text-uppercase text-muted">Media</strong>
                                                        <span class="badge bg-light text-dark rounded-pill">{{ $entry?->media?->count() ?? 0 }}</span>
                                                    </div>

                                                    <form action="{{ route('admin.v3.inspections.media.store', [$inspection, $entry]) }}" method="POST" enctype="multipart/form-data" class="inspection-upload-form mb-3">
                                                        @csrf
                                                        <div class="inspection-upload-box border rounded-3 p-3 bg-light">
                                                            <input type="file" name="files[]" class="form-control inspection-file-input" multiple accept="image/*,video/*" capture="environment">
                                                            <div class="inspection-file-queue mt-3"></div>
                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-upload me-1"></i>Enviar ficheiros</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary inspection-camera-btn" data-upload-url="{{ route('admin.v3.inspections.media.store', [$inspection, $entry]) }}">
                                                                    <i class="bi bi-camera me-1"></i>Tirar foto
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    <div class="inspection-media-grid" data-media-grid data-reorder-url="{{ route('admin.v3.inspections.media.reorder', [$inspection, $entry]) }}">
                                                        @forelse($entry?->media?->sortBy('sort_order') ?? [] as $media)
                                                            <div class="inspection-media-card border rounded-3 p-2 bg-white" data-media-id="{{ $media->id }}">
                                                                <div class="inspection-media-preview mb-2">
                                                                    @if($media->type === 'video')
                                                                        <video controls class="w-100 rounded-3" style="height:140px;object-fit:cover;background:#000">
                                                                            <source src="{{ asset('storage/' . $media->path) }}">
                                                                        </video>
                                                                    @else
                                                                        <img src="{{ asset('storage/' . $media->path) }}" class="w-100 rounded-3" style="height:140px;object-fit:cover" alt="{{ $media->original_name }}">
                                                                    @endif
                                                                </div>
                                                                <div class="small fw-semibold text-truncate" title="{{ $media->original_name }}">{{ $media->original_name }}</div>
                                                                <div class="small text-muted">{{ $media->description ?: 'Sem descrição' }}</div>
                                                                <form action="{{ route('admin.v3.inspections.media.destroy', [$inspection, $entry, $media]) }}" method="POST" class="mt-2">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Eliminar media?')"><i class="bi bi-trash me-1"></i>Remover</button>
                                                                </form>
                                                            </div>
                                                        @empty
                                                            <div class="text-muted small">Sem media anexada.</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="modal fade" id="inspectionCameraModal" tabindex="-1" aria-labelledby="inspectionCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inspectionCameraModalLabel"><i class="bi bi-camera me-1"></i>Tirar foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-3">
                <div id="cameraError" class="alert alert-danger d-none mb-3"></div>
                <div class="text-center">
                    <video id="cameraStream" autoplay playsinline muted class="w-100 rounded-3" style="max-height:420px;object-fit:cover;background:#000;display:block"></video>
                    <canvas id="cameraCanvas" class="d-none"></canvas>
                    <img id="cameraPreview" class="w-100 rounded-3 d-none" style="max-height:420px;object-fit:contain" alt="Pré-visualização">
                </div>
                <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary" id="cameraCaptureBtn"><i class="bi bi-camera me-1"></i>Capturar</button>
                    <button type="button" class="btn btn-outline-secondary d-none" id="cameraRetakeBtn"><i class="bi bi-arrow-counterclockwise me-1"></i>Nova foto</button>
                    <button type="button" class="btn btn-success d-none" id="cameraSaveBtn"><i class="bi bi-check2 me-1"></i>Guardar foto</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .inspection-media-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    }
    .inspection-media-card {
        cursor: grab;
    }
    .sortable-ghost {
        opacity: .4;
    }
    .inspection-upload-box {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    }
    .inspection-button-group.btn-group .inspection-choice-btn {
        flex: 1 1 0;
        min-width: 0;
        font-size: 0.78rem;
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function () {
    const baseForm = document.getElementById('inspectionBaseForm');
    const fuelSelect = document.getElementById('inspectionFuelSelect');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const updateUrl = '{{ route('admin.v3.inspections.update', $inspection) }}';

    let autosaveTimer = null;

    function saveInspection(status) {
        const formData = new FormData(baseForm);
        formData.set('_method', 'PUT');
        formData.set('status', status || document.getElementById('inspectionStatusInput').value || 'draft');

        document.querySelectorAll('.inspection-auto-input').forEach((input) => {
            formData.set(input.name, input.value);
        });

        return fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        }).then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw data;
            }
            document.getElementById('inspectionStatusInput').value = status || document.getElementById('inspectionStatusInput').value;
            return data;
        });
    }

    function queueAutosave() {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            saveInspection('in_progress').catch(() => {});
        }, 900);
    }

    window.inspectionForceSave = function (status) {
        saveInspection(status).then(() => {
            if (status === 'completed') {
                window.location.href = '{{ route('admin.v3.inspections.report', $inspection) }}';
            } else {
                sessionStorage.setItem('insp_scroll', window.scrollY);
                window.location.reload();
            }
        }).catch(() => {});
    };

    // Restore scroll position after forced save/reload
    (function () {
        const saved = sessionStorage.getItem('insp_scroll');
        if (saved !== null) {
            sessionStorage.removeItem('insp_scroll');
            // Use requestAnimationFrame to wait for layout to settle
            requestAnimationFrame(() => requestAnimationFrame(() => window.scrollTo(0, parseInt(saved, 10))));
        }
    })();

    function applyFuelVisibility() {
        const selectedFuel = fuelSelect.value;

        document.querySelectorAll('[data-item-card]').forEach((el) => {
            const fuels = JSON.parse(el.dataset.fuels || 'null');
            const visible = !fuels || !fuels.length || !selectedFuel || fuels.includes(selectedFuel);
            el.style.display = visible ? '' : 'none';
        });

        document.querySelectorAll('[data-category-card]').forEach((card) => {
            const visibleItems = [...card.querySelectorAll('[data-item-card]')].filter((el) => window.getComputedStyle(el).display !== 'none').length;
            card.style.display = visibleItems ? '' : 'none';
        });
    }

    document.querySelectorAll('.inspection-auto-input').forEach((input) => {
        input.addEventListener('change', queueAutosave);
        input.addEventListener('input', queueAutosave);
    });

    const STATUS_COLORS = { nao_verificado: 'secondary', ok: 'success', atencao: 'warning', problema: 'danger' };
    const PRIORITY_COLORS = { baixa: 'success', media: 'warning', alta: 'danger' };

    function applyStatusButtons(group, activeValue) {
        group.querySelectorAll('.inspection-choice-btn').forEach((btn) => {
            const key = btn.dataset.choiceValue;
            const color = STATUS_COLORS[key] || 'secondary';
            const isActive = key === activeValue;
            btn.className = `btn btn-sm inspection-choice-btn ${isActive ? `btn-${color}` : `btn-outline-${color}`}${color === 'warning' && isActive ? ' text-dark' : ''}`;
        });
    }

    function applyPriorityButtons(group, activeValue, currentStatus) {
        const grey = currentStatus === 'ok' || currentStatus === 'nao_verificado';
        group.querySelectorAll('.inspection-choice-btn').forEach((btn) => {
            const key = btn.dataset.choiceValue;
            const isActive = key === activeValue;
            if (grey) {
                btn.className = `btn btn-sm inspection-choice-btn ${isActive ? 'btn-secondary' : 'btn-outline-secondary'}`;
            } else {
                const color = PRIORITY_COLORS[key] || 'secondary';
                btn.className = `btn btn-sm inspection-choice-btn ${isActive ? `btn-${color}` : `btn-outline-${color}`}${color === 'warning' && isActive ? ' text-dark' : ''}`;
            }
        });
    }

    document.querySelectorAll('.inspection-choice-btn').forEach((button) => {
        button.addEventListener('click', function () {
            const group = this.closest('.inspection-button-group');
            if (!group) return;

            const field = group.dataset.field;
            const entryId = group.dataset.entryId;
            const value = this.dataset.choiceValue;

            const hiddenInput = document.querySelector(`.inspection-auto-input[name="entries[${entryId}][${field}]"]`);
            if (!hiddenInput) return;

            hiddenInput.value = value;

            if (field === 'status') {
                applyStatusButtons(group, value);
                const priorityGroup = document.querySelector(`.inspection-button-group[data-field="priority"][data-entry-id="${entryId}"]`);
                if (priorityGroup) {
                    const priorityInput = document.querySelector(`.inspection-auto-input[name="entries[${entryId}][priority]"]`);
                    applyPriorityButtons(priorityGroup, priorityInput?.value, value);
                }
            } else {
                const statusInput = document.querySelector(`.inspection-auto-input[name="entries[${entryId}][status]"]`);
                applyPriorityButtons(group, value, statusInput?.value);
            }

            queueAutosave();
        });
    });

    fuelSelect.addEventListener('change', () => {
        applyFuelVisibility();
        queueAutosave();
    });

    applyFuelVisibility();

    const savedBrand = @json(old('brand', $inspection->brand));
    const savedModel = @json(old('model', $inspection->model));
    const savedSubmodel = @json(old('sub_model', $inspection->sub_model));
    let bootstrap = true;

    function inspectionLoadModels() {
        const brandSel = document.getElementById('inspectionBrandSelect');
        const modelSel = document.getElementById('inspectionModelSelect');
        const subSel = document.getElementById('inspectionSubmodelSelect');
        const opt = brandSel.options[brandSel.selectedIndex];
        const models = JSON.parse(opt?.dataset.models || '[]');

        modelSel.innerHTML = '<option value="">— Selecionar modelo —</option>';
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.name;
            option.textContent = model.name;
            option.dataset.submodels = JSON.stringify(model.submodels);
            modelSel.appendChild(option);
        });

        modelSel.disabled = models.length === 0;
        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subSel.disabled = true;

        if (bootstrap && savedModel) {
            modelSel.value = savedModel;
            inspectionLoadSubmodels();
        }
    }

    function inspectionLoadSubmodels() {
        const modelSel = document.getElementById('inspectionModelSelect');
        const subSel = document.getElementById('inspectionSubmodelSelect');
        const opt = modelSel.options[modelSel.selectedIndex];
        const subs = JSON.parse(opt?.dataset.submodels || '[]');

        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subs.forEach(submodel => {
            const option = document.createElement('option');
            option.value = submodel;
            option.textContent = submodel;
            subSel.appendChild(option);
        });
        subSel.disabled = subs.length === 0;

        if (bootstrap && savedSubmodel) {
            subSel.value = savedSubmodel;
        }
    }

    window.inspectionLoadModels = inspectionLoadModels;
    window.inspectionLoadSubmodels = inspectionLoadSubmodels;

    document.addEventListener('DOMContentLoaded', function () {
        if (savedBrand) {
            document.getElementById('inspectionBrandSelect').value = savedBrand;
            inspectionLoadModels();
        }

        bootstrap = false;

        document.querySelectorAll('[data-media-grid]').forEach((grid) => {
            if (grid._sortable) return;

            grid._sortable = Sortable.create(grid, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.inspection-media-card',
                onEnd: function () {
                    const mediaIds = [...grid.querySelectorAll('[data-media-id]')].map(el => el.dataset.mediaId);
                    fetch(grid.dataset.reorderUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ media_ids: mediaIds }),
                    });
                }
            });
        });

        document.querySelectorAll('.inspection-upload-form').forEach((form) => {
            const input = form.querySelector('.inspection-file-input');
            const queue = form.querySelector('.inspection-file-queue');

            input.addEventListener('change', () => {
                queue.innerHTML = '';

                [...input.files].forEach((file) => {
                    const item = document.createElement('div');
                    item.className = 'border rounded-3 p-2 bg-white mb-2';

                    const isImage = file.type.startsWith('image/');
                    const previewUrl = URL.createObjectURL(file);

                    item.innerHTML = `
                        <div class="d-flex gap-2 align-items-start">
                            <div style="width:64px;height:64px;flex:0 0 64px;overflow:hidden;border-radius:10px;background:#f1f3f5" class="border">
                                ${isImage ? `<img src="${previewUrl}" style="width:100%;height:100%;object-fit:cover">` : `<div class="h-100 d-flex align-items-center justify-content-center text-muted"><i class="bi bi-file-earmark-play fs-4"></i></div>`}
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold text-truncate">${file.name}</div>
                                <input type="text" name="descriptions[]" class="form-control form-control-sm mt-1" placeholder="Descrição da media">
                            </div>
                        </div>
                    `;

                    queue.appendChild(item);
                });
            });
        });

        const cameraModalEl = document.getElementById('inspectionCameraModal');
        const cameraVideo = document.getElementById('cameraStream');
        const cameraCanvas = document.getElementById('cameraCanvas');
        const cameraPreview = document.getElementById('cameraPreview');
        const cameraCaptureBtn = document.getElementById('cameraCaptureBtn');
        const cameraRetakeBtn = document.getElementById('cameraRetakeBtn');
        const cameraSaveBtn = document.getElementById('cameraSaveBtn');
        const cameraError = document.getElementById('cameraError');

        let cameraStream = null;
        let cameraBlob = null;
        let cameraUploadUrl = null;

        function stopCameraStream() {
            if (cameraStream) {
                cameraStream.getTracks().forEach((track) => track.stop());
                cameraStream = null;
            }
        }

        function startCameraStream() {
            return navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then((stream) => {
                    cameraStream = stream;
                    cameraVideo.srcObject = stream;
                    cameraError.classList.add('d-none');
                    cameraCaptureBtn.classList.remove('d-none');
                })
                .catch((err) => {
                    cameraError.textContent = 'Não foi possível aceder à câmara: ' + err.message;
                    cameraError.classList.remove('d-none');
                    cameraCaptureBtn.classList.add('d-none');
                });
        }

        document.querySelectorAll('.inspection-camera-btn').forEach((btn) => {
            btn.addEventListener('click', function () {
                cameraUploadUrl = this.dataset.uploadUrl;
                cameraBlob = null;

                cameraVideo.style.display = 'block';
                cameraPreview.classList.add('d-none');
                cameraCaptureBtn.classList.remove('d-none');
                cameraRetakeBtn.classList.add('d-none');
                cameraSaveBtn.classList.add('d-none');
                cameraError.classList.add('d-none');
                cameraSaveBtn.disabled = false;
                cameraSaveBtn.innerHTML = '<i class="bi bi-check2 me-1"></i>Guardar foto';

                window.bootstrap.Modal.getOrCreateInstance(cameraModalEl).show();
                startCameraStream();
            });
        });

        cameraModalEl.addEventListener('hidden.bs.modal', stopCameraStream);

        cameraCaptureBtn.addEventListener('click', function () {
            cameraCanvas.width = cameraVideo.videoWidth || 1280;
            cameraCanvas.height = cameraVideo.videoHeight || 720;
            cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0);

            cameraCanvas.toBlob((blob) => {
                cameraBlob = blob;
                cameraPreview.src = URL.createObjectURL(blob);
                cameraVideo.style.display = 'none';
                cameraPreview.classList.remove('d-none');
                cameraCaptureBtn.classList.add('d-none');
                cameraRetakeBtn.classList.remove('d-none');
                cameraSaveBtn.classList.remove('d-none');
                stopCameraStream();
            }, 'image/jpeg', 0.92);
        });

        cameraRetakeBtn.addEventListener('click', function () {
            cameraBlob = null;
            cameraPreview.classList.add('d-none');
            cameraVideo.style.display = 'block';
            cameraCaptureBtn.classList.remove('d-none');
            cameraRetakeBtn.classList.add('d-none');
            cameraSaveBtn.classList.add('d-none');
            startCameraStream();
        });

        cameraSaveBtn.addEventListener('click', function () {
            if (!cameraBlob || !cameraUploadUrl) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>A guardar…';

            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const filename = `foto_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.jpg`;

            const fd = new FormData();
            fd.append('_token', csrf);
            fd.append('files[]', cameraBlob, filename);

            fetch(cameraUploadUrl, { method: 'POST', body: fd })
                .then((response) => {
                    if (response.ok || response.redirected) {
                        window.bootstrap.Modal.getInstance(cameraModalEl)?.hide();
                        window.location.reload();
                        return;
                    }

                    return response.text().then((text) => {
                        throw new Error(text || 'Upload falhou');
                    });
                })
                .catch((err) => {
                    cameraError.textContent = 'Erro ao guardar: ' + (err.message || 'erro desconhecido');
                    cameraError.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Guardar foto';
                });
        });
    });
})();
</script>
@endpush

@endsection