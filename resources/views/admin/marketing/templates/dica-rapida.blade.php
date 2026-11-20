<section class="mk-slide mk-txt mk-dica" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Dica rápida'])
    <div class="mk-txt__main">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['items'])
        @php $it = $content['items'][0]; @endphp
        <div class="mk-rule"></div>
        <div class="mk-item mk-dica__item">
            @if($it['a'] !== '')<b>{{ $it['a'] }}</b>@endif
            @if($it['b'] !== '')<p>{{ $it['b'] }}</p>@endif
        </div>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
