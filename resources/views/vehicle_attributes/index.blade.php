@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-primary mb-0">Atributos do Veículo</h1>
        <a href="{{ route('vehicle-attributes.create') }}" class="btn btn-outline-primary shadow-sm">
          <i class="fas fa-plus me-1"></i> Adicionar Atributo
        </a>
      </div>

      @forelse($attributes as $group => $groupAttributes)
      <h4 class="mt-4 mb-2">{{ $group ?: 'Sem Grupo' }}</h4>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-5">
          <thead class="table-dark">
            <tr>
              <th>Ordem</th>
              <th>Nome</th>
              <th>Chave</th>
              <th>Tipo</th>
              <th>Campo AutoScout24</th>
              <th>Campo Mobile.de</th>
              <th>Grupo de Atributos</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="sortable-{{ \Illuminate\Support\Str::slug($group) }}">
            @foreach($groupAttributes as $attr)
            <tr data-id="{{ $attr->id }}">
              <td class="drag-handle text-center" style="cursor: move;">
                <i class="fas fa-arrows-alt-v"></i>
              </td>
              <td>{{ $attr->name }}</td>
              <td>{{ $attr->key }}</td>
              <td>{{ $attr->type }}</td>
              <td>{{ $attr->field_name_autoscout }}</td>
              <td>{{ $attr->field_name_mobile }}</td>

              <td>
                <form action="{{ route('vehicle-attributes.update-group', $attr->id) }}" method="POST" class="d-flex align-items-center">
                  @csrf
                  @method('PATCH')
                  <select name="attribute_group" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    @foreach(['Equipamento', 'Segurança & Desempenho', 'Equipamento Interior', 'Equipamento Exterior', 'Conforto & Multimédia', 'Dados do Veículo','Características Técnicas', 'Outros Extras'] as $attribute_group)
                    <option value="{{ $attribute_group }}" {{ $attr->attribute_group == $attribute_group ? 'selected' : '' }}>
                      {{ ucfirst($attribute_group) }}
                    </option>
                    @endforeach
                  </select>
                </form>
              </td>
              <td>
                <a href="{{ route('vehicle-attributes.edit', $attr->id) }}" class="btn btn-sm btn-outline-secondary me-2">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('vehicle-attributes.destroy', $attr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza que deseja eliminar este atributo?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger ">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
            </tr>

            @endforeach
          </tbody>
        </table>
      </div>
      @empty
      <p class="text-muted">Nenhum atributo encontrado.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    @foreach($attributes as $group => $groupAttributes)
      (function() {
        const groupId = 'sortable-{{ \Illuminate\Support\Str::slug($group ?: '
        sem - grupo ') }}';
        const el = document.getElementById(groupId);
        if (el) {
          new Sortable(el, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function(evt) {
              const order = Array.from(el.querySelectorAll('tr')).map(row => row.dataset.id);
              fetch('{{ route("vehicle-attributes.sort") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                  order: order,
                  group: @json($group)
                })
              }).then(response => {
                if (!response.ok) throw new Error('Erro ao guardar ordem');
                return response.json();
              }).then(data => {
                console.log('Ordem atualizada com sucesso', data);
              }).catch(error => {
                console.error('Erro:', error);
              });
            }
          });
        }
      })();
    @endforeach
  });
</script>
@endpush