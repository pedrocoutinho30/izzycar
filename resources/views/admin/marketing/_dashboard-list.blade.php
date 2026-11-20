<ul class="list-group list-group-flush">
    @forelse($posts as $p)
    <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
        <div class="text-truncate">
            <a href="{{ route('admin.marketing.editor', $p) }}" class="fw-semibold text-decoration-none">{{ $p->title ?: 'Sem título' }}</a>
            <div class="small text-muted">{{ $p->type->label() }} · {{ $p->status->label() }}@if($showDate && $p->scheduled_for) · {{ $p->scheduled_for->format('d/m/Y H:i') }}@endif</div>
        </div>
        <span class="small text-muted text-nowrap">{{ $p->updated_at->format('d/m H:i') }}</span>
    </li>
    @empty
    <li class="list-group-item text-muted">{{ $empty }}</li>
    @endforelse
</ul>
