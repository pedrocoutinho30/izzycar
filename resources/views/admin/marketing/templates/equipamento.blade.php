<section class="mk-slide mk-equip" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._photo', ['p' => $content['photos'][0] ?? null, 'class' => 'mk-equip__photo'])
    <div class="mk-equip__body">
        <div class="mk-eyebrow">{{ $content['highlights'] ? 'Equipamento em destaque' : 'Características' }}</div>
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        <ul class="mk-list">
            @if($content['highlights'])
                @foreach($content['highlights'] as $hl)<li>{{ $hl }}</li>@endforeach
            @else
                @foreach($content['specs'] as [$k, $v])<li>{{ $k }}: {{ $v }}</li>@endforeach
            @endif
        </ul>
        <div class="mk-equip__foot"><span>{{ $content['cta'] }}</span><img src="{{ $content['logo'] }}" alt="Izzycar"></div>
    </div>
</section>
