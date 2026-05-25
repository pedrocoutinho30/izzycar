{{-- ── Upload zone ─────────────────────────────────────────────────── --}}
<div class="mb-4">
    <form action="{{ route('admin.v3.vehicles.photos.upload', $vehicle->id) }}"
          method="POST" enctype="multipart/form-data" id="photoUploadForm">
        @csrf
        <div class="v3-drop-zone border-dashed rounded p-4 text-center" id="dropZone"
             ondragover="event.preventDefault(); this.classList.add('dragging')"
             ondragleave="this.classList.remove('dragging')"
             ondrop="handlePhotoDrop(event)">
            <i class="bi bi-cloud-upload fs-3 text-primary mb-2 d-block"></i>
            <p class="fw-semibold mb-1">Arraste fotos aqui ou clique para selecionar</p>
            <p class="text-muted small mb-2">JPG, PNG, WebP — máx. 5MB por foto — múltiplos ficheiros</p>
            <label class="btn btn-outline-primary btn-sm">
                <i class="bi bi-folder-open me-1"></i> Selecionar Fotos
                <input type="file" name="fotos[]" id="photoInput" accept="image/*"
                       multiple style="display:none" onchange="submitPhotoForm()">
            </label>
        </div>
        <div id="uploadProgress" class="mt-2 small text-muted d-none">
            <div class="spinner-border spinner-border-sm text-primary me-1"></div>
            <span id="uploadProgressText">A carregar…</span>
        </div>
    </form>
</div>

{{-- ── Photo grid ──────────────────────────────────────────────────── --}}
@if($vehicle->photos->count())
<p class="text-muted small mb-2">
    <i class="bi bi-cursor-fill me-1"></i> Arraste para reordenar &nbsp;·&nbsp;
    <i class="bi bi-star-fill text-warning"></i> = foto de capa
</p>

<div id="photoGrid" class="row g-2 mb-3">
    @foreach($vehicle->photos->sortBy('order_position') as $photo)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-photo-id="{{ $photo->id }}">
        <div class="v3-photo-card {{ $photo->is_cover ? 'is-cover' : '' }}">
            <div class="v3-photo-img-wrap">
                <img src="{{ asset('storage/' . $photo->path) }}"
                     alt="Foto"
                     class="v3-photo-img">
                @if($photo->is_cover)
                <span class="v3-cover-badge">
                    <i class="bi bi-star-fill text-warning"></i> Capa
                </span>
                @endif
            </div>
            <div class="v3-photo-actions">
                @if(!$photo->is_cover)
                <form action="{{ route('admin.v3.vehicles.photos.cover', [$vehicle->id, $photo->id]) }}"
                      method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-xs btn-outline-warning" title="Definir como capa">
                        <i class="bi bi-star" style="font-size:.7rem"></i>
                    </button>
                </form>
                @endif
                <form action="{{ route('admin.v3.vehicles.photos.destroy', [$vehicle->id, $photo->id]) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Eliminar esta foto?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-outline-danger" title="Eliminar">
                        <i class="bi bi-trash" style="font-size:.7rem"></i>
                    </button>
                </form>
                @php
                    $backupRelPath = preg_replace('/(\.([^.\/]+))$/', '_original$1', $photo->path);
                    $hasBackup = \Illuminate\Support\Facades\Storage::disk('public')->exists($backupRelPath);
                @endphp
                <button class="btn btn-xs btn-outline-secondary" title="Ajustar crop"
                        data-photo-id="{{ $photo->id }}"
                        data-img-url="{{ asset('storage/' . $photo->path) }}"
                        data-focal-x="{{ $photo->focal_x ?? 50 }}"
                        data-focal-y="{{ $photo->focal_y ?? 50 }}"
                        data-has-backup="{{ $hasBackup ? 'true' : 'false' }}"
                        onclick="openFocalEditor(this)">
                    <i class="bi bi-crop" style="font-size:.7rem"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<p class="text-muted small">Sem fotos carregadas.</p>
@endif

