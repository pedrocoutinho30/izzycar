@php $total = count($slides); @endphp
@foreach($slides as $i => $slide)
    @php $n = $i + 1; $layout = $slide['layout']; @endphp
    @if($layout === 'cover')
        @include('admin.marketing.templates.premium')
    @elseif(isset($slide['photos']))
        @php $ph = $slide['photos']; @endphp
        <section class="mk-slide mk-car-photos" style="width:{{ $w }}px;height:{{ $h }}px">
            @if($layout === 'full')
                <div class="mk-grid"><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[0], 'class' => ''])</div></div>
            @elseif($layout === 'duo')
                <div class="mk-grid mk-grid--col"><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[0]])</div><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[1]])</div></div>
            @elseif($layout === 'trio-left')
                <div class="mk-grid"><div style="flex:1.5">@include('admin.marketing.templates._photo', ['p' => $ph[0]])</div><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[1]])@include('admin.marketing.templates._photo', ['p' => $ph[2]])</div></div>
            @elseif($layout === 'trio-top')
                <div class="mk-grid mk-grid--col"><div style="flex:1.5">@include('admin.marketing.templates._photo', ['p' => $ph[0]])</div><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[1]])@include('admin.marketing.templates._photo', ['p' => $ph[2]])</div></div>
            @else
                <div class="mk-grid mk-grid--col"><div style="flex:1">@include('admin.marketing.templates._photo', ['p' => $ph[0]])@include('admin.marketing.templates._photo', ['p' => $ph[1]])</div><div style="flex:1.5">@include('admin.marketing.templates._photo', ['p' => $ph[2]])</div></div>
            @endif
            <div class="mk-car-bar"><div><b>{{ $content['title'] }}</b>@if($content['subtitle'])<small>{{ $content['subtitle'] }}</small>@endif</div><div style="display:flex;align-items:center;gap:30px"><span class="n">{{ $n }} / {{ $total }}</span><img src="{{ $content['logo'] }}" alt="Izzycar"></div></div>
        </section>
    @elseif($layout === 'equipment')
        <section class="mk-slide mk-car-equip" style="width:{{ $w }}px;height:{{ $h }}px">
            <div class="mk-car-text"><div class="mk-eyebrow">{{ $content['title'] }}</div><h2>Equipamento</h2><div class="mk-rule"></div>
                <ul class="mk-list">@foreach(array_slice($content['equipment'], 0, 8) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            <div class="mk-car-bar"><div></div><div style="display:flex;align-items:center;gap:30px"><span class="n">{{ $n }} / {{ $total }}</span><img src="{{ $content['logo'] }}" alt="Izzycar"></div></div>
        </section>
    @elseif($layout === 'specs')
        <section class="mk-slide mk-car-specs" style="width:{{ $w }}px;height:{{ $h }}px">
            <div class="mk-car-text"><div class="mk-eyebrow" style="color:var(--gold-deep)">{{ $content['title'] }}</div><h2>Características</h2>
                <dl>@foreach($content['specs'] as [$k, $v])<div class="row"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>@endforeach</dl></div>
            <div class="mk-car-bar"><div></div><div style="display:flex;align-items:center;gap:30px"><span class="n" style="color:var(--gold-deep)">{{ $n }} / {{ $total }}</span><img src="{{ $content['logo'] }}" alt="Izzycar"></div></div>
        </section>
    @else
        <section class="mk-slide mk-car-cta" style="width:{{ $w }}px;height:{{ $h }}px">
            <img src="{{ $content['logo'] }}" alt="Izzycar">
            <h2>{{ $content['cta'] }}</h2>
            @if($content['price'])<div class="price">{{ $content['price'] }}</div>@endif
            <p>{{ $content['site'] }}</p>
        </section>
    @endif
@endforeach
