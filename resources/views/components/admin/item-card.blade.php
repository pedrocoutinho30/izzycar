{{--
    COMPONENTE: Item Card
    
    USO:
    @include('components.admin.item-card', [
        'item' => $proposal,
        'title' => $proposal->brand . ' ' . $proposal->model,
        'subtitle' => $proposal->version,
        'image' => $proposal->images[0] ?? null,
        'badges' => [
            ['text' => $proposal->status, 'color' => 'primary'],
            ['text' => $proposal->year, 'color' => 'secondary']
        ],
        'meta' => [
            ['icon' => 'person', 'text' => $proposal->client->name],
            ['icon' => 'calendar', 'text' => $proposal->created_at->format('d/m/Y')]
        ],
        'actions' => [
            ['icon' => 'pencil', 'href' => route('proposals.edit', $proposal), 'color' => 'primary', 'label' => 'Editar'],
            ['icon' => 'trash', 'href' => route('proposals.destroy', $proposal), 'color' => 'danger', 'label' => 'Eliminar', 'method' => 'DELETE']
        ]
    ])
    
    DESCRIÇÃO:
    Card moderno para exibir items em listas
    Mobile-first com layout adaptativo
--}}

@props([
    'item' => null,
    'title' => '',
    'subtitle' => '',
    'image' => null,
    'badges' => [],
    'meta' => [],
    'actions' => []
])

<div class="modern-card item-card">
    <div class="row g-3">
        <!-- Imagem (se existir) -->
        @if($image)
        <div class="col-auto">
            <div class="item-image">
                <img src="{{ is_array($image) ? asset('storage/' . $image) : $image }}" alt="{{ $title }}">
            </div>
        </div>
        @endif

        <!-- Conteúdo principal -->
        <div class="col">
            <!-- Título e subtitle -->
            <div class="item-header">
                <h5 class="item-title">{{ $title }}</h5>
                @if($subtitle)
                <p class="item-subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            <!-- Badges -->
            @if(count($badges) > 0)
            <div class="item-badges mb-2">
                @foreach($badges as $badge)
                <span class="badge bg-{{ $badge['color'] ?? 'secondary' }} rounded-pill">
                    @if(isset($badge['icon']))
                    <i class="bi {{ $badge['icon'] }} me-1"></i>
                    @endif
                    {{ $badge['text'] }}
                </span>
                @endforeach
            </div>
            @endif

            <!-- Meta informações -->
            @if(count($meta) > 0)
            <div class="item-meta">
                @foreach($meta as $m)
                <span class="meta-item">
                    <i class="bi {{ $m['icon'] }}"></i>
                    {{ $m['text'] }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Ações -->
        @if(count($actions) > 0)
        <div class="col-12 col-md-auto">
            <div class="item-actions">
                @foreach($actions as $action)
                    @if(isset($action['method']) && $action['method'] !== 'GET')
                    <form action="{{ $action['href'] }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $action['confirm'] ?? 'Tem certeza?' }}')">
                        @csrf
                        @method($action['method'])
                        <button 
                            type="submit" 
                            class="btn btn-icon btn-{{ $action['color'] ?? 'secondary' }}-modern"
                            title="{{ $action['label'] ?? '' }}"
                        >
                            <i class="bi {{ $action['icon'] }}"></i>
                        </button>
                    </form>
                    @else
                    <a 
                        href="{{ $action['href'] }}" 
                        class="btn btn-icon btn-{{ $action['color'] ?? 'secondary' }}-modern"
                        title="{{ $action['label'] ?? '' }}"
                        @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                    >
                        <i class="bi {{ $action['icon'] }}"></i>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Estilos do item card */
    .item-card {
        transition: all 0.3s ease;
    }

    .item-card:hover {
        transform: translateY(-2px);
    }

    .item-image {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-header {
        margin-bottom: 0.75rem;
    }

    .item-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--admin-secondary);
        margin-bottom: 0.25rem;
    }

    .item-subtitle {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0;
    }

    .item-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.875rem;
        color: #666;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .meta-item i {
        color: var(--admin-primary);
    }

    .item-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .item-actions {
            justify-content: flex-start;
        }

        .item-image {
            width: 60px;
            height: 60px;
        }

        .item-title {
            font-size: 1rem;
        }
    }
</style>
@endpush