<style>
.v3-drop-zone { border: 2px dashed #ced4da; transition: all .2s; cursor: pointer; }
.v3-drop-zone.dragging,
.v3-drop-zone:hover { border-color: #4A6FA5; background: rgba(74,111,165,.05); }
.v3-photo-card { border-radius: 8px; overflow: hidden; background: #f8f9fa;
                 border: 2px solid transparent; transition: border-color .2s; }
.v3-photo-card.is-cover { border-color: #ffc107; }
.v3-photo-img-wrap { position: relative; padding-top: 75%; }
.v3-photo-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.v3-cover-badge { position: absolute; top: 4px; left: 4px;
                  background: rgba(0,0,0,.6); color: #fff; border-radius: 4px;
                  padding: 1px 5px; font-size: .65rem; }
.v3-photo-actions { display: flex; gap: 4px; padding: 4px; justify-content: center; }
.btn-xs { padding: .2rem .4rem; font-size: .7rem; line-height: 1; }
.sortable-ghost { opacity: .4; }
#photoGrid [data-photo-id] { cursor: grab; }
#photoGrid [data-photo-id]:active { cursor: grabbing; }
</style>

<script>
const _uploadUrl  = '{{ route("admin.v3.vehicles.photos.upload", $vehicle->id) }}';
const _returnUrl  = '{{ route("admin.v3.vehicles.edit", $vehicle->id) }}?tab=photos';

async function _compressImage(file) {
    return new Promise(resolve => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = function () {
            URL.revokeObjectURL(url);
            const MAX = 1920;
            let w = img.width, h = img.height;
            if (w > MAX || h > MAX) {
                if (w >= h) { h = Math.round(h * MAX / w); w = MAX; }
                else        { w = Math.round(w * MAX / h); h = MAX; }
            }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            canvas.toBlob(blob => resolve(blob || file), 'image/jpeg', 0.85);
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

async function uploadFiles(files) {
    const csrf      = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const progress  = document.getElementById('uploadProgress');
    const text      = document.getElementById('uploadProgressText');
    progress.classList.remove('d-none');
    let ok = 0;
    const total = files.length;
    for (const file of files) {
        text.textContent = `A carregar ${ok + 1} de ${total}…`;
        let blob;
        try { blob = await _compressImage(file); } catch { blob = file; }
        const fd = new FormData();
        fd.append('fotos[]', blob, file.name.replace(/\.[^.]+$/, '.jpg'));
        fd.append('_token', csrf);
        try {
            const res = await fetch(_uploadUrl, { method: 'POST', body: fd });
            if (res.ok) ok++;
        } catch (e) { console.error('Upload error:', e); }
    }
    text.textContent = `${ok} de ${total} carregada(s). A recarregar…`;
    window.location.href = _returnUrl;
}

function submitPhotoForm() {
    const input = document.getElementById('photoInput');
    if (input.files.length) uploadFiles(Array.from(input.files));
    input.value = '';
}

function handlePhotoDrop(event) {
    event.preventDefault();
    document.getElementById('dropZone').classList.remove('dragging');
    if (event.dataTransfer.files.length) uploadFiles(Array.from(event.dataTransfer.files));
}

// Drag-to-reorder: inicializado pelo bloco de scripts abaixo, após SortableJS carregar
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
// Re-init Sortable after it loads (tab may already be visible)
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('photoGrid');
    if (!grid || grid._sortable) return;
    grid._sortable = Sortable.create(grid, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            const order = [...grid.querySelectorAll('[data-photo-id]')]
                           .map(el => el.dataset.photoId);
            fetch('{{ route("admin.v3.vehicles.photos.reorder", $vehicle->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ order }),
            }).catch(()=>{});
        }
    });
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush

{{-- ── Crop Editor Modal ──────────────────────────────────────────── --}}
<div class="modal fade" id="focalModal" tabindex="-1" aria-labelledby="focalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="focalModalLabel">
                    <i class="bi bi-crop me-2"></i>Editor de Crop
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div style="max-height:480px;overflow:hidden;background:#000;">
                            <img id="focalImg" style="max-width:100%;display:block;" alt="Crop editor">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column gap-3">
                        <div>
                            <p class="text-muted small mb-1 fw-semibold">Pré-visualização</p>
                            <div id="cropPreview" style="width:100%;height:170px;overflow:hidden;border:1px solid #dee2e6;border-radius:8px;"></div>
                        </div>
                        <div>
                            <p class="text-muted small mb-1 fw-semibold">Proporção</p>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn btn-outline-secondary crop-ratio-btn active" data-ratio="free">Livre</button>
                                <button type="button" class="btn btn-outline-secondary crop-ratio-btn" data-ratio="1.7778">16:9</button>
                                <button type="button" class="btn btn-outline-secondary crop-ratio-btn" data-ratio="1.3333">4:3</button>
                                <button type="button" class="btn btn-outline-secondary crop-ratio-btn" data-ratio="1">1:1</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted small mb-1 fw-semibold">Dimensões</p>
                            <small id="cropDimensionsInfo" class="text-muted font-monospace">—</small>
                        </div>
                        <div id="cropRestoreSection" class="d-none">
                            <button type="button" class="btn btn-sm btn-outline-warning w-100" id="focalRestoreBtn">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Repor original
                            </button>
                            <small class="text-muted d-block mt-1">Remove o crop e restaura a imagem original.</small>
                        </div>
                        <div>
                            <span id="focalSaveMsg" class="text-success small fw-semibold d-none">
                                <i class="bi bi-check-circle-fill me-1"></i>Guardado com sucesso!
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <small class="text-muted me-auto">Arraste e redimensione a área de crop. A imagem original é substituída.</small>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-sm btn-primary" id="focalSaveBtn">
                    <i class="bi bi-floppy me-1"></i>Guardar crop
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(function () {
    const _cropBase = '{{ url("gestao/v3/vehicles/" . $vehicle->id . "/photos") }}';
    const _csrf     = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    let _cropId     = null;
    let _cropBtn    = null;
    let _cropper    = null;

    window.openFocalEditor = function (btn) {
        _cropBtn = btn;
        _cropId  = btn.dataset.photoId;

        const img = document.getElementById('focalImg');
        if (_cropper) { _cropper.destroy(); _cropper = null; }

        img.src = btn.dataset.imgUrl + '?t=' + Date.now();

        document.getElementById('focalSaveMsg').classList.add('d-none');
        document.getElementById('cropDimensionsInfo').textContent = '—';

        // Show/hide restore section
        const hasBackup = btn.dataset.hasBackup === 'true';
        document.getElementById('cropRestoreSection').classList.toggle('d-none', !hasBackup);

        // Reset ratio buttons
        document.querySelectorAll('.crop-ratio-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.crop-ratio-btn[data-ratio="free"]').classList.add('active');

        bootstrap.Modal.getOrCreateInstance(document.getElementById('focalModal')).show();

        document.getElementById('focalModal').addEventListener('shown.bs.modal', function init() {
            document.getElementById('focalModal').removeEventListener('shown.bs.modal', init);
            _cropper = new Cropper(img, {
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 1,
                aspectRatio: NaN,
                preview: '#cropPreview',
                responsive: true,
                guides: true,
                center: true,
                highlight: true,
                toggleDragModeOnDblclick: false,
                crop: function (e) {
                    const d = e.detail;
                    document.getElementById('cropDimensionsInfo').textContent =
                        Math.round(d.width) + ' × ' + Math.round(d.height) + ' px  (x: ' + Math.round(d.x) + ', y: ' + Math.round(d.y) + ')';
                },
            });
        });
    };

    // Aspect ratio buttons
    document.querySelectorAll('.crop-ratio-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.crop-ratio-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (_cropper) {
                const r = this.dataset.ratio;
                _cropper.setAspectRatio(r === 'free' ? NaN : parseFloat(r));
            }
        });
    });

    // Save
    document.getElementById('focalSaveBtn').addEventListener('click', function () {
        if (!_cropper) return;
        const btn  = this;
        const data = _cropper.getData(true);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>A guardar…';

        fetch(_cropBase + '/' + _cropId + '/crop', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ x: data.x, y: data.y, width: data.width, height: data.height }),
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar crop';
            if (json.success) {
                // Update thumbnail in grid
                if (_cropBtn) {
                    _cropBtn.dataset.imgUrl    = json.new_url;
                    _cropBtn.dataset.focalX    = 50;
                    _cropBtn.dataset.focalY    = 50;
                    _cropBtn.dataset.hasBackup = 'true';
                    const card = _cropBtn.closest('[data-photo-id]');
                    if (card) {
                        const thumbImg = card.querySelector('.v3-photo-img');
                        if (thumbImg) {
                            thumbImg.src = json.new_url;
                            thumbImg.style.objectPosition = '50% 50%';
                        }
                    }
                }
                document.getElementById('cropRestoreSection').classList.remove('d-none');
                document.getElementById('focalSaveMsg').classList.remove('d-none');
                // Reload cropper with the new (cropped) image
                _cropper.replace(json.new_url);
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar crop';
        });
    });

    // Restore original
    document.getElementById('focalRestoreBtn').addEventListener('click', function () {
        if (!confirm('Repor a imagem original? O crop actual será descartado.')) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>A repor…';

        fetch(_cropBase + '/' + _cropId + '/restore', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Repor original';
            if (json.success) {
                if (_cropBtn) {
                    _cropBtn.dataset.imgUrl    = json.new_url;
                    _cropBtn.dataset.hasBackup = 'false';
                    const card = _cropBtn.closest('[data-photo-id]');
                    if (card) {
                        const thumbImg = card.querySelector('.v3-photo-img');
                        if (thumbImg) { thumbImg.src = json.new_url; thumbImg.style.objectPosition = '50% 50%'; }
                    }
                }
                document.getElementById('cropRestoreSection').classList.add('d-none');
                document.getElementById('focalSaveMsg').classList.add('d-none');
                if (_cropper) _cropper.replace(json.new_url);
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Repor original';
        });
    });

    // Destroy cropper on modal close
    document.getElementById('focalModal').addEventListener('hidden.bs.modal', function () {
        if (_cropper) { _cropper.destroy(); _cropper = null; }
    });
}());
</script>
