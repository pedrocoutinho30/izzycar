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
            <div class="spinner-border spinner-border-sm text-primary me-1"></div>A carregar…
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
</style>

<script>
function submitPhotoForm() {
    document.getElementById('uploadProgress').classList.remove('d-none');
    document.getElementById('photoUploadForm').submit();
}

function handlePhotoDrop(event) {
    event.preventDefault();
    document.getElementById('dropZone').classList.remove('dragging');
    const input = document.getElementById('photoInput');
    const dt = event.dataTransfer;
    // Transfer files to input via DataTransfer
    input.files = dt.files;
    submitPhotoForm();
}

// Drag-to-reorder with Sortable.js
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('photoGrid');
    if (!grid) return;

    if (typeof Sortable !== 'undefined') {
        Sortable.create(grid, {
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
    } else {
        // Sortable not available, skip drag-to-reorder silently
    }
});
</script>
