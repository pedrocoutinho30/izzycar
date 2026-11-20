@php $dense = $h <= 1350 && count($content['items']) >= 4; @endphp
<section class="mk-slide mk-txt mk-serv{{ $dense ? ' mk-dense' : '' }}" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Serviço'])
    <div class="mk-txt__main">
        @if(!empty($content['photos']))
        @include('admin.marketing.templates._photo', ['p' => $content['photos'][0], 'class' => 'mk-serv__photo'])
        @endif
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
        @if($content['items'])
        <ul class="mk-list mk-serv__list">
            @foreach($content['items'] as $it)
            <li class="mk-item">{{ $it['a'] !== '' ? $it['a'] : $it['b'] }}@if($it['a'] !== '' && $it['b'] !== '')<small>{{ $it['b'] }}</small>@endif</li>
            @endforeach
        </ul>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
