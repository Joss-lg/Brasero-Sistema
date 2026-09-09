@extends('layouts.admin')

@section('title', 'Mesas | El Brasero')
@section('header-title', 'Gestión de Mesas')
@section('header-subtitle', 'Supervisión en tiempo real y gestión espacial')

@section('content')
<div class="p-3 sm:p-4 lg:p-6 max-w-[1600px] mx-auto w-full space-y-4 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)] h-[calc(100vh-80px)] overflow-hidden">
        
    {{-- CABECERA --}}
    @include('admin.mesas.header')

    {{-- FILTROS --}}
    @include('admin.mesas.filtros')

    {{-- VISTA MAPA --}}
    @include('admin.mesas.plano-espacial')

    {{-- VISTA LISTA --}}
    @include('admin.mesas.lista')

    {{-- MODALES --}}
    @include('admin.mesas.modales')
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mesas.css') }}">
@endpush