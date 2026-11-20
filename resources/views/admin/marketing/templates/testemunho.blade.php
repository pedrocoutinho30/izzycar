@php
    $star = '<svg width="64" height="64" viewBox="0 0 24 24"><polygon points="12,1.8 15,8.6 22.4,9.3 16.8,14.2 18.5,21.5 12,17.7 5.5,21.5 7.2,14.2 1.6,9.3 9,8.6" fill="#B59151"/></svg>';
    $starOff = str_replace('#B59151', 'rgba(255,255,255,.22)', $star);
@endphp
<section class="mk-slide mk-txt mk-quote" style="width:{{ $w }}px;height:{{ $h }}px">
    @include('admin.marketing.templates._txt-head', ['label' => 'Testemunho'])
    <div class="mk-txt__main">
        @if($content['rating'] > 0)
        <div class="mk-stars" style="width:{{ 64 * 5 }}px">
            <div class="mk-stars__row">{!! str_repeat($starOff, 5) !!}</div>
            <div class="mk-stars__fill" style="width:{{ $content['rating_pct'] }}%"><div class="mk-stars__row" style="width:{{ 64 * 5 }}px">{!! str_repeat($star, 5) !!}</div></div>
        </div>
        @endif
        @if($content['quote'] !== '')
        <blockquote class="mk-quote__text q-{{ $content['quote_size'] }}">{{ $content['quote_pre'] ? '…' : '' }}{{ $content['quote'] }}{{ $content['quote_post'] ? '…' : '' }}</blockquote>
        @endif
        @if($content['author'] !== '')
        <div class="mk-quote__by"><b>{{ $content['author'] }}</b>@if($content['origin_label'])<span>{{ $content['origin_label'] }}</span>@endif</div>
        @endif
    </div>
    @include('admin.marketing.templates._txt-foot')
</section>
