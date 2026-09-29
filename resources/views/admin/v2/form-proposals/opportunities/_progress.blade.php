{{-- Progresso da checklist. Espera $progress (App\Support\ChecklistProgress). --}}
<div data-opp-progress title="{{ $progress->pending() ? 'Por confirmar: ' . implode(', ', $progress->pendingLabels) : 'Nada pendente' }}">
    <div class="opp-progress-label">
        <span>Informação: <strong data-role="confirmed">{{ $progress->confirmed }}</strong>/<span data-role="total">{{ $progress->total }}</span> confirmados</span>
        <span data-role="percent">{{ $progress->percent() }}%</span>
    </div>
    <div class="opp-progress">
        <div class="seg-confirmed" data-role="bar-confirmed" style="width: {{ $progress->confirmedWidth() }}%"></div>
        <div class="seg-na" data-role="bar-na" style="width: {{ $progress->notApplicableWidth() }}%"></div>
    </div>
    <div class="text-muted mt-1" style="font-size:.72rem" data-role="breakdown">
        {{ $progress->pending() }} por confirmar @if($progress->notApplicable) · {{ $progress->notApplicable }} N/A @endif
    </div>
</div>
