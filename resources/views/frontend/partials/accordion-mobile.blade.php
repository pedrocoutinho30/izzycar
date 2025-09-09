<div class="row mt-4">
    <h3 class="text-center mb-4">{{ $title }}</h3>
    @php
    $accordionId = 'accordion-' . \Illuminate\Support\Str::slug($title) . '-' . uniqid();
    @endphp
    <div class="accordion custom-accordion mt-3" id="{{ $accordionId }}">
        @forelse ($data ?? [] as $key => $item)
        @php
        $slug = \Illuminate\Support\Str::slug($item['title']);
        $headingId = "heading-{$key}-{$slug}";
        $collapseId = "collapse-{$key}-{$slug}-" . uniqid();
        @endphp

        <div class="accordion-item">
            <h2 class="accordion-header" id="{{ $headingId }}">
                <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}">
                    {{ $item['title'] }}
                </button>
            </h2>
            <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                aria-labelledby="{{ $headingId }}"
                data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body text-dark">
                    {!! $item['content'] !!}
                    @if(!empty($item['image']))
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $item['image']) }}"
                            style="width: 150px; height: 150px; object-fit: cover;"
                            class="img-fluid rounded shadow-sm mt-3"
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