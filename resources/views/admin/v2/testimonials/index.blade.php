@extends('layouts.admin-v2')

@section('title', 'Testemunhos')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-chat-quote', 'label' => 'Testemunhos', 'href' => ''],
    ],
    'title'       => 'Testemunhos',
    'subtitle'    => 'Gestão de avaliações e opiniões de clientes',
    'actionHref'  => route('admin.testimonials.create'),
    'actionLabel' => 'Novo Testemunho',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['icon' => 'chat-quote',   'title' => 'Total',      'value' => $stats['total'],      'color' => 'primary'],
        ['icon' => 'eye',          'title' => 'Publicados', 'value' => $stats['publicados'], 'color' => 'success'],
        ['icon' => 'star-fill',    'title' => 'Média',      'value' => $stats['media'] . ' ★', 'color' => 'warning'],
        ['icon' => 'google',       'title' => 'Google',     'value' => $stats['google'],     'color' => 'info'],
    ]
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="modern-card">
    <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-list-ul me-1"></i> Lista de Testemunhos
        </h5>
        <input type="text" id="tableSearch" class="form-control form-control-sm" style="max-width:240px"
               placeholder="&#xF52A; Pesquisar...">
    </div>
    <div class="modern-card-body p-0">
        @if($testimonials->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-chat-quote display-4 d-block mb-2"></i>
                Ainda não há testemunhos registados.
                <div class="mt-3">
                    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Adicionar o primeiro
                    </a>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="testimonialTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Avaliação</th>
                            <th>Origem</th>
                            <th>Comentário</th>
                            <th>Publicado</th>
                            <th>Data</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testimonials as $t)
                        <tr>
                            <td class="fw-semibold align-middle">{{ $t->name }}</td>
                            <td class="align-middle text-nowrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $t->rating ? '-fill text-warning' : ' text-muted opacity-25' }}" style="font-size:.85rem"></i>
                                @endfor
                                <span class="ms-1 text-muted small">({{ $t->rating }})</span>
                            </td>
                            <td class="align-middle">
                                @php
                                    $originColors = ['google'=>'danger','facebook'=>'primary','instagram'=>'warning','email'=>'secondary','outro'=>'dark'];
                                    $originIcons  = ['google'=>'google','facebook'=>'facebook','instagram'=>'instagram','email'=>'envelope','outro'=>'three-dots'];
                                    $oc = $originColors[$t->origin] ?? 'secondary';
                                    $oi = $originIcons[$t->origin] ?? 'chat';
                                @endphp
                                <span class="badge bg-{{ $oc }} bg-opacity-10 text-{{ $oc }} border border-{{ $oc }} border-opacity-25">
                                    <i class="bi bi-{{ $oi }} me-1"></i>{{ \App\Models\Testimonial::ORIGINS[$t->origin] ?? $t->origin }}
                                </span>
                            </td>
                            <td class="align-middle" style="max-width:300px">
                                <span class="text-truncate d-block" style="max-width:280px" title="{{ $t->comment }}">{{ $t->comment }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="form-check form-switch" style="min-width:50px">
                                    <input class="form-check-input toggle-published" type="checkbox"
                                           data-id="{{ $t->id }}"
                                           data-url="{{ route('admin.testimonials.toggle', $t) }}"
                                           {{ $t->published ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="align-middle text-muted small text-nowrap">{{ $t->created_at->format('d/m/Y') }}</td>
                            <td class="align-middle text-end text-nowrap">
                                <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Eliminar este testemunho?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
// Pesquisa na tabela
document.getElementById('tableSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#testimonialTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Toggle publicado via AJAX
document.querySelectorAll('.toggle-published').forEach(function (cb) {
    cb.addEventListener('change', function () {
        fetch(this.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            this.checked = data.published;
        })
        .catch(() => { this.checked = !this.checked; });
    });
});
</script>
@endpush
