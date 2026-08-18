@extends('layouts.admin-v2')

@section('title', 'Pré-Leads (WhatsApp)')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Pré-Leads'],
    ],
    'title' => 'Pré-Leads (WhatsApp)',
    'subtitle' => 'Contactos recebidos via WhatsApp que ainda não são Clientes/Leads — aprova para criar a lead ou rejeita para descartar (spam/publicidade).',
])

<div class="modern-card border-warning">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-whatsapp text-warning"></i> Pendentes</h5>
        <span class="badge bg-warning text-dark rounded-pill">{{ $preLeads->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Telefone</th>
                    <th>Mensagem</th>
                    <th>Recebido</th>
                    <th style="min-width: 360px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($preLeads as $preLead)
                <tr>
                    <td>{{ $preLead->phone }}</td>
                    <td class="text-truncate" style="max-width: 320px;" title="{{ $preLead->message }}">{{ $preLead->message ?? '—' }}</td>
                    <td>{{ $preLead->created_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1 align-items-center">
                            <form action="{{ route('admin.v2.pre-leads.approve', $preLead->id) }}" method="POST" class="d-inline-flex gap-1">
                                @csrf
                                <input type="text" name="name" value="{{ $preLead->name }}" placeholder="Nome" class="form-control form-control-sm" style="width: 160px;" required>
                                <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                            </form>
                            <form action="{{ route('admin.v2.pre-leads.reject', $preLead->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Rejeitar e apagar este pré-lead? Esta ação não pode ser desfeita.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Rejeitar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted p-4">Sem pré-leads pendentes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
