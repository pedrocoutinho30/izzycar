<section class="mk-slide mk-premium" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-premium__photo'])
    <div class="mk-badge-logo"><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    <div class="mk-premium__body">
        @if($content['specs_line'])<div class="mk-eyebrow">{{ $content['specs_line'] }}</div>@endif
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        <div class="mk-rule"></div>
        <div class="mk-premium__foot">
            <div style="font-size:30px;font-weight:600;opacity:.85">{{ $content['cta'] }}</div>
            @if($content['price'])<div class="mk-price">{{ $content['price'] }}</div>@endif
        </div>
    </div>
</section>
