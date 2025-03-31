@extends('layouts.admin')

@section('main-content')
<h1>Propostas</h1>
<a href="{{ route('proposals.create') }}" class="btn btn-primary">Nova proposta</a>
<table class="table">
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Estado</th>
            <th>Combustível</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($proposals as $proposal)
        <tr>
            <td>{{ $proposal->client->name }}</td>
            <td>{{ $proposal->brand }}</td>
            <td>{{ $proposal->model }}</td>
            <td>{{ $proposal->status }}</td>
            <td>{{ $proposal->fuel }}</td>
            <td>
                <a href="{{ route('proposals.edit', $proposal) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                <form action="{{ route('proposals.destroy', $proposal) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                <a href="{{ route('proposals.downloadPdf', $proposal->id) }}" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                </a>
                <form action="{{ route('proposals.duplicate', $proposal->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-copy"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection