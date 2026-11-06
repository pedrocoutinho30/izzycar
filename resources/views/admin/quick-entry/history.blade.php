@extends('layouts.admin-v2')

@section('title', 'Histórico da inserção rápida')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi-lightning-charge', 'label' => 'Histórico da inserção rápida'],
    ],
    'title' => 'Histórico da inserção rápida',
    'subtitle' => 'Frases interpretadas, proposta original e o que foi guardado. Serve para melhorar as regras; não as altera.',
])

<form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">Todos os estados</option>
        @foreach(['interpretada', 'confirmada', 'cancelada'] as $s)
            <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Data</th><th>Utilizador</th><th>Frase</th><th>Comando</th><th>Estado</th><th>Versão</th><th>Registo</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                @php
                    $fields = $item->proposal['fields'] ?? [];
                    $changed = [];
                    foreach ($item->final_data ?? [] as $k => $v) {
                        $before = $fields[$k]['value'] ?? null;
                        if ((string) $before !== (string) $v) { $changed[$k] = [$before, $v]; }
                    }
                @endphp
                <tr>
                    <td class="text-nowrap">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ trim(($item->user?->name ?? '—') . ' ' . ($item->user?->last_name ?? '')) }}</td>
                    <td style="max-width:420px">
                        {{ $item->original_text }}
                        <details class="small text-muted mt-1">
                            <summary>Proposta e avisos</summary>
                            @foreach($item->proposal['warnings'] ?? [] as $w)<div>• {{ $w['message'] }}</div>@endforeach
                            @if($changed)
                                <div class="mt-1 fw-semibold">Corrigido pelo utilizador:</div>
                                @foreach($changed as $k => [$before, $after])<div>{{ $k }}: «{{ $before }}» → «{{ $after }}»</div>@endforeach
                            @elseif($item->status === 'confirmada')
                                <div class="mt-1">Guardado sem correções.</div>
                            @endif
                        </details>
                    </td>
                    <td>{{ $item->module ? $item->module . ' / ' . $item->action : '—' }}</td>
                    <td><span class="badge {{ $item->status === 'confirmada' ? 'bg-success' : ($item->status === 'cancelada' ? 'bg-secondary' : 'bg-warning text-dark') }}">{{ $item->status }}</span></td>
                    <td>{{ $item->interpreter_version }}</td>
                    <td>@if($item->record_id)<a href="{{ route('admin.v2.movements.edit', $item->record_id) }}">Movimento #{{ $item->record_id }}</a>@else — @endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Sem interpretações.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
