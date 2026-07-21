@php
    $actionHref   = $actionHref   ?? null;
    $actionLabel  = $actionLabel  ?? null;
    $action2Href  = $action2Href  ?? null;
    $action2Label = $action2Label ?? null;
    $action2Icon  = $action2Icon  ?? 'bi-arrow-right';
    $extraActions = $extraActions ?? [];
    $subtitle     = $subtitle     ?? null;
    $breadcrumbs  = $breadcrumbs  ?? [];
@endphp

<div class="page-header">
    <div class="page-breadcrumb">
        @foreach ($breadcrumbs as $index => $breadcrumb)
        
        @if(isset($breadcrumb['href']) && $breadcrumb['href'])
        <a href="{{ $breadcrumb['href'] }}"><i class="bi {{ $breadcrumb['icon'] }}"></i>{{ $breadcrumb['label'] }}</a>
        @else
        <span><i class="bi {{ $breadcrumb['icon'] }}"></i>{{ $breadcrumb['label'] }}</span>
        @endif

        @if ($index < count($breadcrumbs) - 1)
            <span class="separator">/</span>
            @endif
            @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($actionHref || $action2Href || count($extraActions))
        <div class="d-flex gap-2 flex-wrap">
            @foreach($extraActions as $xa)
            <a href="{{ $xa['href'] }}" class="btn {{ $xa['class'] ?? 'btn-secondary-modern' }}">
                @if(!empty($xa['icon']))<i class="bi {{ $xa['icon'] }} me-1"></i>@endif
                {{ $xa['label'] }}
            </a>
            @endforeach
            @if ($action2Href)
            <a href="{{ $action2Href }}" class="btn btn-secondary-modern">
                <i class="bi {{ $action2Icon }} me-1"></i>
                {{ $action2Label }}
            </a>
            @endif
            @if ($actionHref)
            <a href="{{ $actionHref }}" class="btn btn-primary-modern">
                <i class="bi bi-pencil me-1"></i>
                {{ $actionLabel }}
            </a>
            @endif
        </div>
        @endif
    </div>
</div>