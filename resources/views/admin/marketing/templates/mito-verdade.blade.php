@php $total = count($slides); @endphp
@foreach($slides as $i => $slide)
    @if($slide['layout'] === 'cover')
        @include('admin.marketing.templates._txt-cover', ['label' => 'Mito ou verdade'])
    @elseif($slide['layout'] === 'item')
        @php $it = $slide['item']; @endphp
        <section class="mk-slide mk-mito" style="width:{{ $w }}px;height:{{ $h }}px">
            @if($it['a'] !== '')
            <div class="mk-mito__top">
                <small>Mito</small>
                <p>{{ $it['a'] }}</p>
            </div>
            @endif
            <div class="mk-mito__bottom">
                @if($it['b'] !== '')
                <small>Verdade</small>
                <p>{{ $it['b'] }}</p>
                @endif
                <div class="mk-mito__n">{{ $i + 1 }} / {{ $total }}</div>
            </div>
        </section>
    @else
        @include('admin.marketing.templates._txt-cta')
    @endif
@endforeach
