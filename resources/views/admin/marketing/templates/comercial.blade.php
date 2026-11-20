<section class="mk-slide mk-comercial" style="width:{{ $w }}px;height:{{ $h }}px">
    <div class="mk-comercial__photo">
        @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-comercial__bg'])
        @if($content['price'])<div class="mk-comercial__tag"><small>Preço</small><b>{{ $content['price'] }}</b></div>@endif
    </div>
    <div class="mk-badge-logo"><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    <div class="mk-comercial__body">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['highlights'])
        <ul class="mk-list">@foreach(array_slice($content['highlights'], 0, 4) as $hl)<li>{{ $hl }}</li>@endforeach</ul>
        @elseif($content['specs_line'])
        <p class="mk-sub" style="color:var(--wine);font-weight:600">{{ $content['specs_line'] }}</p>
        @endif
    </div>
    <div class="mk-comercial__cta"><span>{{ $content['cta'] }}</span><span>{{ $content['site'] }}</span></div>
</section>
