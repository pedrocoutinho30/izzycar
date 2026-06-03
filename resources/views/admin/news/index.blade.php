@extends('layouts.admin-v2')
@section('title', 'Notícias')

@section('content')
<div class="admin-content">

  @include('components.admin.page-header', [
    'breadcrumbs' => [
      ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
      ['icon' => 'bi bi-newspaper',  'label' => 'Notícias'],
    ],
    'title'       => 'Notícias',
    'subtitle'    => 'Gere os artigos do blog',
    'actionHref'  => route('admin.news.create'),
    'actionLabel' => '+ Novo Artigo',
  ])

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
      <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Stats --}}
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="an-stat">
        <div class="an-stat__icon" style="background:#eff6ff;color:#3b5bdb"><i class="bi bi-newspaper fs-4"></i></div>
        <div><div class="an-stat__n">{{ $total }}</div><div class="an-stat__l">Total</div></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="an-stat">
        <div class="an-stat__icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle fs-4"></i></div>
        <div><div class="an-stat__n">{{ $published }}</div><div class="an-stat__l">Publicados</div></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="an-stat">
        <div class="an-stat__icon" style="background:#fefce8;color:#ca8a04"><i class="bi bi-pencil-square fs-4"></i></div>
        <div><div class="an-stat__n">{{ $drafts }}</div><div class="an-stat__l">Rascunhos</div></div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="modern-card mb-4">
    <form method="GET" class="d-flex flex-wrap gap-3 align-items-end">
      <div class="flex-grow-1" style="min-width:200px">
        <label class="form-label small text-muted mb-1">Pesquisar</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0"
                 placeholder="Título do artigo..." value="{{ request('search') }}">
        </div>
      </div>
      <div style="min-width:160px">
        <label class="form-label small text-muted mb-1">Estado</label>
        <select name="status" class="form-select">
          <option value="">Todos</option>
          <option value="Publicado" {{ request('status') === 'Publicado' ? 'selected' : '' }}>Publicado</option>
          <option value="Rascunho"  {{ request('status') === 'Rascunho'  ? 'selected' : '' }}>Rascunho</option>
        </select>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary-modern px-4">Filtrar</button>
        @if(request()->anyFilled(['search','status']))
          <a href="{{ route('admin.news.index') }}" class="btn btn-secondary-modern px-3">
            <i class="bi bi-x-lg"></i>
          </a>
        @endif
      </div>
    </form>
  </div>

  {{-- List --}}
  <div class="modern-card p-0" style="overflow:hidden">
    @if($articles->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="bi bi-newspaper fs-1 d-block mb-3 opacity-25"></i>
        <p class="mb-3">Ainda não há artigos. Cria o primeiro!</p>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary-modern">
          <i class="bi bi-plus-lg me-1"></i> Novo Artigo
        </a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover mb-0 an-table">
          <thead>
            <tr>
              <th style="width:54px"></th>
              <th>Artigo</th>
              <th style="width:130px">Data</th>
              <th style="width:120px">Estado</th>
              <th style="width:110px" class="text-end pe-4">Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($articles as $a)
            <tr>
              <td class="ps-3 pe-0">
                <div class="an-thumb">
                  @if($a->cover_image)
                    <img src="{{ asset('storage/'.$a->cover_image) }}"
                         onerror="this.src='{{ asset('img/logo-simples.png') }}';" alt="">
                  @else
                    <div class="an-thumb__empty"><i class="bi bi-image"></i></div>
                  @endif
                </div>
              </td>
              <td>
                <a href="{{ route('admin.news.edit', $a->id) }}" class="an-title">
                  {{ $a->title }}
                </a>
                <div class="an-meta mt-1">
                  <span><i class="bi bi-link-45deg"></i>/noticias/{{ $a->slug }}</span>
                  <span><i class="bi bi-clock"></i>{{ $a->read_time }} min</span>
                  @if($a->gallery && count($a->gallery))
                    <span><i class="bi bi-images"></i>{{ count($a->gallery) }} fotos</span>
                  @endif
                </div>
              </td>
              <td class="text-muted small">
                {{ $a->published_at ? $a->published_at->format('d/m/Y') : '—' }}
              </td>
              <td>
                <button class="an-badge {{ $a->status === 'Publicado' ? 'an-badge--pub' : 'an-badge--draft' }}"
                        data-id="{{ $a->id }}">
                  <span class="an-badge__dot"></span>
                  <span class="an-badge__text">{{ $a->status }}</span>
                </button>
              </td>
              <td class="pe-3">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('admin.news.edit', $a->id) }}"
                     class="an-btn" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="{{ route('frontend.news-details', $a->slug) }}"
                     target="_blank" class="an-btn" title="Ver no site">
                    <i class="bi bi-eye"></i>
                  </a>
                  <button type="button" class="an-btn an-btn--danger an-delete"
                          data-id="{{ $a->id }}" data-title="{{ addslashes($a->title) }}"
                          title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($articles->hasPages())
        <div class="px-4 py-3 border-top">{{ $articles->links() }}</div>
      @endif
    @endif
  </div>

