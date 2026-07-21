@extends('layouts.admin-v2')

@section('title', 'Relatórios Mensais')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Relatórios'],
    ],
    'title'    => 'Relatórios Mensais',
    'subtitle' => 'Arquivo de relatórios PDF e geração manual',
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ─── Gerar Relatório ─── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-plus-circle text-danger me-2"></i>Gerar Relatório
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Gera o relatório para um mês específico, guarda-o em arquivo e envia por email.
                </p>
                <form action="{{ route('admin.v2.reports.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="month" class="form-label fw-semibold small">Mês</label>
                        <input type="month"
                               id="month"
                               name="month"
                               class="form-control @error('month') is-invalid @enderror"
                               value="{{ old('month', now()->subMonth()->format('Y-m')) }}"
                               max="{{ now()->format('Y-m') }}">
                        @error('month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary-modern w-100">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Gerar e Enviar
                    </button>
                </form>

                <hr class="my-3">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    O relatório automático é gerado no último dia de cada mês às 23:30.
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Arquivo de Relatórios ─── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-archive text-danger me-2"></i>Arquivo
                </h6>
                <span class="badge bg-secondary">{{ count($reports) }} relatório(s)</span>
            </div>
            <div class="card-body p-0">
                @if(count($reports) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Período</th>
                                <th>Ficheiro</th>
                                <th>Tamanho</th>
                                <th>Gerado em</th>
                                <th class="pe-4 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 lh-1">
                                            <i class="bi bi-file-earmark-pdf fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-capitalize">{{ $report['label'] }}</div>
                                            <div class="text-muted small">{{ $report['year'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-muted small">{{ $report['name'] }}</code>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $report['size'] }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $report['mtime']->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('admin.v2.reports.download', ['year' => $report['year'], 'month' => str_pad($report['month'], 2, '0', STR_PAD_LEFT)]) }}"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-pdf fs-1 d-block mb-2 opacity-25"></i>
                    <div class="fw-semibold">Ainda não existem relatórios</div>
                    <div class="small mt-1">Gera o primeiro relatório usando o formulário ao lado.</div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection
