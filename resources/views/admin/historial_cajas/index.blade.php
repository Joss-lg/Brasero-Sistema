@extends('layouts.admin')

@section('title', 'Historial de Cajas | Ollintem Pro')

@section('content')
<div class="px-4 py-6 sm:p-8 lg:p-10 w-full max-w-[1600px] mx-auto space-y-6 sm:space-y-8 relative z-10 font-sans min-h-screen transition-colors duration-500">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 pb-6" style="border-bottom: 1px solid var(--border-color);">
        <div class="space-y-1.5 sm:space-y-1">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight flex items-center gap-2.5 sm:gap-3" style="color: var(--text-color);">
                <span class="p-2 bg-[#b74309]/10 text-[#b74309] rounded-xl shrink-0">
                    <svg class="w-4.5 h-4.5 sm:w-5 sm:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </span>
                Historial de Turnos
            </h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">
                Auditoría financiera, saldos iniciales, cierres de turno y conciliaciones automáticas.
            </p>
        </div>
        <div class="inline-flex items-center w-fit gap-2 text-[11px] sm:text-xs font-semibold px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl shadow-sm" style="background-color: var(--card-color); border: 1px solid var(--border-color); color: var(--text-muted);">
            <span class="flex h-2 w-2 rounded-full bg-[#b74309] shrink-0"></span>
            Mostrando {{ $turnos->count() }} turnos
        </div>
    </div>

    {{-- VISTA MÓVIL --}}
    <div class="md:hidden space-y-3">
        @forelse($turnos as $turno)
            <div class="rounded-2xl shadow-sm p-4 space-y-3" style="background-color: var(--card-color); border: 1px solid var(--border-color);">

                <div class="flex items-center justify-between">
                    <span class="px-2 py-1 rounded-md font-mono text-[11px] font-bold" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                        #{{ $turno->id }}
                    </span>

                    @if($turno->estado === 'abierta')
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Activa
                        </div>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                            Cerrada · {{ $turno->updated_at->format('h:i A') }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-[#b74309]/10 text-[#b74309] flex items-center justify-center font-bold text-sm uppercase ring-1 ring-[#b74309]/20 shrink-0">
                        {{ substr($turno->user->nombre ?? $turno->user->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-bold text-sm truncate" style="color: var(--text-color);">{{ $turno->user->nombre ?? 'N/A' }}</span>
                        <span class="text-[10px] font-semibold tracking-wider" style="color: var(--text-muted);">ID: {{ $turno->user_id }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold" style="color: var(--text-color);">
                        {{ $turno->created_at->format('d/m/Y') }} · {{ $turno->created_at->format('h:i A') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold border
                        {{ $turno->turno === 'Matutino' ? 'bg-[#b74309]/10 text-[#b74309] border-[#b74309]/20' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20' }}">
                        {{ $turno->turno }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2" style="border-top: 1px solid var(--border-color);">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold uppercase tracking-widest" style="color: var(--text-muted);">Saldo Inicial</p>
                        <p class="font-semibold text-sm" style="color: var(--text-muted);">${{ number_format($turno->monto_inicial, 2) }}</p>
                    </div>
                    <div class="space-y-0.5 text-right">
                        <p class="text-[10px] font-bold uppercase tracking-widest" style="color: var(--text-muted);">Saldo Final</p>
                        @if($turno->estado === 'abierta')
                            <p class="font-bold" style="color: var(--text-muted);">--</p>
                        @else
                            <p class="font-black text-sm" style="color: var(--text-color);">${{ number_format($turno->monto_final_real ?? 0, 2) }}</p>
                        @endif
                    </div>
                </div>

                <a href="{{ route('historial.show', $turno->id) }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-xs font-bold rounded-lg bg-[#b74309] hover:bg-[#8f3207] text-white active:scale-95 shadow-sm transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Ver Detalles
                </a>
            </div>
        @empty
            <div class="rounded-2xl shadow-sm py-12 text-center" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                <div class="flex flex-col items-center justify-center gap-3">
                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center" style="background-color: var(--input-bg); color: var(--text-muted);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold" style="color: var(--text-color);">Sin registros</h3>
                        <p class="text-[11px] font-medium" style="color: var(--text-muted);">No se encontraron turnos en el historial.</p>
                    </div>
                </div>
            </div>
        @endforelse

        @if($turnos->hasPages())
            <div class="pt-2 overflow-x-auto">{{ $turnos->links() }}</div>
        @endif
    </div>

    {{-- VISTA ESCRITORIO --}}
    <div class="hidden md:block w-full rounded-2xl shadow-sm overflow-hidden transition-all" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full pb-2" style="scrollbar-color: var(--border-color) transparent;">
            <table class="w-full min-w-[850px] text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest" style="border-bottom: 1px solid var(--border-color); background-color: var(--input-bg); color: var(--text-muted);">
                        <th class="px-4 sm:px-6 py-4">ID Caja</th>
                        <th class="px-4 sm:px-6 py-4">Fecha</th>
                        <th class="px-4 sm:px-6 py-4">Turno</th>
                        <th class="px-4 sm:px-6 py-4">Empleado</th>
                        <th class="px-4 sm:px-6 py-4 text-center">Estado</th>
                        <th class="px-4 sm:px-6 py-4 text-right">Saldo Inicial</th>
                        <th class="px-4 sm:px-6 py-4 text-right">Saldo Final</th>
                        <th class="px-4 sm:px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm font-medium" style="color: var(--text-muted); divide-color: var(--border-color);">
                    @forelse($turnos as $turno)
                        <tr class="transition-colors duration-200 group hover:bg-[#b74309]/[0.03]" style="border-bottom: 1px solid var(--border-color);">

                            <td class="px-4 sm:px-6 py-4 sm:py-5 font-mono text-xs font-bold">
                                <span class="px-2 py-1 rounded-md" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                    #{{ $turno->id }}
                                </span>
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5 font-semibold" style="color: var(--text-color);">
                                {{ $turno->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-bold w-fit border
                                        {{ $turno->turno === 'Matutino' ? 'bg-[#b74309]/10 text-[#b74309] border-[#b74309]/20' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20' }}">
                                        {{ $turno->turno }}
                                    </span>
                                    <span class="text-[9px] sm:text-[10px] font-medium tracking-wide" style="color: var(--text-muted);">
                                        {{ $turno->created_at->format('h:i A') }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-[#b74309]/10 text-[#b74309] flex items-center justify-center font-bold text-xs sm:text-sm uppercase ring-1 ring-[#b74309]/20">
                                        {{ substr($turno->user->nombre ?? $turno->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs sm:text-sm group-hover:text-[#b74309] transition-colors" style="color: var(--text-color);">{{ $turno->user->nombre ?? 'N/A' }}</span>
                                        <span class="text-[9px] sm:text-[10px] font-semibold mt-0.5 tracking-wider" style="color: var(--text-muted);">ID: {{ $turno->user_id }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5 text-center">
                                @if($turno->estado === 'abierta')
                                    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Activa
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-[11px] font-bold" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                            Cerrada
                                        </span>
                                        <span class="text-[9px] sm:text-[10px] font-medium" style="color: var(--text-muted);">
                                            {{ $turno->updated_at->format('h:i A') }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5 text-right font-semibold" style="color: var(--text-muted);">
                                ${{ number_format($turno->monto_inicial, 2) }}
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5 text-right">
                                @if($turno->estado === 'abierta')
                                    <span class="font-bold tracking-tight" style="color: var(--text-muted);">--</span>
                                @else
                                    <span class="font-black tracking-tight text-sm sm:text-base" style="color: var(--text-color);">
                                        ${{ number_format($turno->monto_final_real ?? 0, 2) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 sm:px-6 py-4 sm:py-5 text-center">
                                <a href="{{ route('historial.show', $turno->id) }}" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-bold rounded-lg bg-[#b74309] hover:bg-[#8f3207] text-white shadow-sm transition-all hover:scale-105 active:scale-95 outline-none focus:ring-2 focus:ring-[#b74309]/40">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Ver Detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 sm:py-20">
                                <div class="flex flex-col items-center justify-center gap-3 sm:gap-4">
                                    <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-2xl flex items-center justify-center" style="background-color: var(--input-bg); color: var(--text-muted);">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="text-sm font-bold" style="color: var(--text-color);">Sin registros</h3>
                                        <p class="text-[11px] sm:text-xs font-medium" style="color: var(--text-muted);">No se encontraron turnos en el historial.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($turnos->hasPages())
            <div class="px-4 sm:px-6 py-4 overflow-x-auto" style="border-top: 1px solid var(--border-color); background-color: var(--input-bg);">
                {{ $turnos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection