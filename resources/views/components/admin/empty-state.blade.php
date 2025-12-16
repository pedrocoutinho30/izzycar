{{--
    COMPONENTE: Empty State
    
    USO:
    @include('components.admin.empty-state', [
        'icon' => 'inbox',
        'title' => 'Nenhum resultado encontrado',
        'message' => 'Não existem dados para exibir',
        'action' => [
            'text' => 'Criar Novo',
            'href' => route('create'),
            'icon' => 'plus-lg'
        ]
    ])
    
    DESCRIÇÃO:
    Estado vazio elegante para quando não há dados a exibir
--}}

@props([
    'icon' => 'inbox',
    'title' => 'Nenhum resultado',
    'message' => '',
    'action' => null
])

<div class="empty-state text-center py-5">
    <div class="empty-icon mb-4">
        <i class="bi {{ $icon }}"></i>
    </div>
    
    <h4 class="empty-title mb-2">{{ $title }}</h4>
    
    @if($message)
        <p class="empty-message text-muted mb-4">{{ $message }}</p>
    @endif
    
    @if($action)
        <a href="{{ $action['href'] }}" class="btn btn-primary-modern">
            @if(isset($action['icon']))
                <i class="bi {{ $action['icon'] }} text-white"></i>
            @endif
            {{ $action['text'] }}
        </a>
    @endif
</div>

@push('styles')
<style>
    .empty-state {
        padding: 4rem 2rem;
    }

    .empty-icon {
        font-size: 5rem;
        color: #dee2e6;
        opacity: 0.5;
    }

    .empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #6c757d;
    }

    .empty-message {
        font-size: 1rem;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 768px) {
        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-icon {
            font-size: 3.5rem;
        }

        .empty-title {
            font-size: 1.25rem;
        }

        .empty-message {
            font-size: 0.9rem;
        }
    }
</style>
@endpush
