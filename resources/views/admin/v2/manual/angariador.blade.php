@extends('layouts.admin-v2')

@section('title', 'Manual do Angariador')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Manual do Angariador']
    ],
    'title'       => 'Manual do Angariador',
    'subtitle'    => 'Guia completo para trabalhar como angariador Izzycar — consulte sempre que tiver dúvidas',
])

@include('admin.v2.manual._angariador-content', ['publicView' => false])

@endsection
