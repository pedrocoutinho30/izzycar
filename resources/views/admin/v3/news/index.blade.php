@extends('layouts.admin-v2')
@section('title', 'Notícias')

@section('content')
<div class="admin-content">

  @include('components.admin.page-header', [
    'breadcrumbs' => [
      ['icon'=>'bi bi-house-door','label'=>'Dashboard','href'=>route('admin.v2.dashboard')],
      ['icon'=>'bi bi-newspaper','label'=>'Notícias'],
    ],
    'title'       => 'Notícias',
    'subtitle'    => 'Gere os artigos do blog',
    'actionHref'  => route('admin.v3.news.create'),
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
      <div class="ni-stat">
        <div class="ni-stat__icon" style="background:#eff6ff;color:#3b5bdb"><i class="bi bi-newspaper fs-5"></i></div>
        <div><div class="ni-stat__n">{{ $total }}</div><div class="ni-stat__l">Total</div></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="ni-stat">
        <div class="ni-stat__icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle fs-5"></i></div>
        <div><div class="ni-stat__n">{{ $published }}</div><div class="ni-stat__l">Publicados</div></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="ni-stat">
        <div class="ni-stat__icon" style="background:#fefce8;color:#ca8a04"><i class="bi bi-pencil-square fs-5"></i></div>
        <div><div class="ni-stat__n">{{ $total - $published }}</div><div class="ni-stat__l">Rascunhos</div></div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="modern-card mb-4">
    <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
      <div class="flex-grow-1" style="min-width:200px">
        <label class="form-label mb-1 small text-muted">Pesquisar</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control border-start-0" placeholder="Título..." value="{{ request('q') }}">
        </div>
      </div>
      <div style="min-width:150px">
        <label class="form-label mb-1 small text-muted">Estado</label>
        <select name="status" class="form-select">
          <option value="">Todos</option>
          <option value="Publicado" {{ request('status')=='Publicado'?'selected':'' }}>Publicado</option>
          <option value="Rascunho"  {{ request('status')=='Rascunho' ?'selected':'' }}>Rascunho</option>
        </select>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary-modern px-4">Filtrar</button>
        @if(request()->anyFilled(['q','status']))
        <a href="{{ route('admin.v3.news.index') }}" class="btn btn-secondary-modern px-3"><i class="bi bi-x-lg"></i></a>
        @endif
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="modern-card p-0" style="overflow:hidden">
    @if($articles->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="bi bi-newspaper fs-1 d-block mb-3 opacity-25"></i>
        <p class="mb-3">Nenhum artigo encontrado.</p>
        <a href="{{ route('admin.v3.news.create') }}" class="btn btn-primary-modern">+ Novo Artigo</a>
      </div>
    @else
    <table class="table table-hover mb-0 ni-table">
      <thead>
        <tr>
          <th style="width:52px"></th>
          <th>Artigo</th>
          <th style="width:120px">Data</th>
          <th style="width:110px">Estado</th>
          <th style="width:100px" class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($articles as $a)
        @php
          $c      = $a->contents->pluck('field_value','field_name');
          $title  = $c['title']  ?? '(sem título)';
          $status = $c['status'] ?? 'Rascunho';
          $date   = $c['date']   ?? null;
          $img    = $c['image']  ?? null;
          $words  = str_word_count(strip_tags($c['content'] ?? ''));
        @endphp
        <tr>
          <td class="pe-0">
            <div class="ni-thumb">
              @if($img)
                <img src="{{ asset('storage/'.$img) }}" alt="" onerror="this.src='{{ asset('img/logo-simples.png') }}'">
              @else
                <div class="ni-thumb__empty"><i class="bi bi-image text-muted small"></i></div>
              @endif
            </div>
          </td>
          <td>
            <a href="{{ route('admin.v3.news.edit',$a->id) }}" class="ni-title">{{ $title }}</a>
            <div class="ni-meta">
              <span><i class="bi bi-link-45deg"></i> {{ $a->slug }}</span>
              <span><i class="bi bi-file-word"></i> {{ number_format($words) }} palavras</span>
              <span><i class="bi bi-clock"></i> {{ max(1,(int)($words/200)) }} min</span>
            </div>
          </td>
          <td class="text-muted small">{{ $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '—' }}</td>
          <td>
            <button class="ni-badge {{ $status==='Publicado'?'ni-badge--pub':'ni-badge--draft' }}"
                    data-id="{{ $a->id }}" data-current="{{ $status }}">
              <span class="ni-badge__dot"></span>{{ $status }}
            </button>
          </td>
          <td>
            <div class="d-flex gap-1 justify-content-end">
              <a href="{{ route('admin.v3.news.edit',$a->id) }}" class="ni-btn" title="Editar"><i class="bi bi-pencil"></i></a>
              <a href="{{ route('frontend.news-details',$a->slug) }}" target="_blank" class="ni-btn" title="Ver"><i class="bi bi-eye"></i></a>
              <button type="button" class="ni-btn ni-btn--del" title="Eliminar"
                      data-id="{{ $a->id }}" data-title="{{ addslashes($title) }}">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($articles->hasPages())
      <div class="px-4 py-3 border-top">{{ $articles->links() }}</div>
    @endif
    @endif
  </div>

</div>

{{-- Delete modal --}}
<div class="modal fade" id="delModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold">Eliminar artigo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body pt-0"><p class="text-muted mb-0">Tens a certeza que queres eliminar <strong id="delTitle"></strong>?</p></div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary-modern" data-bs-dismiss="modal">Cancelar</button>
        <form id="delForm" method="POST">@csrf @method('DELETE')
          <button class="btn btn-danger-modern"><i class="bi bi-trash me-1"></i>Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Delete
document.querySelectorAll('.ni-btn--del').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.getElementById('delTitle').textContent = '"'+this.dataset.title+'"';
    document.getElementById('delForm').action = '/gestao/v2/news/'+this.dataset.id;
    new bootstrap.Modal(document.getElementById('delModal')).show();
  });
});

