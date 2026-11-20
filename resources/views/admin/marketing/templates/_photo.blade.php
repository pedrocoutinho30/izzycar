{{-- Foto como background (o html2canvas não suporta object-fit). Sem foto: bloco neutro, nunca imagem inventada. --}}
@php $p = $p ?? null; @endphp
<div class="mk-photo {{ $class ?? '' }}" @if($p) style="background-image:url('{{ $p['url'] }}');background-position:{{ $p['x'] }}% {{ $p['y'] }}%" @endif></div>
