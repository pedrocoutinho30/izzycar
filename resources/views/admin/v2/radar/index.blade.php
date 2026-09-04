@extends('layouts.admin-v2')

@section('title', 'Scarper AutoScout24')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-broadcast"></i> Radar de Preços AutoScout24
            </h1>
            <p class="text-muted mb-0">Uma pesquisa (radar) por segmento de carro. Cada uma tem o seu próprio histórico de preços.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.v2.radar-equipment.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-check"></i> Equipamento
            </a>
            <a href="{{ route('admin.v2.radar.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nova Pesquisa
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="bi bi-car-front"></i> Pesquisas</h5>
            <span class="badge bg-secondary rounded-pill">{{ $searches->count() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Estado</th>
                        <th>Última recolha</th>
                        <th class="text-end">🇩🇪 Alemanha</th>
                        <th class="text-end">🇵🇹 Portugal</th>
                        <th class="text-center" title="Entra na atualização automática periódica">Ativa</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($searches as $search)
                        @php
                            $run = $search->latestRun;
                            $badgeColor = match($run?->status) {
                                'ok' => 'success',
                                'blocked' => 'warning',
                                'error' => 'danger',
                                default => 'secondary',
                            };
                            $badgeLabel = match($run?->status) {
                                'ok' => 'Recolha OK',
                                'blocked' => 'Bloqueado pelo site',
                                'error' => 'Erro na recolha',
                                default => 'Nunca corrida',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.v2.radar.show', $search) }}" class="fw-semibold text-decoration-none">{{ $search->name }}</a>
                                @if($search->new_listings_count > 0)
                                    <span class="badge bg-danger ms-1" title="{{ $search->new_listings_count }} anúncio(s) novo(s) desde a última vez que abriste esta pesquisa">novo</span>
                                @endif
                                <div class="text-muted small">{{ $search->make ? ucfirst($search->make) : 'Todas as marcas' }}{{ $search->model ? ' ' . ucfirst($search->model) : '' }}</div>
                            </td>
                            <td><span class="badge bg-{{ $badgeColor }} bg-opacity-75">{{ $badgeLabel }}</span></td>
                            <td class="text-muted small">{{ $run?->started_at->diffForHumans() ?? '—' }}</td>
                            <td class="text-end">{{ $search->listings_count }}</td>
                            <td class="text-end">{{ $search->pt_listings_count > 0 ? $search->pt_listings_count : '—' }}</td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input active-toggle" data-search-id="{{ $search->id }}" {{ $search->is_active ? 'checked' : '' }}
                                       title="Entra na atualização automática periódica">
                            </td>
                            <td class="text-end">
                                <div class="item-actions d-inline-flex gap-1">
                                    <a href="{{ route('admin.v2.radar.show', $search) }}" class="btn btn-icon btn-secondary-modern" title="Ver anúncios">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.v2.radar.run', $search) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-primary-modern" title="Correr novamente">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.v2.radar.edit', $search) }}" class="btn btn-icon btn-secondary-modern" title="Editar pesquisa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.v2.radar.destroy', $search) }}" class="d-inline"
                                          onsubmit="return confirm('Apagar a pesquisa &quot;{{ $search->name }}&quot;? Isto apaga também todos os anúncios e histórico de preços recolhidos. Não pode ser desfeito.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-danger-modern" title="Apagar pesquisa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">Ainda não há pesquisas. <a href="{{ route('admin.v2.radar.create') }}">Cria a primeira</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    const toggleUrlTemplate = '{{ route('admin.v2.radar.toggle-active', ['radarSearch' => '__ID__']) }}';

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('active-toggle')) return;

        const checkbox = e.target;
        checkbox.disabled = true;

        fetch(toggleUrlTemplate.replace('__ID__', checkbox.dataset.searchId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ active: checkbox.checked }),
        }).finally(() => { checkbox.disabled = false; });
    });
})();
</script>
@endsection
