@extends('layouts.admin')

@section('title', 'Detalle de Turno | El Brasero')

@section('content')
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1400px] mx-auto space-y-6 relative z-10 font-sans min-h-screen transition-colors duration-300">

    {{-- Botón de Regresar y Encabezado --}}
    <div class="flex flex-col gap-3 pb-5" style="border-bottom: 1px solid var(--border-color);">
        <div>
            <a href="{{ route('historial.index') }}" class="inline-flex items-center gap-2 text-xs font-bold transition-colors hover:text-[#b74309]" style="color: var(--text-muted);">
                ← Volver al historial
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-2" style="color: var(--text-color);">
                    Auditoría de Caja #{{ $turno->id }}
                </h1>
                <p class="text-xs sm:text-sm font-medium mt-1" style="color: var(--text-muted);">
                    Detalles específicos del flujo financiero capturado en este turno.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($turno->estado === 'abierta')
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 animate-pulse border border-emerald-200/50 dark:border-emerald-800/30">
                        ● Caja Activa
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                        Turno Cerrado
                    </span>
                @endif

                <a href="{{ route('historial.pdf', $turno->id) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-[#b74309] hover:bg-[#8f3207] text-white transition-colors shadow-sm">
                    <i class="fas fa-file-pdf"></i> Ver PDF
                </a>

                <a href="{{ route('historial.pdf', ['id' => $turno->id, 'descargar' => 1]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-colors hover:border-[#b74309] hover:text-[#b74309]" style="border: 1px solid var(--border-color); color: var(--text-color);">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
    </div>

    {{-- Grid de Información General --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Tarjeta 1: Datos de Apertura --}}
        <div class="p-6 rounded-3xl space-y-4" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <h3 class="text-xs font-bold uppercase tracking-widest" style="color: var(--text-muted);">Datos de Apertura</h3>
            <div class="space-y-3">
                <div>
                    <span class="block text-[11px] uppercase font-bold" style="color: var(--text-muted);">Empleado Responsable</span>
                    <span class="text-sm font-bold" style="color: var(--text-color);">{{ $turno->user->nombre ?? $turno->user->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase font-bold" style="color: var(--text-muted);">Turno Asignado</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-[#b74309]/10 text-[#b74309] border border-[#b74309]/20 mt-0.5">
                        {{ $turno->turno }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase font-bold" style="color: var(--text-muted);">Fecha y Hora Apertura</span>
                    <span class="text-xs font-semibold" style="color: var(--text-color);">{{ $turno->created_at->format('d/m/Y - h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Conciliación --}}
        <div class="p-6 rounded-3xl space-y-4" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <h3 class="text-xs font-bold uppercase tracking-widest" style="color: var(--text-muted);">Conciliación de Saldos</h3>
            <div class="space-y-3">
                @php
                    $esperadoTurno = (float) ($turno->monto_final_esperado ?? 0);
                    $contadoTurno  = (float) ($turno->monto_final_real ?? 0);
                    $difTurno = $turno->diferencia !== null
                        ? (float) $turno->diferencia
                        : round($contadoTurno - $esperadoTurno, 2);
                @endphp

                <div class="flex justify-between items-center pb-1.5" style="border-bottom: 1px solid var(--border-color);">
                    <span class="text-xs font-medium" style="color: var(--text-muted);">Fondo Inicial:</span>
                    <span class="text-xs font-bold" style="color: var(--text-color);">${{ number_format($turno->monto_inicial, 2) }}</span>
                </div>

                @if($turno->estado === 'cerrada')
                    <div class="flex justify-between items-center pb-1.5" style="border-bottom: 1px solid var(--border-color);">
                        <span class="text-xs font-medium" style="color: var(--text-muted);">Debía haber en caja:</span>
                        <span class="text-xs font-bold" style="color: var(--text-color);">${{ number_format($esperadoTurno, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-1.5" style="border-bottom: 1px solid var(--border-color);">
                        <span class="text-xs font-medium" style="color: var(--text-muted);">Efectivo contado:</span>
                        <span class="text-xs font-bold" style="color: var(--text-color);">${{ number_format($contadoTurno, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-bold" style="color: var(--text-color);">Resultado:</span>
                        @if(abs($difTurno) < 0.01)
                            <span class="text-xs font-bold text-emerald-500">✓ Caja Cuadrada</span>
                        @elseif($difTurno < 0)
                            <span class="text-xs font-bold text-red-500">⚠ Faltante: ${{ number_format(abs($difTurno), 2) }}</span>
                        @else
                            <span class="text-xs font-bold text-amber-500">⚠ Sobrante: ${{ number_format($difTurno, 2) }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-center pt-2 text-xs font-semibold italic" style="color: var(--text-muted);">
                        El balance final se calculará al cerrar el turno.
                    </div>
                @endif
            </div>
        </div>

        {{-- Tarjeta 3: Notas --}}
        <div class="p-6 rounded-3xl flex flex-col justify-between" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest mb-3" style="color: var(--text-muted);">Notas / Observaciones</h3>
                <p class="text-xs font-medium leading-relaxed p-3 rounded-2xl min-h-[75px]" style="color: var(--text-muted); background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    {{ $turno->observaciones ?? 'Sin comentarios ni incidentes reportados en este turno.' }}
                </p>
            </div>
            @if($turno->estado === 'cerrada')
                <span class="text-[10px] font-semibold block text-right mt-2" style="color: var(--text-muted);">
                    Cierre procesado a las: {{ $turno->updated_at->format('h:i A') }}
                </span>
            @endif
        </div>
    </div>

    {{-- FRANJA: Resumen de Turno --}}
    <div class="rounded-2xl shadow-xl p-4 sm:p-5 relative overflow-hidden w-full" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-[#b74309] to-[#e8946a]"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-4 flex-wrap">
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center whitespace-nowrap" style="color: var(--text-muted);">
                    <i class="fas fa-cash-register text-[#b74309] mr-2"></i> Resumen de Turno
                </h3>
                <span class="text-[10px] sm:text-xs font-bold" style="color: var(--text-muted);">ID Caja: <span style="color: var(--text-color);">#{{ $turno->id }}</span></span>
                <span class="text-[10px] sm:text-xs font-bold" style="color: var(--text-muted);">Cajero: <span style="color: var(--text-color);">{{ $turno->user->nombre ?? $turno->user->name ?? 'N/A' }}</span></span>
                <span class="px-2.5 py-0.5 rounded-md text-[10px] sm:text-xs font-bold bg-[#b74309]/10 border border-[#b74309]/20 text-[#b74309] uppercase tracking-wider">
                    {{ $turno->turno }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--text-muted);">Saldo Inicial</span>
                <span class="font-black text-sm sm:text-base" style="color: var(--text-color);">${{ number_format($turno->monto_inicial, 2) }}</span>
            </div>
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-money-bill-wave text-emerald-500 mr-1 w-3"></i> Efectivo
                </span>
                <span class="font-black text-emerald-500 text-sm sm:text-base">+${{ number_format($ventasEfectivo, 2) }}</span>
            </div>
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-credit-card text-sky-500 mr-1 w-3"></i> Tarjeta
                </span>
                <span class="font-black text-sky-500 text-sm sm:text-base">+${{ number_format($ventasTarjeta, 2) }}</span>
            </div>
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-university text-indigo-500 mr-1 w-3"></i> Transf.
                </span>
                <span class="font-black text-indigo-500 text-sm sm:text-base">+${{ number_format($ventasTransferencia, 2) }}</span>
            </div>
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-minus-circle text-rose-500 mr-1 w-3"></i> Gastos
                </span>
                <span class="font-black text-rose-500 text-sm sm:text-base">-${{ number_format($totalGastos, 2) }}</span>
            </div>
            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner col-span-2 sm:col-span-1 bg-[#b74309]/10 border border-[#b74309]/30">
                <span class="text-[9px] sm:text-[10px] font-black text-[#b74309]/80 uppercase tracking-widest mb-1">Saldo Estimado</span>
                <span class="font-black text-[#b74309] text-base sm:text-lg">${{ number_format($saldoEstimado, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- BLOQUE 1: Ventas del Turno --}}
    <div class="rounded-2xl shadow-xl overflow-hidden w-full" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="p-3 sm:p-4 flex flex-wrap gap-2 justify-between items-center w-full" style="background: linear-gradient(to right, rgba(183,67,9,0.08), transparent); border-bottom: 1px solid var(--border-color);">
            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center" style="color: var(--text-color);">
                <i class="fas fa-shopping-cart text-[#b74309] mr-2"></i> Ventas del Turno
            </h3>
            <span class="text-[10px] sm:text-xs font-black bg-[#b74309]/10 text-[#b74309] px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-[#b74309]/20 whitespace-nowrap">
                Total: ${{ number_format($totalVentas, 2) }}
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            @if($historicoVentas->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                    <i class="fas fa-inbox text-2xl sm:text-3xl mb-3" style="color: var(--text-muted);"></i>
                    <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">No hay ventas registradas en este turno.</p>
                </div>
            @else
                @php
                    $ventasAgrupadas = $historicoVentas->groupBy(fn($v) => $v->flujoable_id ?? 'sin-orden-'.$v->id);
                @endphp
                <table class="w-full text-xs sm:text-sm text-center border-collapse">
                    <thead>
                        <tr class="text-[10px] sm:text-xs font-bold uppercase tracking-wider" style="background-color: var(--input-bg); border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Folio</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Concepto</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Método(s) de Pago</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Total</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4"></th>
                        </tr>
                    </thead>
                    <tbody style="color: var(--text-color);">
                        @foreach($ventasAgrupadas as $ordenId => $pagos)
                            @php
                                $primera     = $pagos->first();
                                $totalFila   = $pagos->sum('monto');
                                $esMixto     = $pagos->count() > 1;
                                $ordenIdReal = $primera->flujoable_id;
                            @endphp
                            <tr class="transition-colors hover:bg-[#b74309]/[0.03]" style="border-bottom: 1px solid var(--border-color);">
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium whitespace-nowrap" style="color: var(--text-muted);">
                                    {{ \Carbon\Carbon::parse($primera->fecha)->format('H:i') }} hrs
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-center">
                                    @if($primera->folio_ticket ?? null)
                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-black font-mono tracking-wider" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                            #{{ str_pad($primera->folio_ticket, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @else
                                        <span style="color: var(--border-color);">—</span>
                                    @endif
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 font-semibold">{{ $primera->concepto }}</td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        @if($esMixto)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 border border-amber-500/20 text-amber-500 uppercase tracking-wide whitespace-nowrap">
                                                <i class="fas fa-layer-group text-[8px] mr-0.5"></i> Pago mixto
                                            </span>
                                            @foreach($pagos as $pago)
                                                @php
                                                    $colorMetodo = match($pago->metodo_pago) {
                                                        'tarjeta'       => 'sky',
                                                        'transferencia' => 'indigo',
                                                        'descuento'     => 'violet',
                                                        default          => 'emerald',
                                                    };
                                                @endphp
                                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider bg-{{ $colorMetodo }}-500/10 border border-{{ $colorMetodo }}-500/20 text-{{ $colorMetodo }}-500 uppercase">
                                                        {{ $pago->metodo_pago }}
                                                    </span>
                                                    <span class="text-[10px] font-bold" style="color: var(--text-muted);">${{ number_format($pago->monto, 2) }}</span>
                                                    @if(!empty($pago->referencia))
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-zinc-500/10 border border-zinc-500/20 text-zinc-400">
                                                            #{{ $pago->referencia }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            @php
                                                $colorMetodo = match($primera->metodo_pago) {
                                                    'tarjeta'       => 'sky',
                                                    'transferencia' => 'indigo',
                                                    'descuento'     => 'violet',
                                                    default          => 'emerald',
                                                };
                                            @endphp
                                            <span class="px-2 sm:px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-black tracking-wider bg-{{ $colorMetodo }}-500/10 border border-{{ $colorMetodo }}-500/20 text-{{ $colorMetodo }}-500 uppercase whitespace-nowrap">
                                                {{ $primera->metodo_pago }}
                                            </span>
                                            @if(!empty($primera->referencia))
                                                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-500/10 border border-zinc-500/20 text-zinc-400 uppercase tracking-wide whitespace-nowrap shadow-inner">
                                                    <i class="fas fa-hashtag text-[8px] text-zinc-500 mr-0.5"></i>Ref: {{ $primera->referencia }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 font-black text-emerald-500 whitespace-nowrap">
                                    +${{ number_format($totalFila, 2) }}
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($ordenIdReal)
                                            <button type="button"
                                               class="btn-imprimir-directo w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:text-[#b74309] hover:border-[#b74309] cursor-pointer"
                                               style="border: 1px solid var(--border-color); color: var(--text-muted);"
                                               data-url="{{ route('admin.caja.ticket.imprimir.orden', $ordenIdReal) }}"
                                               title="Imprimir ticket">
                                                <i class="fas fa-print text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- BLOQUE 2: Gastos y Salidas --}}
    <div class="rounded-2xl shadow-xl overflow-hidden w-full" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="p-3 sm:p-4 flex flex-wrap gap-2 justify-between items-center w-full" style="background: linear-gradient(to right, rgba(244,63,94,0.08), transparent); border-bottom: 1px solid var(--border-color);">
            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center" style="color: var(--text-color);">
                <i class="fas fa-hand-holding-usd text-rose-500 mr-2"></i> Gastos y Salidas
            </h3>
            <span class="text-[10px] sm:text-xs font-black bg-rose-500/10 text-rose-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-rose-500/20 whitespace-nowrap">
                Total: ${{ number_format($totalGastos, 2) }}
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            @if($historicoGastos->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                    <i class="fas fa-receipt text-2xl sm:text-3xl mb-3" style="color: var(--text-muted);"></i>
                    <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">No hay gastos o salidas registrados en este turno.</p>
                </div>
            @else
                <table class="w-full text-xs sm:text-sm text-center border-collapse">
                    <thead>
                        <tr class="text-[10px] sm:text-xs font-bold uppercase tracking-wider" style="background-color: var(--input-bg); border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Categoría</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4 text-left">Concepto / Descripción</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Monto</th>
                        </tr>
                    </thead>
                    <tbody style="color: var(--text-color);">
                        @foreach($historicoGastos as $gasto)
                            <tr class="transition-colors hover:bg-[#b74309]/[0.03]" style="border-bottom: 1px solid var(--border-color);">
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium whitespace-nowrap" style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($gasto->fecha)->format('H:i') }} hrs</td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold bg-rose-500/10 border border-rose-500/20 text-rose-500 uppercase tracking-wide whitespace-nowrap">
                                        {{ $gasto->categoria }}
                                    </span>
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-left font-medium">
                                    <span class="font-semibold block">{{ $gasto->concepto }}</span>
                                    @if($gasto->observaciones)
                                        <span class="text-[10px] sm:text-xs block mt-0.5" style="color: var(--text-muted);">{{ $gasto->observaciones }}</span>
                                    @endif
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 font-black text-rose-500 whitespace-nowrap">-${{ number_format($gasto->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

{{-- Iframe invisible para procesar la impresión directa en la misma ventana --}}
<iframe id="iframeImpresionDirecta" class="hidden" style="position: fixed; right: 0; bottom: 0; width: 0; height: 0; border: 0;"></iframe>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const iframe = document.getElementById('iframeImpresionDirecta');

    document.querySelectorAll('.btn-imprimir-directo').forEach(btn => {
        btn.addEventListener('click', () => {
            const url = btn.dataset.url;
            if (!url || !iframe) return;

            // Feedback visual temporal en el botón
            const icono = btn.querySelector('i');
            const claseOriginal = icono ? icono.className : '';
            if (icono) icono.className = 'fas fa-spinner fa-spin text-xs';
            btn.disabled = true;

            iframe.src = url;

            iframe.onload = () => {
                if (icono) icono.className = claseOriginal;
                btn.disabled = false;

                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.error('Error al invocar la impresión:', e);
                }
            };
        });
    });
});
</script>
@endsection