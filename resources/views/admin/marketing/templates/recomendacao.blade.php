<section class="mk-slide mk-reco" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-reco__photo'])
    <div class="mk-ribbon" style="background:var(--gold);color:var(--ink)">{{ $content['badge'] ?: 'Recomendação' }}</div>
    <div class="mk-badge-logo"><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    <div class="mk-reco__body">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['highlights'] || $content['specs'])
        <div class="mk-chips">
            @foreach(array_slice($content['highlights'], 0, 4) as $hl)<span>{{ $hl }}</span>@endforeach
            @if(!$content['highlights'])@foreach(array_slice($content['specs'], 0, 4) as [$k, $v])<span>{{ $v }}</span>@endforeach @endif
        </div>
        @endif
        @if($content['price'] || $content['savings'])
        <div class="mk-reco__prices">
            @if($content['price'])<div class="mk-reco__price">{{ $content['price'] }}</div>@endif
            @if($content['savings'])<div class="mk-reco__save"><small>Poupança</small><b>{{ $content['savings'] }}</b></div>@endif
        </div>
        @endif
        <div class="mk-reco__foot"><span>{{ $content['cta'] }}</span><span style="color:var(--gold)">{{ $content['site'] }}</span></div>
    </div>
</section>
