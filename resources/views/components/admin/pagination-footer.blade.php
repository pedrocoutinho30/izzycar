@if ($items->total() > 0)
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
        <div class="text-muted">
            Mostrando {{ $items->firstItem() }} a {{ $items->lastItem() }}
            de {{ $items->total() }} {{ $label }}
        </div>

        <div>
            {{ $items->links() }}
        </div>
    </div>
@endif
