@extends('layouts.admin')

@section('title', 'Caja | El Brasero')

@section('content')
@php
    // $mesasLibres ahora llega desde CajaController: ya NO se puede calcular
    // aquí porque $mesas viene filtrada y solo trae las mesas con cuenta
    // abierta, así que este conteo siempre daría 0.
    $mesasLibres = $mesasLibres ?? 0;
@endphp

<div id="toastContainer" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-8 sm:bottom-8 z-[9999] flex flex-col gap-3 items-center sm:items-end" aria-live="polite" aria-atomic="true"></div>

{{-- Contenedor principal --}}
<div class="px-3 py-4 sm:px-4 sm:py-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-5 sm:space-y-8 relative z-10 font-sans overflow-x-hidden min-h-screen transition-colors duration-300" style="background-color: var(--bg-color);">
    
    {{-- ALERTAS DE SESIÓN --}}
    @if(session('error'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- HEADER Y PANEL FINANCIERO --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5 sm:gap-6 w-full">
        <div class="space-y-2.5 sm:space-y-3 w-full xl:w-auto flex flex-col sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-start">
            <div class="w-full">
                <div class="inline-flex items-center gap-2 rounded-full px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-sm transition-colors text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/20 max-w-full flex-wrap">
                    <span class="h-2 w-2 rounded-full bg-[#b74309] animate-pulse shrink-0"></span>
                    <span class="truncate">Panel Financiero [Turno: {{ $cajaActiva->turno ?? 'N/A' }}]</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tighter break-words mt-1" style="color: var(--text-color);">Panel de Caja</h1>
            </div>
        </div>
        
        {{-- TARJETAS ESTADÍSTICAS --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 w-full xl:w-auto">
            <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border shadow-sm flex flex-col justify-center w-full transition-colors duration-300"
                 style="background-color: var(--card-color); border-color: var(--border-color);">
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest" style="color: var(--text-muted);">Mesas activas</p>
                <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter" style="color: var(--text-color);" id="mesas-activas-display">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border shadow-sm flex flex-col justify-center w-full transition-colors duration-300"
                 style="background-color: var(--card-color); border-color: var(--border-color);">
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest" style="color: var(--text-muted);">Mesas libres</p>
                <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter" style="color: var(--text-muted);" id="mesas-libres-display">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6 pt-1 sm:pt-4 w-full" id="mesas-container">
        @include('admin.caja.partials.mesas')
    </div>
</div>

<script>
    window.mostrarToast = function(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        const typeClasses = type === 'success' ? 'border-l-4 border-emerald-500' : 'border-l-4 border-rose-500';

        toast.className = `w-full sm:min-w-[300px] sm:w-auto p-4 rounded-2xl border shadow-xl flex items-center gap-3 opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-5 transition-all duration-300 ${typeClasses}`;
        toast.style.backgroundColor = 'var(--card-color)';
        toast.style.borderColor = 'var(--border-color)';
        
        toast.innerHTML = `<div><strong class="block text-sm font-bold" style="color: var(--text-color);">${type === 'success' ? 'Éxito' : 'Error'}</strong><span class="text-xs" style="color: var(--text-muted);">${message}</span></div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => { toast.classList.remove('opacity-0', 'translate-y-3', 'sm:translate-x-5'); }, 50);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-3', 'sm:translate-x-5');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    document.addEventListener('DOMContentLoaded', function () {
        const botonesFiltro = document.querySelectorAll('[data-filter]');
        let filtroActivo = 'all';

        const aplicarFiltro = () => {
            document.querySelectorAll('[data-mesa-status]').forEach(card => {
                const coincide = filtroActivo === 'all' || card.dataset.mesaStatus === filtroActivo;
                card.style.display = coincide ? 'flex' : 'none';
                card.style.opacity = coincide ? '1' : '0';
            });
        };

        botonesFiltro.forEach(boton => {
            boton.addEventListener('click', () => {
                botonesFiltro.forEach(b => b.classList.remove('filter-button--active'));
                boton.classList.add('filter-button--active');
                filtroActivo = boton.dataset.filter;
                aplicarFiltro();
            });
        });

        // ---------------------------------------------------------------
        // AUTO-REFRESCO
        // ---------------------------------------------------------------
        const URL_API_MESAS = @json(route('admin.caja.api.mesas'));
        let refrescando = false;

        async function actualizarMesas() {
            if (refrescando || document.hidden) return;
            refrescando = true;

            try {
                const res = await fetch(URL_API_MESAS, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    console.error('apiMesas falló:', res.status);
                    return;
                }

                const data = await res.json();

                if (data && data.caja_cerrada) {
                    window.location.reload();
                    return;
                }

                if (!data || !data.success) {
                    console.error('apiMesas respondió sin success:', data);
                    return;
                }

                const contenedor = document.getElementById('mesas-container');
                if (contenedor && contenedor.innerHTML.trim() !== data.html.trim()) {
                    contenedor.innerHTML = data.html;
                    aplicarFiltro();
                }

                const setTexto = (id, valor) => {
                    const el = document.getElementById(id);
                    if (el && el.innerText !== String(valor)) el.innerText = valor;
                };
                setTexto('total-abierto-display', data.totalAbierto);
                setTexto('mesas-activas-display', data.mesasActivas);
                setTexto('mesas-libres-display', data.mesasLibres);

            } catch (err) {
                console.error('Error actualizando mesas de caja:', err);
            } finally {
                refrescando = false;
            }
        }

        setInterval(actualizarMesas, 5000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) actualizarMesas();
        });
    });
</script>
@endsection