<section class="mk-slide mk-txt mk-faq" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Pergunta frequente'])
    <div class="mk-txt__main">
        @if($content['items'])
        @php $it = $content['items'][0]; @endphp
        <div class="mk-item mk-faq__item">
            @if($it['a'] !== '')<h1 class="mk-faq__q">{{ $it['a'] }}</h1>@endif
            @if($it['b'] !== '')<div class="mk-faq__a">{{ $it['b'] }}</div>@endif
        </div>
        @else
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
