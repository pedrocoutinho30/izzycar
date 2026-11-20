@php $dense = $h <= 1350 && count($content['items']) >= 4; @endphp
<section class="mk-slide mk-txt mk-steps-slide{{ $dense ? ' mk-dense' : '' }}" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Passo a passo'])
    <div class="mk-txt__main">
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['items'])
        <div class="mk-steps">
            @foreach($content['items'] as $i => $it)
            <div class="mk-item mk-step">
                <span class="mk-step__n">{{ $i + 1 }}</span>
                <div>@if($it['a'] !== '')<b>{{ $it['a'] }}</b>@endif @if($it['b'] !== '')<p>{{ $it['b'] }}</p>@endif</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
