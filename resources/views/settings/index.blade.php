@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Configurações</h1>
                <a href="{{ route('settings.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Configuração
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Label</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $setting)
                        <tr>
                            <td>{{ $setting->title }}</td>
                            <td>{{ $setting->label }}</td>
                            <td>{{ $setting->type }}</td>
                            <td>
                                @if($setting->type === 'image' && $setting->value)
                                <img src="{{ asset('storage/'.$setting->value) }}" width="80">
                                @else
                                {{ $setting->value }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('settings.edit', $setting) }}" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('settings.destroy', $setting) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">Nenhuma configuração encontrada</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection