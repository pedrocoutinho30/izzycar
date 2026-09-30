@extends('layouts.admin-v2')

@section('title', 'Permissões')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Permissões']
    ],
    'title' => 'Permissões',
    'subtitle' => 'Catálogo por categoria, objeto, ação e âmbito — as permissões atribuem-se nos perfis',
    'extraActions' => [
        ['href' => route('admin.v2.roles.index'), 'label' => 'Gerir perfis', 'icon' => 'bi-person-badge', 'class' => 'btn-primary-modern'],
    ],
])

<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<div class="alert alert-info">
    <i class="bi bi-shield-check me-2"></i>
    O perfil <strong>admin</strong> tem acesso total e não aparece na tabela.
    <strong>Todos</strong> = todos os registos; <strong>Próprios</strong> = só os registos associados ao utilizador.
</div>

@php $actionKeys = ['view', 'create', 'update', 'delete']; @endphp

@foreach($modules as $moduleKey => $resources)
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-grid-3x3-gap"></i>
            {{ $registry->moduleLabel($moduleKey) }}
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $resources->count() }} objetos</span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:22%">Objeto</th>
                        @foreach($actionKeys as $action)
                        <th>{{ $registry->actionLabel($action) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($resources as $resourceKey => $resource)
                    <tr>
                        <td>
                            <strong>{{ $resource['label'] }}</strong>
                            <div class="small text-muted font-monospace">{{ $resourceKey }}</div>
                        </td>
                        @foreach($actionKeys as $action)
                        <td class="small">
                            @if(!array_key_exists($action, $resource['actions']))
                                <span class="text-muted">—</span>
                            @else
                                @if($resource['actions'][$action])
                                    <div class="text-muted mb-1">
                                        {{ collect($resource['actions'][$action])->map(fn ($scope) => $registry->scopeLabel($scope))->implode(' / ') }}
                                    </div>
                                @endif
                                @foreach($roleGrants as $roleName => $grants)
                                    @php $grant = $grants[$resourceKey][$action] ?? null; @endphp
                                    @if($grant && $roleName !== 'admin')
                                        <span class="badge {{ $grant === 'own' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} mb-1">
                                            {{ $roleName }}@if(is_string($grant)) · {{ $registry->scopeLabel($grant) }}@endif
                                        </span>
                                    @endif
                                @endforeach
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

@endsection
