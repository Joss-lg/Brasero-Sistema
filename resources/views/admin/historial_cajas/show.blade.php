@extends('layouts.admin')

@section('title', 'Detalle de Turno | Agostadero')

@section('content')
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1400px] mx-auto space-y-6 relative z-10 font-sans min-h-screen bg-[var(--bg-color)] transition-colors duration-300">

    {{-- Botón de Regresar y Encabezado --}}
    <div class="flex flex-col gap-3 border-b border-[var(--border-color)] pb-5">
        <div>
            <a href="{{ route('historial.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--text-muted)] hover:text-blue-500 transition-colors">
                ← Volver al historial
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[var(--text-color)] flex items-center gap-2">
                     Auditoría de Caja #{{ $turno->id }}
                </h1>
                <p class="text-xs sm:text-sm font-medium text-[var(--text-muted)] mt-1">
                    Detalles específicos del flujo financiero capturado en este turno.
                </p>
            </div>

            {{-- Estado del turno + acciones del PDF --}}
            <div class="flex flex-wrap items-center gap-2">
                @if($turno->estado === 'abierta')
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 animate-pulse border border-emerald-200/50 dark:border-emerald-800/30">
                        ● Caja Activa
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-slate-800 text-[var(--text-muted)] border border-gray-200 dark:border-slate-700/60">
                         Turno Cerrado
                    </span>
                @endif

                {{-- Mismo endpoint para ambos: sin parámetro abre el PDF en el
                     navegador, con ?descargar=1 fuerza la descarga. --}}
                <a href="{{ route('historial.pdf', $turno->id) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-500 text-white transition-colors shadow-sm">
                    <i class="fas fa-file-pdf"></i>
                    Ver PDF
                </a>

                <a href="{{ route('historial.pdf', ['id' => $turno->id, 'descargar' => 1]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black border border-[var(--border-color)] text-[var(--text-color)] hover:border-blue-500 hover:text-blue-500 transition-colors">
                    <i class="fas fa-download"></i>
                    Descargar
                </a>
            </div>
        </div>
    </div>

    {{-- Grid de Información General --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Tarjeta 1: Metadatos del Turno --}}
        <div class="p-6 rounded-3xl border border-[var(--border-color)] bg-[var(--card-color)] space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Datos de Apertura</h3>

            <div class="space-y-3">
                <div>
                    <span class="block text-[11px] text-[var(--text-muted)] uppercase font-bold">Empleado Responsable</span>
                    <span class="text-sm font-bold text-[var(--text-color)]">{{ $turno->user->nombre ?? $turno->user->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] text-[var(--text-muted)] uppercase font-bold">Turno Asignado</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400 mt-0.5">
                        {{ $turno->turno === 'Matutino' ? '' : '' }} {{ $turno->turno }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] text-[var(--text-muted)] uppercase font-bold">Fecha y Hora Apertura</span>
                    <span class="text-xs font-semibold text-[var(--text-color)]">{{ $turno->created_at->format('d/m/Y - h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Balance Financiero --}}
        <div class="p-6 rounded-3xl border border-[var(--border-color)] bg-[var(--card-color)] space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Conciliación de Saldos</h3>

            <div class="space-y-3">
                @php
                    // El corte guarda estos valores al cerrar: son el registro
                    // oficial de lo que se contó ese día.
                    //
                    // OJO: monto_final_esperado YA incluye el fondo inicial
                    // (se calcula como inicial + entradas − salidas). Antes
                    // aquí se sumaba el fondo otra vez, así que un turno con
                    // $3,000 de fondo y $2,600 contados reportaba un faltante
                    // de $3,400 en lugar de los $400 reales.
                    $esperadoTurno = (float) ($turno->monto_final_esperado ?? 0);
                    $contadoTurno  = (float) ($turno->monto_final_real ?? 0);

                    // Se usa la diferencia guardada; el cálculo solo es
                    // respaldo para cortes viejos que no la tengan.
                    $difTurno = $turno->diferencia !== null
                        ? (float) $turno->diferencia
                        : round($contadoTurno - $esperadoTurno, 2);
                @endphp

                <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-1.5">
                    <span class="text-xs text-[var(--text-muted)] font-medium">Fondo Inicial:</span>
                    <span class="text-xs font-bold text-[var(--text-color)]">${{ number_format($turno->monto_inicial, 2) }}</span>
                </div>

                @if($turno->estado === 'cerrada')
                    <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-1.5">
                        <span class="text-xs text-[var(--text-muted)] font-medium">Debía haber en caja:</span>
                        <span class="text-xs font-bold text-[var(--text-color)]">${{ number_format($esperadoTurno, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-1.5">
                        <span class="text-xs text-[var(--text-muted)] font-medium">Efectivo contado:</span>
                        <span class="text-xs font-bold text-[var(--text-color)]">${{ number_format($contadoTurno, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-bold text-[var(--text-color)]">Resultado:</span>
                        @if(abs($difTurno) < 0.01)
                            <span class="text-xs font-bold text-emerald-500">✓ Caja Cuadrada</span>
                        @elseif($difTurno < 0)
                            <span class="text-xs font-bold text-red-500">⚠ Faltante: ${{ number_format(abs($difTurno), 2) }}</span>
                        @else
                            <span class="text-xs font-bold text-amber-500">⚠ Sobrante: ${{ number_format($difTurno, 2) }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-center pt-2 text-xs font-semibold text-[var(--text-muted)] italic">
                        El balance final se calculará al cerrar el turno.
                    </div>
                @endif
            </div>
        </div>

        {{-- Tarjeta 3: Notas de Auditoría --}}
        <div class="p-6 rounded-3xl border border-[var(--border-color)] bg-[var(--card-color)] flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)] mb-3">Notas / Observaciones</h3>
                <p class="text-xs font-medium text-[var(--text-muted)] leading-relaxed bg-[var(--input-bg)] p-3 rounded-2xl border border-[var(--border-color)] min-h-[75px]">
                    {{ $turno->observaciones ?? 'Sin comentarios ni incidentes reportados en este turno.' }}
                </p>
            </div>
            @if($turno->estado === 'cerrada')
                <span class="text-[10px] text-[var(--text-muted)] font-semibold block text-right mt-2">
                    Cierre procesado a las: {{ $turno->updated_at->format('h:i A') }}
                </span>
            @endif
        </div>
    </div>

    {{-- FRANJA: Resumen de Turno en formato horizontal de tarjetas --}}
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl p-4 sm:p-5 relative overflow-hidden w-full">
        <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-blue-500 to-indigo-600"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-4 flex-wrap">
                <h3 class="text-xs sm:text-sm font-black text-[var(--text-muted)] uppercase tracking-wider flex items-center whitespace-nowrap">
                    <i class="fas fa-cash-register text-blue-500 mr-2"></i> Resumen de Turno
                </h3>
                <span class="text-[10px] sm:text-xs font-bold text-[var(--text-muted)]">ID Caja: <span class="text-[var(--text-color)]">#{{ $turno->id }}</span></span>
                <span class="text-[10px] sm:text-xs font-bold text-[var(--text-muted)]">Cajero: <span class="text-[var(--text-color)]">{{ $turno->user->nombre ?? $turno->user->name ?? 'N/A' }}</span></span>
                <span class="px-2.5 py-0.5 rounded-md text-[10px] sm:text-xs font-bold bg-blue-500/10 border border-blue-500/20 text-blue-500 uppercase tracking-wider">
                    {{ $turno->turno === 'Matutino' ? '' : '' }} {{ $turno->turno }}
                </span>
            </div>
        </div>

        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">

            <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 flex flex-col justify-center shadow-inner">
                <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">Saldo Inicial</span>
                <span class="font-black text-[var(--text-color)] text-sm sm:text-base">${{ number_format($turno->monto_inicial, 2) }}</span>
            </div>

            <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 flex flex-col justify-center shadow-inner">
                <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1 flex items-center">
                    <i class="fas fa-money-bill-wave text-emerald-500 mr-1 w-3"></i> Efectivo
                </span>
                <span class="font-black text-emerald-500 text-sm sm:text-base">+${{ number_format($ventasEfectivo, 2) }}</span>
            </div>

            <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 flex flex-col justify-center shadow-inner">
                <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1 flex items-center">
                    <i class="fas fa-credit-card text-sky-500 mr-1 w-3"></i> Tarjeta
                </span>
                <span class="font-black text-sky-500 text-sm sm:text-base">+${{ number_format($ventasTarjeta, 2) }}</span>
            </div>

            <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 flex flex-col justify-center shadow-inner">
                <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1 flex items-center">
                    <i class="fas fa-university text-indigo-500 mr-1 w-3"></i> Transf.
                </span>
                <span class="font-black text-indigo-500 text-sm sm:text-base">+${{ number_format($ventasTransferencia, 2) }}</span>
            </div>

            <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 flex flex-col justify-center shadow-inner">
                <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1 flex items-center">
                    <i class="fas fa-minus-circle text-rose-500 mr-1 w-3"></i> Gastos
                </span>
                <span class="font-black text-rose-500 text-sm sm:text-base">-${{ number_format($totalGastos, 2) }}</span>
            </div>

            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-3 flex flex-col justify-center shadow-inner col-span-2 sm:col-span-1">
                <span class="text-[9px] sm:text-[10px] font-black text-blue-500/80 uppercase tracking-widest mb-1">Saldo Estimado</span>
                <span class="font-black text-blue-500 text-base sm:text-lg">${{ number_format($saldoEstimado, 2) }}</span>
            </div>

        </div>
    </div>

    {{-- BLOQUE 1: Ventas del Turno --}}
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl overflow-hidden w-full">
        <div class="bg-gradient-to-r from-sky-500/10 to-transparent p-3 sm:p-4 border-b border-[var(--border-color)] flex flex-wrap gap-2 justify-between items-center w-full">
            <h3 class="text-xs sm:text-sm font-black text-[var(--text-color)] uppercase tracking-wider flex items-center">
                <i class="fas fa-shopping-cart text-sky-500 mr-2"></i> Ventas del Turno
            </h3>
            <span class="text-[10px] sm:text-xs font-black bg-sky-500/10 text-sky-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-sky-500/20 whitespace-nowrap">
                Total: ${{ number_format($totalVentas, 2) }}
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            @if($historicoVentas->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                    <i class="fas fa-inbox text-2xl sm:text-3xl text-[var(--text-muted)] mb-3"></i>
                    <p class="text-xs sm:text-sm text-[var(--text-muted)] font-medium">No hay ventas registradas en este turno.</p>
                </div>
            @else
                @php
                    $ventasAgrupadas = $historicoVentas->groupBy(fn($v) => $v->flujoable_id ?? 'sin-orden-'.$v->id);
                @endphp
                <table class="w-full text-xs sm:text-sm text-center border-collapse">
                    <thead>
                        <tr class="bg-[var(--input-bg)] text-[var(--text-muted)] font-bold text-[10px] sm:text-xs border-b border-[var(--border-color)] uppercase tracking-wider">
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Folio</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Concepto</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Método(s) de Pago</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Total</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-color)] text-[var(--text-color)]">
                        @foreach($ventasAgrupadas as $ordenId => $pagos)
                            @php
                                $primera     = $pagos->first();
                                $totalFila   = $pagos->sum('monto');
                                $esMixto     = $pagos->count() > 1;
                                $ordenIdReal = $primera->flujoable_id;
                            @endphp
                            <tr class="hover:bg-[var(--input-bg)] transition-colors">
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium text-[var(--text-muted)] whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($primera->fecha)->format('H:i') }} hrs
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-center">
                                    @if($primera->folio_ticket ?? null)
                                        <span class="px-2 py-0.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[11px] font-black text-[var(--text-muted)] font-mono tracking-wider">
                                            #{{ str_pad($primera->folio_ticket, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @else
                                        <span class="text-[var(--border-color)]">—</span>
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
                                                    <span class="text-[10px] font-bold text-[var(--text-muted)]">${{ number_format($pago->monto, 2) }}</span>
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
                                            <a href="{{ route('admin.caja.ticket.imprimir.orden', $ordenIdReal) }}"
                                               target="_blank"
                                               class="w-8 h-8 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:text-amber-500 hover:border-amber-500 transition-colors flex items-center justify-center"
                                               title="Reimprimir ticket">
                                                <i class="fas fa-print text-xs"></i>
                                            </a>
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
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl overflow-hidden w-full">
        <div class="bg-gradient-to-r from-rose-500/10 to-transparent p-3 sm:p-4 border-b border-[var(--border-color)] flex flex-wrap gap-2 justify-between items-center w-full">
            <h3 class="text-xs sm:text-sm font-black text-[var(--text-color)] uppercase tracking-wider flex items-center">
                <i class="fas fa-hand-holding-usd text-rose-500 mr-2"></i> Gastos y Salidas
            </h3>
            <span class="text-[10px] sm:text-xs font-black bg-rose-500/10 text-rose-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-rose-500/20 whitespace-nowrap">
                Total: ${{ number_format($totalGastos, 2) }}
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            @if($historicoGastos->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                    <i class="fas fa-receipt text-2xl sm:text-3xl text-[var(--text-muted)] mb-3"></i>
                    <p class="text-xs sm:text-sm text-[var(--text-muted)] font-medium">No hay gastos o salidas registrados en este turno.</p>
                </div>
            @else
                <table class="w-full text-xs sm:text-sm text-center border-collapse">
                    <thead>
                        <tr class="bg-[var(--input-bg)] text-[var(--text-muted)] font-bold text-[10px] sm:text-xs border-b border-[var(--border-color)] uppercase tracking-wider">
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Categoría</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4 text-left">Concepto / Descripción</th>
                            <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-color)] text-[var(--text-color)]">
                        @foreach($historicoGastos as $gasto)
                            <tr class="hover:bg-[var(--input-bg)] transition-colors">
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium text-[var(--text-muted)] whitespace-nowrap">{{ \Carbon\Carbon::parse($gasto->fecha)->format('H:i') }} hrs</td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold bg-rose-500/10 border border-rose-500/20 text-rose-500 uppercase tracking-wide whitespace-nowrap">
                                        {{ $gasto->categoria }}
                                    </span>
                                </td>
                                <td class="py-3 sm:py-4 px-2 sm:px-4 text-left font-medium">
                                    <span class="font-semibold block">{{ $gasto->concepto }}</span>
                                    @if($gasto->observaciones)
                                        <span class="text-[10px] sm:text-xs text-[var(--text-muted)] block mt-0.5">{{ $gasto->observaciones }}</span>
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
@endsection