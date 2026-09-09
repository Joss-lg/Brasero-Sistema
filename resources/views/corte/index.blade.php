@extends('layouts.admin')

@section('title', 'Productos Vendidos')

@section('content')
<div class="p-4 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 flex-1 flex flex-col">

    {{-- Encabezado --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] tracking-tight">Reporte de Productos Vendidos</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">
                Del <span class="font-bold text-[var(--text-color)]">{{ $fechaInicio }}</span>
                al <span class="font-bold text-[var(--text-color)]">{{ $fechaFin }}</span>
                @if($areaFiltro !== 'todas')
                    &mdash; Área: <span class="font-bold text-[var(--text-color)] uppercase">{{ $areaFiltro }}</span>
                @endif
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            {{-- Filtro de fechas --}}
            <form method="GET" action="{{ route('admin.corte.index') }}" class="flex w-full sm:w-auto items-center gap-2">
                <input type="hidden" name="area" value="{{ $areaFiltro }}">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="w-full sm:w-36 h-12 bg-black/5 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-4 text-xs font-bold text-[var(--text-color)] focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                <span class="text-[var(--text-muted)] font-bold">-</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="w-full sm:w-36 h-12 bg-black/5 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-4 text-xs font-bold text-[var(--text-color)] focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                <button type="submit" title="Filtrar"
                    class="bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 text-white w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center transition-all shadow-lg active:scale-95">
                    <i class="fas fa-calendar-alt"></i>
                </button>
            </form>

            {{-- PDF --}}
            <a href="{{ route('admin.corte.pdf', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'area' => $areaFiltro]) }}"
               class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-7 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-blue-600/20 active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    {{-- ── RESUMEN POR ÁREA ──────────────────────────────────────────────── --}}
    @if(isset($resumenPorArea) && $resumenPorArea->isNotEmpty())
    <div class="bg-[var(--card-color)] border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl shadow-sm p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-black text-[var(--text-muted)] uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-chart-pie text-blue-500"></i> Resumen por Área
            </h3>
            <div class="text-right">
                <p class="text-[10px] text-[var(--text-muted)] uppercase tracking-wider">Total general</p>
                <p class="text-lg font-black text-[var(--text-color)]">${{ number_format($totalVentas, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-{{ $resumenPorArea->count() }} gap-3">
            @foreach($resumenPorArea as $area)
                @php
                    $colorBase = match(strtolower($area->area)) {
                        'barra'  => 'indigo',
                        'cocina' => 'orange',
                        default  => 'emerald',
                    };
                    $icono = match(strtolower($area->area)) {
                        'barra'  => 'fa-glass-martini-alt',
                        'cocina' => 'fa-utensils',
                        default  => 'fa-tag',
                    };
                @endphp
                <div class="rounded-2xl border border-{{ $colorBase }}-200 dark:border-{{ $colorBase }}-500/20 bg-{{ $colorBase }}-50 dark:bg-{{ $colorBase }}-500/5 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-{{ $colorBase }}-500/15 flex items-center justify-center">
                            <i class="fas {{ $icono }} text-{{ $colorBase }}-500 text-xs"></i>
                        </div>
                        <span class="text-sm font-black text-[var(--text-color)] uppercase">{{ $area->area }}</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] text-[var(--text-muted)]">Monto</span>
                            <span class="text-base font-black text-{{ $colorBase }}-600 dark:text-{{ $colorBase }}-400">${{ number_format($area->total_monto, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] text-[var(--text-muted)]">Piezas vendidas</span>
                            <span class="text-sm font-bold text-[var(--text-color)]">{{ number_format($area->total_piezas) }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="flex justify-between text-[10px] text-[var(--text-muted)] mb-1">
                                <span>% del total</span>
                                <span class="font-black text-{{ $colorBase }}-500">{{ $area->porcentaje }}%</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-{{ $colorBase }}-200 dark:bg-{{ $colorBase }}-500/20">
                                <div class="h-full rounded-full bg-{{ $colorBase }}-500" style="width: {{ $area->porcentaje }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── BOTONES DE ÁREA ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2">

        {{-- Botón "Todas" --}}
        <a href="{{ route('admin.corte.index', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'area' => 'todas']) }}"
           class="flex items-center gap-2 px-5 h-10 rounded-2xl text-xs font-black uppercase tracking-wider transition-all border
                  {{ $areaFiltro === 'todas'
                      ? 'bg-[var(--text-color)] text-[var(--bg-color)] border-transparent shadow-md'
                      : 'bg-[var(--card-color)] text-[var(--text-muted)] border-[var(--border-color)] hover:border-[var(--text-muted)]' }}">
            <i class="fas fa-layer-group text-[10px]"></i> Todas
        </a>

        {{-- Botón por cada área disponible --}}
        @foreach($areasDisponibles as $area)
            @php
                $areaSlug  = strtolower($area);
                $activo    = strtolower($areaFiltro) === $areaSlug;
                $icono     = match($areaSlug) {
                    'barra'   => 'fa-glass-martini-alt',
                    'cocina'  => 'fa-utensils',
                    default   => 'fa-tag',
                };
                $colorBase = match($areaSlug) {
                    'barra'  => 'indigo',
                    'cocina' => 'orange',
                    default  => 'emerald',
                };
            @endphp
            <a href="{{ route('admin.corte.index', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'area' => $area]) }}"
               class="flex items-center gap-2 px-5 h-10 rounded-2xl text-xs font-black uppercase tracking-wider transition-all border
                      {{ $activo
                          ? "bg-{$colorBase}-500 text-white border-transparent shadow-md shadow-{$colorBase}-500/30"
                          : "bg-[var(--card-color)] text-[var(--text-muted)] border-[var(--border-color)] hover:border-{$colorBase}-400 hover:text-{$colorBase}-500" }}">
                <i class="fas {{ $icono }} text-[10px]"></i>
                {{ $area }}
            </a>
        @endforeach
    </div>

    {{-- ── TABLA ────────────────────────────────────────────────────────── --}}
    <div class="bg-[var(--card-color)] border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl sm:rounded-[2rem] shadow-sm p-4 sm:p-6 lg:p-8 w-full">

        @forelse($ventasPorArea as $area => $productos)
            @php
                $areaSlug  = strtolower($area);
                $icono     = match($areaSlug) { 'barra' => 'fa-glass-martini-alt', 'cocina' => 'fa-utensils', default => 'fa-tag' };
                $colorBase = match($areaSlug) { 'barra' => 'indigo', 'cocina' => 'orange', default => 'emerald' };
                $totalUnidades = $productos->sum('total_vendido');
            @endphp

            <div class="mb-10 last:mb-0">

                {{-- Cabecera del área --}}
                <div class="mb-4 flex items-center justify-between border-b border-[var(--border-color)] pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-{{ $colorBase }}-500/10 flex items-center justify-center text-{{ $colorBase }}-500 border border-{{ $colorBase }}-500/20">
                            <i class="fas {{ $icono }} text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[var(--text-color)] uppercase tracking-tight">{{ $area }}</h3>
                            <p class="text-[10px] text-[var(--text-muted)] font-bold">{{ $productos->count() }} productos · {{ $totalUnidades }} unidades vendidas</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl text-[11px] font-black bg-{{ $colorBase }}-500/10 text-{{ $colorBase }}-500 border border-{{ $colorBase }}-500/20 uppercase">
                        {{ $totalUnidades }} Unidades.
                    </span>
                </div>
    
                {{-- Tabla --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[var(--border-color)]">
                                <th class="pb-3 px-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">#</th>
                                <th class="pb-3 px-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">Producto</th>
                                <th class="pb-3 px-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] text-center">Vendidos</th>
                                <th class="pb-3 px-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] text-right">% del área</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos->sortByDesc('total_vendido') as $i => $item)
                                @php $pct = $totalUnidades > 0 ? round($item->total_vendido / $totalUnidades * 100, 1) : 0; @endphp
                                <tr class="border-b border-[var(--border-color)]/50 hover:bg-[var(--input-bg)] transition-colors">
                                    <td class="py-3 px-4 text-xs text-[var(--text-muted)] font-bold w-10">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4 text-sm font-black text-[var(--text-color)]">{{ $item->producto }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-3 py-1 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl text-xs font-black text-[var(--text-color)]">
                                            {{ $item->total_vendido }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-24 h-1.5 rounded-full bg-[var(--border-color)] overflow-hidden">
                                                <div class="h-full rounded-full bg-{{ $colorBase }}-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-[var(--text-muted)] w-10 text-right">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-[var(--border-color)]">
                                <td colspan="2" class="pt-3 px-4 text-xs font-black text-[var(--text-muted)] uppercase">Total</td>
                                <td class="pt-3 px-4 text-center">
                                    <span class="px-3 py-1 bg-{{ $colorBase }}-500/10 border border-{{ $colorBase }}-500/20 rounded-xl text-xs font-black text-{{ $colorBase }}-500">
                                        {{ $totalUnidades }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="py-16 text-center flex flex-col items-center gap-3">
                <i class="fas fa-inbox text-3xl text-[var(--text-muted)]"></i>
                <p class="text-sm text-[var(--text-muted)] font-bold">No hay productos vendidos en las fechas seleccionadas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection