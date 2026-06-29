@extends('layouts.admin-v2')

@section('title', 'Log de Auditoria')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Log de Auditoria']
    ],
    'title'    => 'Log de Auditoria',
    'subtitle' => 'Registo de todas as acções realizadas no back-office',
    'actionHref'  => '',
    'actionLabel' => ''
])

{{-- Filtros --}}
<form method="GET" action="{{ route('admin.v2.audit-log') }}" class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Filtros</h5>
        @if(request()->hasAny(['user_id','action','search','date_from','date_to']))
        <a href="{{ route('admin.v2.audit-log') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Limpar
        </a>
        @endif
    </div>
    <div class="p-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Pesquisar na descrição..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos os utilizadores</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">Todas as acções</option>
                    @foreach(['login','logout','created','updated','deleted','exported','converted'] as $act)
                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                        {{ ucfirst($act) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm"
                    value="{{ request('date_from') }}" placeholder="De">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm"
                    value="{{ request('date_to') }}" placeholder="Até">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary-modern btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Tabela de logs --}}
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-shield-check"></i> Eventos</h5>
        <span class="badge bg-secondary rounded-pill">{{ $logs->total() }} registos</span>
    </div>

    @if($logs->isEmpty())
    <div class="p-5 text-center text-muted">
        <i class="bi bi-shield-check fs-2 d-block mb-2"></i>
        Nenhum evento encontrado com os filtros aplicados.
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 audit-table">
            <thead>
                <tr>
                    <th style="width:140px">Data / Hora</th>
                    <th style="width:140px">Utilizador</th>
                    <th style="width:90px">Acção</th>
                    <th>Descrição</th>
                    <th style="width:110px">IP</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
            @php
                $actionConfig = [
                    'created'   => ['color' => 'success',   'icon' => 'bi-plus-circle-fill',    'label' => 'Criado'],
                    'updated'   => ['color' => 'primary',   'icon' => 'bi-pencil-fill',          'label' => 'Editado'],
                    'deleted'   => ['color' => 'danger',    'icon' => 'bi-trash-fill',           'label' => 'Eliminado'],
                    'login'     => ['color' => 'info',      'icon' => 'bi-box-arrow-in-right',   'label' => 'Login'],
                    'logout'    => ['color' => 'secondary', 'icon' => 'bi-box-arrow-right',      'label' => 'Logout'],
                    'exported'  => ['color' => 'warning',   'icon' => 'bi-download',             'label' => 'Exportado'],
                    'converted' => ['color' => 'success',   'icon' => 'bi-person-check-fill',    'label' => 'Convertido'],
                ];
                $ac = $actionConfig[$log->action] ?? ['color' => 'secondary', 'icon' => 'bi-circle', 'label' => $log->action];
            @endphp
            <tr>
                <td class="text-muted small">
                    {{ $log->created_at->format('d/m/Y') }}<br>
                    <span class="fw-semibold text-dark">{{ $log->created_at->format('H:i:s') }}</span>
                </td>
                <td>
                    @if($log->user)
                    <div class="d-flex align-items-center gap-2">
                        <div class="audit-avatar">{{ strtoupper(substr($log->user->name, 0, 1)) }}</div>
                        <span class="small">{{ $log->user->name }}</span>
                    </div>
                    @else
                    <span class="text-muted small"><i class="bi bi-gear me-1"></i>Sistema</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $ac['color'] }}-subtle text-{{ $ac['color'] }} border border-{{ $ac['color'] }}-subtle">
                        <i class="{{ $ac['icon'] }} me-1"></i>{{ $ac['label'] }}
                    </span>
                </td>
                <td class="small">{{ $log->description }}</td>
                <td class="text-muted small font-monospace">{{ $log->ip_address }}</td>
                <td>
                    @if($log->old_values || $log->new_values)
                    <button class="btn btn-icon btn-outline-secondary btn-xs"
                            data-bs-toggle="collapse"
                            data-bs-target="#diff-{{ $log->id }}"
                            title="Ver alterações">
                        <i class="bi bi-code-slash"></i>
                    </button>
                    @endif
                </td>
            </tr>
            @if($log->old_values || $log->new_values)
            <tr class="collapse" id="diff-{{ $log->id }}">
                <td colspan="6" class="p-0">
                    <div class="audit-diff">
                        @if($log->old_values)
                        <div class="audit-diff__col audit-diff__col--old">
                            <div class="audit-diff__label">Antes</div>
                            @foreach($log->old_values as $field => $val)
                            <div class="audit-diff__row">
                                <span class="audit-diff__field">{{ $field }}</span>
                                <span class="audit-diff__val">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @if($log->new_values)
                        <div class="audit-diff__col audit-diff__col--new">
                            <div class="audit-diff__label">Depois</div>
                            @foreach($log->new_values as $field => $val)
                            <div class="audit-diff__row">
                                <span class="audit-diff__field">{{ $field }}</span>
                                <span class="audit-diff__val">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@include('components.admin.pagination-footer', ['items' => $logs, 'label' => 'eventos'])

@endsection

@push('styles')
<style>
.audit-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #aaa; font-weight: 700; border-bottom: 1px solid #eee; background: #fafafa; }
.audit-table td { font-size: .83rem; border-bottom: 1px solid #f5f5f5; }
.audit-table tr:last-child td { border-bottom: none; }

.audit-avatar {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--admin-primary), #990000);
    color: #fff; font-size: .72rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.btn-xs { padding: .15rem .4rem; font-size: .75rem; }

/* Diff antes/depois */
.audit-diff {
    display: flex; gap: 0;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}
.audit-diff__col { flex: 1; padding: .75rem 1.25rem; }
.audit-diff__col--old { background: #fff5f5; border-right: 1px solid #eee; }
.audit-diff__col--new { background: #f0fff4; }
.audit-diff__label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #aaa; margin-bottom: .5rem; }
.audit-diff__row { display: flex; gap: 1rem; font-size: .78rem; padding: .2rem 0; border-bottom: 1px solid rgba(0,0,0,.04); }
.audit-diff__row:last-child { border-bottom: none; }
.audit-diff__field { color: #888; min-width: 120px; flex-shrink: 0; }
.audit-diff__val { color: #111; word-break: break-all; }
</style>
@endpush
