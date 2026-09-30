{{-- Matriz de permissões de um perfil: um cartão por categoria, uma linha por
     objeto, colunas Ver/Criar/Editar/Eliminar. Ações com âmbito têm um select
     (— / Todos / Próprios); as restantes, uma caixa. Espera $modules,
     $registry, $grants e $readonly (true = só consulta). --}}
@php
    $readonly = $readonly ?? false;
    $actionKeys = ['view', 'create', 'update', 'delete'];
@endphp

@foreach($modules as $moduleKey => $resources)
<div class="modern-card" data-matrix-module>
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-grid-3x3-gap"></i>
            {{ $registry->moduleLabel($moduleKey) }}
        </h5>
        @unless($readonly)
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary-modern" data-matrix-fill="all">Tudo</button>
            <button type="button" class="btn btn-sm btn-secondary-modern" data-matrix-fill="none">Nada</button>
        </div>
        @endunless
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:32%">Objeto</th>
                        @foreach($actionKeys as $action)
                        <th class="text-center">{{ $registry->actionLabel($action) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($resources as $resourceKey => $resource)
                    <tr>
                        <td><strong>{{ $resource['label'] }}</strong></td>
                        @foreach($actionKeys as $action)
                        @php
                            $available = array_key_exists($action, $resource['actions']);
                            $scopes = $resource['actions'][$action] ?? [];
                            $current = $grants[$resourceKey][$action] ?? null;
                            $field = "grants[{$resourceKey}][{$action}]";
                        @endphp
                        <td class="text-center">
                            @if(!$available)
                                <span class="text-muted">—</span>
                            @elseif($readonly)
                                @if($current === true)
                                    <i class="bi bi-check-lg text-success"></i>
                                @elseif($current)
                                    <span class="badge {{ $current === 'all' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $registry->scopeLabel($current) }}</span>
                                @else
                                    <i class="bi bi-x text-muted"></i>
                                @endif
                            @elseif($scopes === [])
                                <input type="hidden" name="{{ $field }}" value="">
                                <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" @checked($current === true)
                                       aria-label="{{ $registry->actionLabel($action) }} {{ $resource['label'] }}">
                            @else
                                <select name="{{ $field }}" class="form-select form-select-sm mx-auto" style="max-width:120px"
                                        aria-label="{{ $registry->actionLabel($action) }} {{ $resource['label'] }}">
                                    <option value="">—</option>
                                    @foreach($scopes as $scope)
                                    <option value="{{ $scope }}" @selected($current === $scope)>{{ $registry->scopeLabel($scope) }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@unless($readonly)
@once
@push('scripts')
<script>
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-matrix-fill]');
    if (!button) return;
    const all = button.dataset.matrixFill === 'all';
    const card = button.closest('[data-matrix-module]');
    card.querySelectorAll('input[type="checkbox"]').forEach(box => { box.checked = all; });
    // "Tudo" escolhe o âmbito mais abrangente (primeira opção a seguir a "—").
    card.querySelectorAll('select').forEach(select => { select.selectedIndex = all ? 1 : 0; });
});
</script>
@endpush
@endonce
@endunless