</div>

{{-- Delete modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Eliminar artigo</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-0">
        <p class="text-muted mb-0">Tens a certeza que queres eliminar <strong id="deleteTitle"></strong>?
        Esta acção é irreversível e remove também as imagens.</p>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary-modern" data-bs-dismiss="modal">Cancelar</button>
        <form id="deleteForm" method="POST">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger-modern">
            <i class="bi bi-trash me-1"></i> Eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Status toggle
document.querySelectorAll('.an-badge').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var id   = this.dataset.id;
    var self = this;
    fetch('{{ url("/gestao/news") }}/' + id + '/toggle-status', {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      var isPub = d.status === 'Publicado';
      self.className = 'an-badge ' + (isPub ? 'an-badge--pub' : 'an-badge--draft');
      self.querySelector('.an-badge__text').textContent = d.status;
    });
  });
});

// Delete confirm
document.querySelectorAll('.an-delete').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.getElementById('deleteTitle').textContent = '"' + this.dataset.title + '"';
    document.getElementById('deleteForm').action = '{{ url("/gestao/news") }}/' + this.dataset.id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  });
});
</script>

<style>
.an-stat { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:1.1rem 1.4rem; display:flex; align-items:center; gap:1.1rem; }
.an-stat__icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.an-stat__n { font-size:1.6rem; font-weight:800; color:#111; line-height:1; }
.an-stat__l { font-size:.73rem; color:#9ca3af; margin-top:.15rem; }

.an-table thead th { font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; font-weight:600; padding:.8rem 1.25rem; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
.an-table tbody td { padding:.85rem 1.25rem; vertical-align:middle; border-bottom:1px solid #f3f4f6; }
.an-table tbody tr:last-child td { border-bottom:none; }
.an-table tbody tr:hover td { background:#fafafa; }

.an-thumb { width:46px; height:42px; border-radius:8px; overflow:hidden; }
.an-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.an-thumb__empty { width:100%; height:100%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#d1d5db; font-size:.85rem; }

.an-title { font-weight:700; color:#111; text-decoration:none; font-size:.88rem; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.an-title:hover { color:#6e0707; }
.an-meta { display:flex; gap:.8rem; flex-wrap:wrap; font-size:.7rem; color:#9ca3af; }
.an-meta span { display:flex; align-items:center; gap:.2rem; }

.an-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .8rem; border-radius:100px; border:none; cursor:pointer; font-size:.73rem; font-weight:600; transition:.15s; }
.an-badge--pub { background:#f0fdf4; color:#16a34a; } .an-badge--pub:hover { background:#dcfce7; }
.an-badge--draft { background:#fefce8; color:#ca8a04; } .an-badge--draft:hover { background:#fef9c3; }
.an-badge__dot { width:6px; height:6px; border-radius:50%; background:currentColor; flex-shrink:0; }

.an-btn { width:30px; height:30px; padding:0; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; text-decoration:none; font-size:.8rem; cursor:pointer; transition:.15s; }
.an-btn:hover { background:#e5e7eb; color:#111; }
.an-btn--danger:hover { background:#fee2e2; color:#dc2626; border-color:#fecaca; }
</style>
@endpush
