@extends('layouts.admin-mobile')

@section('title', 'Início')

@section('content')
    <div class="row g-2">
        <div class="col-6">
            <div class="m-card text-center">
                <div class="fs-1 fw-bold" style="color: var(--admin-primary)">{{ $leadsNovas }}</div>
                <div class="text-muted small">Leads novas</div>
            </div>
        </div>
        <div class="col-6">
            <div class="m-card text-center">
                <div class="fs-1 fw-bold" style="color: var(--admin-primary)">{{ $followupsHoje }}</div>
                <div class="text-muted small">Follow-ups hoje</div>
            </div>
        </div>
    </div>
@endsection
