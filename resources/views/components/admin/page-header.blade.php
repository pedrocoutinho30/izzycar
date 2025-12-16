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

        @if ($actionHref)
        <a href="{{ $actionHref }}" class="btn btn-primary-modern">
            <i class="bi bi-plus-lg"></i>
            {{ $actionLabel }}
        </a>
        @endif
    </div>
</div>