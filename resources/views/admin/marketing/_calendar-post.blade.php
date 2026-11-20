@php $pend = $p->data['pending_selection'] ?? false; @endphp
<a href="{{ route('admin.marketing.editor', $p) }}" class="d-block text-decoration-none rounded border-start border-3 border-{{ $p->status->color() }} bg-body-tertiary px-2 py-1 mb-1 small" title="{{ $p->title }}">
    <span class="text-muted">{{ $p->scheduled_for->format('H:i') }}</span>
    <span class="fw-semibold text-body">{{ $p->title ?: 'Sem título' }}</span>
    <span class="d-block text-muted" style="font-size:.75rem">{{ $p->type->label() }} · {{ $p->channel ? ucfirst($p->channel) : 'sem rede' }}@if($pend) · <b class="text-warning">por selecionar</b>@endif</span>
</a>
