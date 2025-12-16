{{--
    COMPONENTE: Stats Cards
    
    USO:
    @include('components.admin.stats-cards', [
        'stats' => [
            [
                'title' => 'Total Propostas',
                'value' => '156',
                'icon' => 'file-earmark-text',
                'color' => 'primary',
                'change' => '+12%',
                'changeType' => 'positive'
            ],
            [
                'title' => 'Pendentes',
                'value' => '23',
                'icon' => 'clock',
                'color' => 'warning'
            ]
        ]
    ])
    
    DESCRIÇÃO:
    Cards de estatísticas para dashboard
    Grid responsivo que adapta em mobile
--}}

@props([
    'stats' => []
])

<div class="row g-3 mb-4">
    @foreach($stats as $stat)
    <div class="col-md-6 col-lg-3 col-12">
        <div class="modern-card stat-card stat-{{ $stat['color'] ?? 'primary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-content">
                    <div class="stat-label">{{ $stat['title'] }}</div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    @if(isset($stat['change']))
                    <div class="stat-change {{ $stat['changeType'] ?? 'neutral' }}">
                        <i class="bi {{ $stat['changeType'] === 'positive' ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ $stat['change'] }}
                    </div>
                    @endif
                </div>
                <div class="stat-icon stat-icon-{{ $stat['color'] ?? 'primary' }}">
                    <i class="bi {{ $stat['icon'] }}"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('styles')
<style>
    /* Stats Cards */
    .stat-card {
        padding: 1.5rem;
        height: 100%;
        border-left: 4px solid;
    }

    .stat-card.stat-primary {
        border-left-color: var(--admin-primary);
    }

    .stat-card.stat-success {
        border-left-color: var(--admin-success);
    }

    .stat-card.stat-warning {
        border-left-color: var(--admin-warning);
    }

    .stat-card.stat-danger {
        border-left-color: var(--admin-danger);
    }

    .stat-card.stat-info {
        border-left-color: var(--admin-info);
    }

    .stat-label {
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--admin-secondary);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive {
        color: var(--admin-success);
    }

    .stat-change.negative {
        color: var(--admin-danger);
    }

    .stat-change.neutral {
        color: #666;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .stat-icon-primary {
        background: linear-gradient(135deg, rgba(110, 7, 7, 0.1), rgba(110, 7, 7, 0.2));
        color: var(--admin-primary);
    }

    .stat-icon-success {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.2));
        color: var(--admin-success);
    }

    .stat-icon-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.2));
        color: var(--admin-warning);
    }

    .stat-icon-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.2));
        color: var(--admin-danger);
    }

    .stat-icon-info {
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(23, 162, 184, 0.2));
        color: var(--admin-info);
    }

    @media (max-width: 768px) {
        .stat-value {
            font-size: 1.5rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
    }
</style>
@endpush
