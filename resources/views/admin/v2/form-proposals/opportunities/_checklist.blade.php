{{-- Uma checklist (base, elétrico, ...) com itens de 3 estados. Espera
     $checklist, $checklistStatuses (item_id => ChecklistItemStatus), $url. --}}
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title mb-0"><i class="bi bi-list-check"></i> {{ $checklist->name }}</h5>
        <span class="small text-muted d-none d-xl-inline text-nowrap">
            @foreach(\App\Enums\ChecklistItemStatus::cases() as $option)
                <i class="bi {{ $option->icon() }} ms-2"></i> {{ $option->label() }}
            @endforeach
        </span>
    </div>

    @foreach($checklist->items as $item)
        @php $current = $checklistStatuses[$item->id] ?? \App\Enums\ChecklistItemStatus::Pending; @endphp
        <div class="opp-checklist-item" data-status="{{ $current->value }}">
            <span class="opp-checklist-label">
                <i class="bi {{ $current->icon() }}"></i> {{ $item->label }}
            </span>
            <div class="opp-tristate" role="group" aria-label="{{ $item->label }}" data-opp-tristate data-url="{{ $url }}" data-item="{{ $item->id }}">
                @foreach(\App\Enums\ChecklistItemStatus::cases() as $option)
                <button type="button" class="{{ $option === $current ? 'active' : '' }}"
                        data-value="{{ $option->value }}" data-icon="{{ $option->icon() }}" title="{{ $option->label() }}">
                    <i class="bi {{ match($option) {
                        \App\Enums\ChecklistItemStatus::Pending => 'bi-dash',
                        \App\Enums\ChecklistItemStatus::Confirmed => 'bi-check-lg',
                        \App\Enums\ChecklistItemStatus::NotApplicable => 'bi-x-lg',
                    } }}"></i>
                </button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
