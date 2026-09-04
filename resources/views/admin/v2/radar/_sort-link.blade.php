@php
    $isActive = $sort === $field;
    $nextDir = $isActive && $dir === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->except(['sort', 'dir', 'de_page', 'pt_page']), ['sort' => $field, 'dir' => $nextDir]);
@endphp
<a href="{{ route('admin.v2.radar.show', array_merge(['radarSearch' => $radarSearch], $query)) }}" class="text-decoration-none text-reset">
    {{ $label }}
    @if($isActive)
        <i class="bi bi-caret-{{ $dir === 'asc' ? 'up' : 'down' }}-fill small"></i>
    @else
        <i class="bi bi-arrow-down-up small text-muted"></i>
    @endif
</a>
