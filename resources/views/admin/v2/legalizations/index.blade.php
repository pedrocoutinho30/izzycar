@extends('layouts.admin-v2')

@section('title', 'Legalizações')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-check', 'label' => 'Legalizações', 'href' => ''],
    ],
    'title'       => 'Legalizações',
    'subtitle'    => 'Gestão do processo de legalização de veículos importados',
    'actionHref'  => route('admin.legalizations.create'),
    'actionLabel' => 'Nova Legalização',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['icon' => 'file-earmark-check', 'title' => 'Total',       'value' => $stats['total'],      'color' => 'primary'],
        ['icon' => 'hourglass-split',     'title' => 'Em curso',    'value' => $stats['em_curso'],   'color' => 'info'],
        ['icon' => 'check-circle',        'title' => 'Concluídas',  'value' => $stats['concluidas'], 'color' => 'success'],
        ['icon' => 'plus-circle',         'title' => 'Novas',       'value' => $stats['novas'],      'color' => 'warning'],
    ]
])

<div class="modern-card">
    <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-list-ul me-1"></i> Lista de Legalizações
        </h5>
        <input type="text" id="tableSearch" class="form-control form-control-sm" style="max-width:240px"
               placeholder="&#xF52A; Pesquisar...">
    </div>
    <div class="modern-card-body p-0">
        @if($legalizations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                Ainda não há legalizações registadas.
                <div class="mt-3">
                    <a href="{{ route('admin.legalizations.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i> Criar primeira legalização
                    </a>
                </div>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0" id="legalizationsTable">
                <thead class="table-light">
                    <tr>
                        <th>Marca / Modelo</th>
                        <th>Cliente</th>
                        <th>Nº Homologação</th>
                        <th class="text-center">Progresso</th>
                        <th class="text-center">Documentos</th>
                        <th class="text-center">Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($legalizations as $leg)
                    @php
                        $progress = $leg->progressPercent();
                        $docsCount = $leg->documents->count();
                        $progressColor = $progress === 100 ? 'success' : ($progress > 0 ? 'info' : 'secondary');
                    @endphp
                    <tr>
                        <td class="fw-semibold">
                            {{ $leg->marca }} {{ $leg->modelo }}
                        </td>
                        <td>{{ $leg->client?->name ?? '—' }}</td>
                        <td>
                            @if($leg->num_homologacao)
                                <span class="font-monospace small">{{ $leg->num_homologacao }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center" style="min-width:130px">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width:{{ $progress }}%"></div>
                                </div>
                                <small class="text-muted text-nowrap">{{ $progress }}%</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                <i class="bi bi-paperclip me-1"></i>{{ $docsCount }}
                            </span>
                        </td>
                        <td class="text-center text-muted small text-nowrap">
                            {{ $leg->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.legalizations.show', $leg) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.legalizations.edit', $leg) }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.legalizations.destroy', $leg) }}" method="POST" class="d-inline ms-1"
                                  onsubmit="return confirm('Eliminar esta legalização e todos os documentos associados?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('tableSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#legalizationsTable tbody tr').forEach(row => {
        row.style.display = !q || row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
