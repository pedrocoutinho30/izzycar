@php
    $predefinedDocs = \App\Models\Legalization::DOCUMENTOS;
    // Index uploaded docs by tipo
    $uploadedByType = $vehicle->documents->whereNotNull('tipo')->keyBy('tipo');
    $customDocs     = $vehicle->documents->whereNull('tipo');
@endphp

{{-- ── Documentos Predefinidos ──────────────────────────────────────── --}}
<h6 class="fw-semibold mb-3"><i class="bi bi-file-earmark-check me-1 text-primary"></i> Documentos de Legalização</h6>

<div class="row g-3 mb-4">
    @foreach($predefinedDocs as $key => $label)
    @php $uploaded = $uploadedByType->get($key); @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-{{ $uploaded ? 'success' : 'light' }}" style="border-width:2px!important">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <p class="fw-semibold small mb-0 text-truncate me-2">{{ $label }}</p>
                    @if($uploaded)
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                    @else
                        <i class="bi bi-circle text-muted flex-shrink-0"></i>
                    @endif
                </div>

                @if($uploaded)
                    <div class="d-flex align-items-center gap-1 mt-1">
                        <span class="text-muted small text-truncate flex-grow-1" title="{{ $uploaded->nome_original }}" style="font-size:.7rem">
                            {{ Str::limit($uploaded->nome_original, 28) }}
                        </span>
                        <a href="{{ route('admin.v3.vehicles.documents.download', [$vehicle->id, $uploaded->id]) }}"
                           class="btn btn-sm py-0 px-1 btn-outline-secondary" title="Download">
                            <i class="bi bi-download" style="font-size:.75rem"></i>
                        </a>
                        <form action="{{ route('admin.v3.vehicles.documents.destroy', [$vehicle->id, $uploaded->id]) }}"
                              method="POST" class="d-inline" onsubmit="return confirm('Eliminar este documento?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm py-0 px-1 btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash" style="font-size:.75rem"></i>
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Auto-submit on file select --}}
                    <form action="{{ route('admin.v3.vehicles.documents.upload', $vehicle->id) }}"
                          method="POST" enctype="multipart/form-data" class="mt-1">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $key }}">
                        <input type="file" name="ficheiro" id="file-{{ $key }}"
                               accept=".pdf,.jpg,.jpeg,.png,.webp"
                               style="display:none"
                               onchange="this.closest('form').submit()">
                        <button type="button" onclick="document.getElementById('file-{{ $key }}').click()"
                                class="btn btn-sm btn-outline-primary py-0 w-100">
                            <i class="bi bi-upload me-1"></i> Carregar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Outros Documentos ────────────────────────────────────────────── --}}
<h6 class="fw-semibold mb-3"><i class="bi bi-paperclip me-1 text-secondary"></i> Outros Documentos</h6>

@if($customDocs->count())
<div class="list-group list-group-flush mb-3">
    @foreach($customDocs as $doc)
    <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <i class="bi bi-file-earmark{{ Str::endsWith($doc->nome_original, '.pdf') ? '-pdf text-danger' : ' text-secondary' }} fs-5 flex-shrink-0"></i>
            <div class="overflow-hidden">
                <div class="text-truncate small fw-semibold">{{ $doc->nome_original }}</div>
                @if($doc->titulo)
                    <div class="text-muted" style="font-size:.7rem">{{ $doc->titulo }}</div>
                @endif
            </div>
        </div>
        <div class="d-flex gap-1 flex-shrink-0">
            <a href="{{ route('admin.v3.vehicles.documents.download', [$vehicle->id, $doc->id]) }}"
               class="btn btn-sm btn-outline-secondary" title="Download">
                <i class="bi bi-download"></i>
            </a>
            <form action="{{ route('admin.v3.vehicles.documents.destroy', [$vehicle->id, $doc->id]) }}"
                  method="POST" class="d-inline" onsubmit="return confirm('Eliminar este documento?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@else
<p class="text-muted small mb-3">Sem outros documentos.</p>
@endif

{{-- Upload form for custom docs --}}
<div class="border rounded p-3 bg-light">
    <h6 class="fw-semibold mb-2 small">Adicionar outro documento</h6>
    <form action="{{ route('admin.v3.vehicles.documents.upload', $vehicle->id) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" name="titulo" class="form-control form-control-sm"
                       placeholder="Título / descrição do documento" required>
            </div>
            <div class="col-md-5">
                <input type="file" name="ficheiro" class="form-control form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png,.webp" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-upload me-1"></i> Carregar
                </button>
            </div>
            <div class="col-12">
                <small class="text-muted">PDF, JPG, PNG, WebP — máx. 20MB</small>
            </div>
        </div>
    </form>
</div>
