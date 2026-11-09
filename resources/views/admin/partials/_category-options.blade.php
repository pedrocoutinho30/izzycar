{{--
    <option> das categorias de movimentos.
      $selected  valor atual (opcional)
      $modo      'form' (omissão): um grupo por tipo, filtrado pelo tipo escolhido (JS abaixo);
                 'filtro': lista única sem repetidos, mais as categorias antigas.
--}}
@php
    $selected = $selected ?? null;
    $modo = $modo ?? 'form';
    $porTipo = \App\Models\Expense::categoryGroupsByType();
    $escolhiveis = \App\Models\Expense::selectableCategories();
    $antigas = \App\Models\Expense::categories();
@endphp

@if($modo === 'filtro')
    @php $vistas = []; @endphp
    @foreach($porTipo as $tipo => $grupos)
        @foreach($grupos as $grupo => $itens)
            @php $itens = array_diff_key($itens, $vistas); $vistas += $itens; @endphp
            @if($itens)
                <optgroup label="{{ $tipo === 'income' ? 'Receitas · ' : 'Despesas · ' }}{{ $grupo }}">
                    @foreach($itens as $valor => $nome)
                        <option value="{{ $valor }}" @selected($selected === $valor)>{{ $nome }}</option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
    @endforeach
    @php $velhas = array_diff_key($antigas, $vistas); @endphp
    @if($velhas)
        <optgroup label="Categorias antigas">
            @foreach($velhas as $valor => $nome)
                <option value="{{ $valor }}" @selected($selected === $valor)>{{ $nome }}</option>
            @endforeach
        </optgroup>
    @endif
@else
    @foreach($porTipo as $tipo => $grupos)
        @foreach($grupos as $grupo => $itens)
            <optgroup label="{{ $grupo }}" data-type="{{ $tipo }}">
                @foreach($itens as $valor => $nome)
                    <option value="{{ $valor }}" @selected($selected === $valor)>{{ $nome }}</option>
                @endforeach
            </optgroup>
        @endforeach
    @endforeach
    {{-- Categoria antiga de um movimento existente: mantém-se enquanto não for mudada. --}}
    @if($selected && ! isset($escolhiveis[$selected]))
        <option value="{{ $selected }}" selected data-legacy="1">{{ $antigas[$selected] ?? $selected }} (atual)</option>
    @endif

    @once
        <script>
        // As categorias dependem do tipo (despesa/receita): mostra só as do tipo escolhido.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('select[name="category"]').forEach(function (cat) {
                if (!cat.querySelector('optgroup[data-type]')) return;
                var form = cat.closest('form');
                var tipo = form && form.querySelector('[name="movement_type"]');
                if (!tipo) return;

                function aplicar(limpar) {
                    cat.querySelectorAll('optgroup[data-type]').forEach(function (g) {
                        var ativo = g.dataset.type === tipo.value;
                        g.hidden = !ativo;
                        g.disabled = !ativo;
                    });
                    var atual = cat.options[cat.selectedIndex];
                    var invalida = atual && atual.value !== '' && !atual.dataset.legacy
                        && atual.parentElement.tagName === 'OPTGROUP' && atual.parentElement.disabled;
                    if (limpar && invalida) cat.value = '';
                }
                tipo.addEventListener('change', function () { aplicar(true); });
                aplicar(false);
                // Sem escolha prévia: não deixar selecionada uma categoria do outro tipo.
                var inicial = cat.options[cat.selectedIndex];
                if (inicial && inicial.value !== '' && !inicial.dataset.legacy && inicial.parentElement.disabled) cat.value = '';
            });
        });
        </script>
    @endonce
@endif
