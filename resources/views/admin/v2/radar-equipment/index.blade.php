@extends('layouts.admin-v2')

@section('title', 'Equipamento — Radar de Preços')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.v2.radar.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Radar de Preços
            </a>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-list-check"></i> Equipamento
            </h1>
            <p class="text-muted mb-0">
                Equipamento descoberto automaticamente nos anúncios (AutoScout24 e Standvirtual) - só entra nos
                filtros das pesquisas depois de ativares aqui. Podes renomear e fundir itens equivalentes entre sites.
            </p>
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
            <h5 class="modern-card-title mb-0"><i class="bi bi-list-check"></i> Equipamento descoberto</h5>
            <span class="badge bg-secondary rounded-pill">{{ $equipment->count() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" title="Mostrar como opção de filtro no formulário das pesquisas">Nos filtros</th>
                        <th>Equipamento</th>
                        <th>Aliases (origem)</th>
                        <th class="text-end">Anúncios</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment as $item)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input equipment-filter-toggle" data-equipment-id="{{ $item->id }}" {{ $item->show_in_filters ? 'checked' : '' }}>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.v2.radar-equipment.update', $item) }}" class="d-flex gap-1">
                                @csrf
                                @method('PUT')
                                <input type="text" name="label" value="{{ $item->label }}" class="form-control form-control-sm" style="max-width:260px">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Guardar nome">
                                    <i class="bi bi-check"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            @foreach($item->aliases as $alias)
                                <span class="badge bg-light text-dark border mb-1 d-inline-flex align-items-center gap-1" title="{{ $alias->raw_key }}">
                                    {{ $alias->source === 'autoscout24' ? '🇩🇪' : '🇵🇹' }} {{ \Illuminate\Support\Str::limit($alias->raw_key, 30) }}
                                    @if($item->aliases->count() > 1)
                                    <form method="POST" action="{{ route('admin.v2.radar-equipment.detach-alias', $alias) }}"
                                          onsubmit="return confirm('Desacoplar &quot;{{ addslashes($alias->raw_key) }}&quot; para o seu próprio item? Anúncios já associados a &quot;{{ addslashes($item->label) }}&quot; ficam como estão - só os anúncios novos é que vão passar a usar o item separado.');">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Desacoplar deste item">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </span>
                            @endforeach
                        </td>
                        <td class="text-end">{{ $item->listings_count }}</td>
                        <td class="text-end">
                            <div class="item-actions d-inline-flex gap-1">
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-secondary-modern" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Fundir noutro item">
                                        <i class="bi bi-arrow-down-up"></i>
                                    </button>
                                    <div class="dropdown-menu p-2" style="min-width:260px">
                                        <form method="POST" action="{{ route('admin.v2.radar-equipment.merge', $item) }}"
                                              onsubmit="return confirm('Fundir &quot;{{ addslashes($item->label) }}&quot; no item selecionado? Esta ação apaga &quot;{{ addslashes($item->label) }}&quot; e move os seus aliases/associações para o destino. Não pode ser desfeito.');">
                                            @csrf
                                            <label class="form-label small mb-1">Fundir em:</label>
                                            <select name="target_id" class="form-select form-select-sm mb-2" required>
                                                <option value="">Escolhe o destino</option>
                                                @foreach($equipment as $other)
                                                    @if($other->id !== $item->id)
                                                    <option value="{{ $other->id }}">{{ $other->label }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">Fundir</button>
                                        </form>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.v2.radar-equipment.destroy', $item) }}" class="d-inline"
                                      onsubmit="return confirm('Apagar &quot;{{ addslashes($item->label) }}&quot;? Remove-o de todos os anúncios e pesquisas onde estava selecionado.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger-modern" title="Apagar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            Ainda não foi descoberto equipamento nenhum - aparece aqui automaticamente à medida que novos anúncios forem recolhidos.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    const toggleUrlTemplate = '{{ route('admin.v2.radar-equipment.toggle-filter', ['radarEquipment' => '__ID__']) }}';

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('equipment-filter-toggle')) return;

        const checkbox = e.target;
        checkbox.disabled = true;

        fetch(toggleUrlTemplate.replace('__ID__', checkbox.dataset.equipmentId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ show: checkbox.checked }),
        }).finally(() => { checkbox.disabled = false; });
    });
})();
</script>
@endsection
