@php $total = count($slides); @endphp
@foreach($slides as $i => $slide)
    @if($slide['layout'] === 'cover')
        @include('admin.marketing.templates._txt-cover', ['label' => 'Educativo'])
    @elseif($slide['layout'] === 'item')
        @php $it = $slide['item']; @endphp
        <section class="mk-slide mk-txt mk-edu" style="width:{{ $w }}px;height:{{ $h }}px">
            <div class="mk-txt__main">
                <div class="mk-edu__n">{{ str_pad($slide['n'], 2, '0', STR_PAD_LEFT) }}</div>
                @if($it['a'] !== '')<h2 class="mk-edu__a">{{ $it['a'] }}</h2>@endif
                @if($it['b'] !== '')<p class="mk-edu__b">{{ $it['b'] }}</p>@endif
            </div>
            <div class="mk-txt__foot"><span>{{ $i + 1 }} / {{ $total }}</span><img src="{{ $content['logo'] }}" alt="Izzycar" style="height:80px"></div>
        </section>
    @else
        @include('admin.marketing.templates._txt-cta')
    @endif
@endforeach
