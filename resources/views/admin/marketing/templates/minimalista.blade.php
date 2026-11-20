<section class="mk-slide mk-minimal" style="width:{{ $w }}px;height:{{ $h }}px">
    <img class="mk-logo mk-minimal__logo" src="{{ $content['logo'] }}" alt="Izzycar">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-minimal__photo'])
    <div class="mk-minimal__foot">
        <div>
            <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
            @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        </div>
        @if($content['price'])<div class="mk-price">{{ $content['price'] }}</div>@endif
    </div>
</section>
