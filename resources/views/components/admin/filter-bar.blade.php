{{--
    COMPONENTE: Filter Bar
    
    USO:
    @include('components.admin.filter-bar', [
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Estado',
                'type' => 'select',
                'options' => ['Pendente', 'Aprovada', 'Reprovada'],
                'value' => request('status')
            ],
            [
                'name' => 'search',
                'label' => 'Pesquisar',
                'type' => 'text',
                'placeholder' => 'Pesquisar por nome...',
                'value' => request('search')
            ]
        ],
        'action' => route('proposals.index')
    ])
    
    DESCRIÇÃO:
    Barra de filtros reutilizável com múltiplos tipos de input
    Adaptativa para mobile com collapse
--}}

@props([
    'filters' => [],
    'action' => '',
    'method' => 'GET'
])

<div class="modern-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h6>
        <button class="btn btn-sm btn-secondary-modern d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>

    <div class="collapse show" id="filterCollapse">
        <form action="{{ $action }}" method="{{ $method }}" class="row g-3">
            @csrf
            
            @foreach($filters as $filter)
                <div class="col-md-{{ $filter['col'] ?? 4 }} col-12">
                    <label for="{{ $filter['name'] }}" class="form-label">
                        {{ $filter['label'] }}
                    </label>

                    @if($filter['type'] === 'select')
                        <select 
                            name="{{ $filter['name'] }}" 
                            id="{{ $filter['name'] }}" 
                            class="form-select"
                            @if(isset($filter['onchange'])) onchange="{{ $filter['onchange'] }}" @endif
                        >
                            <option value="">Todos</option>
                            @foreach($filter['options'] as $key => $option)
                                <option 
                                    value="{{ is_array($option) ? $option['value'] : $key }}" 
                                    {{ ($filter['value'] ?? '') == (is_array($option) ? $option['value'] : $key) ? 'selected' : '' }}
                                >
                                    {{ is_array($option) ? $option['label'] : $option }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($filter['type'] === 'text')
                        <input 
                            type="text" 
                            name="{{ $filter['name'] }}" 
                            id="{{ $filter['name'] }}" 
                            class="form-control" 
                            placeholder="{{ $filter['placeholder'] ?? '' }}"
                            value="{{ $filter['value'] ?? '' }}"
                        >

                    @elseif($filter['type'] === 'date')
                        <input 
                            type="date" 
                            name="{{ $filter['name'] }}" 
                            id="{{ $filter['name'] }}" 
                            class="form-control"
                            value="{{ $filter['value'] ?? '' }}"
                        >

                    @elseif($filter['type'] === 'number')
                        <input 
                            type="number" 
                            name="{{ $filter['name'] }}" 
                            id="{{ $filter['name'] }}" 
                            class="form-control" 
                            placeholder="{{ $filter['placeholder'] ?? '' }}"
                            value="{{ $filter['value'] ?? '' }}"
                            @if(isset($filter['min'])) min="{{ $filter['min'] }}" @endif
                            @if(isset($filter['max'])) max="{{ $filter['max'] }}" @endif
                        >
                    @endif
                </div>
            @endforeach

            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="bi bi-search"></i>
                        Filtrar
                    </button>
                    <a href="{{ $action }}" class="btn btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
