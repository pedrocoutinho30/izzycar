<div class="row mt-4">
    <h3 class="text-center">{{$title}}</h3>
    <!-- Tabs verticais -->
    <div class="col-md-3">
        <div class="nav flex-column nav-tabs me-3" id="v-tabs-import" role="tablist" aria-orientation="vertical">
            @foreach ($data as $index => $tab)
            @php
            $slug = \Illuminate\Support\Str::slug($tab['title']);
            @endphp
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
            @endforeach
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="col-md-9">
        <div class="tab-content" id="v-tabs-content-import">
            @foreach ($data as $index => $tab)
            @php
            $slug = \Illuminate\Support\Str::slug($tab['title']);
            @endphp
            <div
                class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                id="pane-import-{{ $slug }}"
                role="tabpanel"
                aria-labelledby="tab-import-{{ $slug }}">
                <div class="content-tab px-4 py-5 text-center">
                    <p class="text-dark mb-4">
                        {!! $tab['content'] ?? '' !!}
                    </p>

                    <img src="{{ asset($tab['image'] ?? 'images/default-car.jpg') }}"  style="max-width: 300px; height: auto;" class="img-fluid rounded shadow-sm mt-3" alt="{{ $tab['title'] }}">
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>