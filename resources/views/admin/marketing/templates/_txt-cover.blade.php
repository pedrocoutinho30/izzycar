<section class="mk-slide mk-txt mk-cover" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => $label ?? null])
    <div class="mk-txt__main">
        <div class="mk-rule" style="margin-top:0"></div>
        <h1 class="mk-title t-{{ $content['title_size'] }}">{{ $content['title'] }}</h1>
        @if($content['subtitle'])<p class="mk-sub">{{ $content['subtitle'] }}</p>@endif
    </div>
    <div class="mk-txt__foot"><span>Desliza para ver</span><span>{{ $content['site'] }}</span></div>
</section>
