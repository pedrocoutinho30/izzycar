{{-- Badge do estado com dropdown para alterar sem sair da página.
     Espera $opportunity e $formProposal. --}}
@php $status = $opportunity->status; @endphp
<div class="dropdown d-inline-block" data-opp-status
     data-url="{{ route('admin.v2.form-proposals.opportunities.update-status', [$formProposal->id, $opportunity->id]) }}">
    <button type="button" class="opp-status-badge dropdown-toggle text-bg-{{ $status->color() }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi {{ $status->icon() }}"></i><span>{{ $status->label() }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach(\App\Enums\OpportunityStatus::cases() as $option)
        <li>
            <button type="button" class="dropdown-item {{ $option === $status ? 'active' : '' }}"
                    data-value="{{ $option->value }}">
                <i class="bi {{ $option->icon() }} me-1"></i>{{ $option->label() }}
            </button>
        </li>
        @endforeach
    </ul>
</div>
