@php $dense = $h <= 1350 && count($content['items']) >= 4; @endphp
<section class="mk-slide mk-txt mk-erros{{ $dense ? ' mk-dense' : '' }}" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Erros comuns'])
    <div class="mk-txt__main">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['items'])
        <div class="mk-steps">
            @foreach($content['items'] as $it)
            <div class="mk-item mk-step mk-step--x">
                <span class="mk-step__n">×</span>
                <div>@if($it['a'] !== '')<b>{{ $it['a'] }}</b>@endif @if($it['b'] !== '')<p>{{ $it['b'] }}</p>@endif</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