// Status toggle
document.querySelectorAll('.ni-badge').forEach(function(btn){
  btn.addEventListener('click', function(){
    var id      = this.dataset.id;
    var current = this.dataset.current;
    var next    = current === 'Publicado' ? 'Rascunho' : 'Publicado';
    var self    = this;
    fetch('/gestao/v2/news/'+id+'/status', {
      method:'PATCH',
      headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
      body: JSON.stringify({status:next})
    }).then(function(r){ return r.json(); }).then(function(d){
      self.dataset.current = d.status;
      self.className = 'ni-badge '+(d.status==='Publicado'?'ni-badge--pub':'ni-badge--draft');
      self.innerHTML = '<span class="ni-badge__dot"></span>'+d.status;
    });
  });
});
</script>

<style>
.ni-stat{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1.1rem 1.4rem;display:flex;align-items:center;gap:1rem}
.ni-stat__icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ni-stat__n{font-size:1.6rem;font-weight:800;color:#111;line-height:1}
.ni-stat__l{font-size:.75rem;color:#9ca3af;margin-top:.15rem}
.ni-table th{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;font-weight:600;padding:.8rem 1.2rem;background:#f9fafb;border-bottom:1px solid #e5e7eb}
.ni-table td{padding:.8rem 1.2rem;vertical-align:middle;border-bottom:1px solid #f3f4f6}
.ni-table tbody tr:last-child td{border-bottom:none}
.ni-table tbody tr:hover td{background:#fafafa}
.ni-thumb{width:44px;height:40px;border-radius:8px;overflow:hidden;flex-shrink:0}
.ni-thumb img,.ni-thumb__empty{width:100%;height:100%;object-fit:cover;display:block}
.ni-thumb__empty{background:#f3f4f6;display:flex;align-items:center;justify-content:center}
.ni-title{font-weight:700;color:#111;text-decoration:none;font-size:.88rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.ni-title:hover{color:#6e0707}
.ni-meta{display:flex;gap:.75rem;flex-wrap:wrap;font-size:.7rem;color:#9ca3af;margin-top:.2rem}
.ni-meta span{display:flex;align-items:center;gap:.2rem}
.ni-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .7rem;border-radius:100px;border:none;cursor:pointer;font-size:.72rem;font-weight:600;transition:all .15s}
.ni-badge--pub{background:#f0fdf4;color:#16a34a}.ni-badge--pub:hover{background:#dcfce7}
.ni-badge--draft{background:#fefce8;color:#ca8a04}.ni-badge--draft:hover{background:#fef9c3}
.ni-badge__dot{width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0}
.ni-btn{width:30px;height:30px;padding:0;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;text-decoration:none;font-size:.8rem;transition:all .15s;cursor:pointer}
.ni-btn:hover{background:#e5e7eb;color:#111}
.ni-btn--del:hover{background:#fee2e2;color:#dc2626;border-color:#fecaca}
</style>
@endpush
