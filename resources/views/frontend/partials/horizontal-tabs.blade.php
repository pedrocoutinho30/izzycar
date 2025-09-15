<div class="row mt-4">
    <h3 class="text-center">{{ $title }}</h3>

    <!-- Tabs horizontais -->
    <div class="col-12">
        <ul class="nav nav-tabs justify-content-center" id="h-tabs-import" role="tablist">
            @foreach ($data as $index => $tab)
                @php
                    $slug = \Illuminate\Support\Str::slug($tab['title']);
                @endphp
                <li  role="presentation">
                    <button
                        class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="tab-import-{{ $slug }}"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-import-{{ $slug }}"
                        type="button"
                        role="tab"
                        aria-controls="pane-import-{{ $slug }}"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $tab['title'] }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Conteúdo -->
    <div class="col-12 ">
        <div class="tab-content" id="h-tabs-content-import">
            @foreach ($data as $index => $tab)
                @php
                    $slug = \Illuminate\Support\Str::slug($tab['title']);
                @endphp
                <div
                    class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                    id="pane-import-{{ $slug }}"
                    role="tabpanel"
                    aria-labelledby="tab-import-{{ $slug }}">
                    <div class="content-tab px-4 text-center">
                        <p class="text-dark mb-4">
                            {!! $tab['content'] ?? '' !!}
                        </p>
                        @if (!empty($tab['image']))
                            <img src="{{ asset('storage/' . $tab['image']) }}" loading="lazy"
                                style="width: 300px; height: 300px; object-fit: cover; border: 3px solid #7f1f1fff;"
                                class="img-fluid rounded shadow-sm mt-3"
                                alt="{{ $tab['title'] }}">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
