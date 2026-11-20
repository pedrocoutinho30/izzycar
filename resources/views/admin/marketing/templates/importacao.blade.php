<section class="mk-slide mk-full mk-import" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-full__photo'])
    <div class="mk-full__scrim" style="height:64%"></div>
    <div class="mk-ribbon wine" style="background:var(--wine);color:#fff">{{ $content['badge'] ?: 'Importação concluída' }}</div>
    <div class="mk-badge-logo"><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    <div class="mk-full__body">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['highlights'])
        <div class="mk-chips">@foreach(array_slice($content['highlights'], 0, 4) as $hl)<span>{{ $hl }}</span>@endforeach</div>
        @endif
        @if($content['savings'])
        <div class="mk-reco__prices"><div class="mk-reco__save"><small>Poupança</small><b>{{ $content['savings'] }}</b></div></div>
        @endif
        <div class="mk-import__cta">{{ $content['cta'] }} <span>{{ $content['site'] }}</span></div>
    </div>
</section>
