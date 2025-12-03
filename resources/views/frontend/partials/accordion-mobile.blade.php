@push('styles')
<style>
    .process-accordion-modern {
        max-width: 100%;
    }

    .process-accordion-item {
        background: white;
        border-radius: 16px;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .process-accordion-item:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        border-color: var(--accent-color);
    }

    .process-accordion-header {
        width: 100%;
        background: white;
        border: none;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: left;
    }

    .process-accordion-header:hover {
        background: #f8f9fa;
    }

    .process-accordion-number {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .process-accordion-title {
        flex: 1;
        font-size: 1rem;
        font-weight: 600;
        color: #111;
    }

    .process-accordion-icon {
        flex-shrink: 0;
        color: var(--accent-color);
        transition: transform 0.3s ease;
    }

    .process-accordion-header:not(.collapsed) .process-accordion-icon {
        transform: rotate(180deg);
    }

    .process-accordion-body {
        padding: 0 1.5rem 1.5rem 5rem;
        font-size: 0.95rem;
        line-height: 1.7;
        color: #495057;
    }

    .process-accordion-image {
        text-align: center;
        margin-top: 1.5rem;
    }

    .process-accordion-image img {
        max-width: 200px;
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    @media (max-width: 576px) {
        .process-accordion-header {
            padding: 1rem 1.25rem;
        }

        .process-accordion-number {
            width: 36px;
            height: 36px;
            font-size: 0.85rem;
        }

        .process-accordion-title {
            font-size: 0.95rem;
        }

        .process-accordion-body {
            padding: 0 1.25rem 1.25rem 4rem;
            font-size: 0.9rem;
        }

        .process-accordion-image img {
            max-width: 150px;
            height: 150px;
        }
    }
</style>
@endpush

<div class="row mt-4">
    <div class="col-12">
        <h3 class="text-center section-title-import mb-4">{{ $title }}</h3>
        @php
        $accordionId = 'accordion-' . \Illuminate\Support\Str::slug($title) . '-' . uniqid();
        @endphp
        <div class="process-accordion-modern" id="{{ $accordionId }}">
            @forelse ($data ?? [] as $key => $item)
            @php
            $slug = \Illuminate\Support\Str::slug($item['title']);
            $headingId = "heading-{$key}-{$slug}";
            $collapseId = "collapse-{$key}-{$slug}-" . uniqid();
            @endphp

            <div class="process-accordion-item">
                <button class="process-accordion-header collapsed" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}">
                    <span class="process-accordion-number">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="process-accordion-title">{{ $item['title'] }}</span>
                    <svg class="process-accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="{{ $collapseId }}" class="collapse"
                    aria-labelledby="{{ $headingId }}"
                    data-bs-parent="#{{ $accordionId }}">
                    <div class="process-accordion-body">
                        {!! $item['content'] !!}
                        @if(!empty($item['image']))
                        <div class="process-accordion-image">
                            <img src="{{ asset('storage/' . $item['image']) }}" 
                                 loading="lazy"
                                 onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                                 alt="{{ $item['title'] }}">
                        </div>
                        @else
                        <div class="process-accordion-image">
                            <img src="{{ asset('img/logo-simples.png') }}" 
                                 alt="{{ $item['title'] }}">
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            @endforelse
        </div>
    </div>
</div>