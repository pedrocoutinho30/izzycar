<section class="mk-slide mk-full" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-full__photo'])
    <div class="mk-full__scrim"></div>
    <div class="mk-ribbon gold">{{ $content['badge'] ?: 'Novo em stock' }}</div>
    <div class="mk-badge-logo"><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    <div class="mk-full__body">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['specs_line'])<div class="mk-full__meta">{{ $content['specs_line'] }}</div>@endif
        @if($content['price'])<div class="mk-price">{{ $content['price'] }}</div>@endif
    </div>
</section>
