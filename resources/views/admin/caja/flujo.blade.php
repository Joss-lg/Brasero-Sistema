@extends('layouts.admin')

@section('content')
{{-- w-full para ocupar todo el espacio disponible --}}
<div class="p-3 sm:p-6 w-full space-y-4 sm:space-y-6" style="background-color: var(--bg-color);">

    {{-- Encabezado y Alertas --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-2 w-full">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-wide" style="color: var(--text-color);">Gestión de Flujo de Caja</h1>
            <p class="text-[10px] sm:text-xs uppercase tracking-widest font-bold mt-1" style="color: var(--text-muted);">Monitoreo de movimientos del turno</p>
        </div>
        @if(session('success'))
            <div class="w-full sm:w-auto flex items-center p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-500 text-xs sm:text-sm animate-fade-in shadow-lg shadow-emerald-500/5">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif
    </div>

    {{-- FRANJA SUPERIOR: Resumen de Turno en formato horizontal de tarjetas --}}
    <div class="rounded-2xl shadow-xl p-4 sm:p-5 relative overflow-hidden w-full border" style="background-color: var(--card-color); border-color: var(--border-color);">
        <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-[#b74309] to-[#8f3207]"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-4 flex-wrap">
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center whitespace-nowrap" style="color: var(--text-muted);">
                    <i class="fas fa-cash-register text-[#b74309] mr-2"></i> Resumen de Turno
                </h3>
                <span class="text-[10px] sm:text-xs font-bold" style="color: var(--text-muted);">ID Caja: <span style="color: var(--text-color);">#{{ $cajaActiva->id }}</span></span>
                <span class="text-[10px] sm:text-xs font-bold" style="color: var(--text-muted);">Cajero: <span style="color: var(--text-color);">{{ $cajaActiva->user->nombre ?? 'Admin' }}</span></span>
                <span class="px-2.5 py-0.5 rounded-md text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/20">
                    {{ $cajaActiva->turno ?? 'Matutino' }}
                </span>
            </div>

            {{-- Acciones --}}
            <div class="flex gap-2 w-full md:w-auto">
                <a href="{{ route('admin.caja.reporte.pdf', $cajaActiva->id) }}" target="_blank"
                    class="flex-1 md:flex-none flex items-center justify-center border font-bold text-[11px] sm:text-xs tracking-widest uppercase py-2.5 px-4 rounded-xl transition-all duration-300 shadow-md group whitespace-nowrap hover:border-[#b74309]/40 hover:bg-[#b74309]/10 cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                     <i class="fas fa-file-export mr-2 group-hover:text-[#b74309] transition-colors" style="color: var(--text-muted);"></i> Exportar
                </a>

                <button id="btnAbrirCierreCaja" type="button" class="flex-1 md:flex-none flex items-center justify-center bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500 hover:text-white text-rose-500 font-bold text-[11px] sm:text-xs tracking-widest uppercase py-2.5 px-4 rounded-xl transition-all duration-300 shadow-md shadow-rose-500/5 cursor-pointer whitespace-nowrap active:scale-95">
                    <i class="fas fa-lock mr-2"></i> Cerrar Caja
                </button>
            </div>
        </div>

        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner border" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--text-muted);">Saldo Inicial</span>
                <span class="font-black text-sm sm:text-base" style="color: var(--text-color);">${{ number_format($cajaActiva->monto_inicial, 2) }}</span>
            </div>

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner border" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-money-bill-wave text-emerald-500 mr-1 w-3"></i> Efectivo
                </span>
                <span class="font-black text-emerald-500 text-sm sm:text-base">+${{ number_format($ventasEfectivo, 2) }}</span>
            </div>

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner border" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-credit-card text-sky-500 mr-1 w-3"></i> Tarjeta
                </span>
                <span class="font-black text-sky-500 text-sm sm:text-base">+${{ number_format($ventasTarjeta, 2) }}</span>
            </div>

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner border" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-university text-indigo-500 mr-1 w-3"></i> Transf.
                </span>
                <span class="font-black text-indigo-500 text-sm sm:text-base">+${{ number_format($ventasTransferencia, 2) }}</span>
            </div>

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner border" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1 flex items-center" style="color: var(--text-muted);">
                    <i class="fas fa-minus-circle text-rose-500 mr-1 w-3"></i> Gastos
                </span>
                <span class="font-black text-rose-500 text-sm sm:text-base">-${{ number_format($totalGastos, 2) }}</span>
            </div>

            <div class="rounded-xl p-3 flex flex-col justify-center shadow-inner col-span-2 sm:col-span-1 border bg-[#b74309]/10 border-[#b74309]/30">
                <span class="text-[9px] sm:text-[10px] font-black text-[#b74309]/80 uppercase tracking-widest mb-1">Saldo Estimado</span>
                <span class="font-black text-[#b74309] text-base sm:text-lg">${{ number_format($saldoEstimado, 2) }}</span>
            </div>

        </div>
    </div>

    {{-- TABLAS DE HISTORIAL --}}
    <div class="space-y-4 sm:space-y-6 w-full">

        {{-- BLOQUE 1: Ventas del Turno --}}
        <div class="rounded-2xl shadow-xl overflow-hidden w-full border" style="background-color: var(--card-color); border-color: var(--border-color);">
            <div class="bg-gradient-to-r from-sky-500/10 to-transparent p-3 sm:p-4 border-b flex flex-wrap gap-2 justify-between items-center w-full" style="border-bottom-color: var(--border-color);">
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center" style="color: var(--text-color);">
                    <i class="fas fa-shopping-cart text-sky-500 mr-2"></i> Ventas del Turno
                </h3>
                <span class="text-[10px] sm:text-xs font-black bg-sky-500/10 text-sky-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-sky-500/20 whitespace-nowrap">
                    Total: ${{ number_format($totalVentas, 2) }}
                </span>
            </div>

            <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch">
                @if($historicoVentas->isEmpty())
                    <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                        <i class="fas fa-inbox text-2xl sm:text-3xl mb-3 opacity-40" style="color: var(--text-muted);"></i>
                        <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">No hay ventas registradas en este turno.</p>
                    </div>
                @else
                    @php
                        $ventasAgrupadas = $historicoVentas->groupBy(fn($v) => $v->flujoable_id ?? 'sin-orden-'.$v->id);
                    @endphp
                    <table class="w-full text-xs sm:text-sm text-center border-collapse">
                        <thead>
                            <tr class="font-bold text-[10px] sm:text-xs border-b uppercase tracking-wider" style="background-color: var(--input-bg); border-bottom-color: var(--border-color); color: var(--text-muted);">
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Folio</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Concepto</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Método(s) de Pago</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Total</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--border-color); color: var(--text-color);">
                            @foreach($ventasAgrupadas as $ordenId => $pagos)
                                @php
                                    $primera     = $pagos->first();
                                    $totalFila   = $pagos->sum('monto');
                                    $esMixto     = $pagos->count() > 1;
                                    $ordenIdReal = $primera->flujoable_id;
                                @endphp
                                <tr class="transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="border-bottom: 1px solid var(--border-color);">
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium whitespace-nowrap" style="color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($primera->fecha)->format('H:i') }} hrs
                                    </td>
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-center">
                                        @php $folio = $foliosPorOrden[$ordenIdReal] ?? null; @endphp
                                        @if($folio)
                                            <span class="px-2 py-0.5 rounded-lg border text-[11px] font-black font-mono tracking-wider" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                                                #{{ str_pad($folio, 3, '0', STR_PAD_LEFT) }}
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
                                                            default         => 'emerald',
                                                        };
                                                    @endphp
                                                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider bg-{{ $colorMetodo }}-500/10 border border-{{ $colorMetodo }}-500/20 text-{{ $colorMetodo }}-500 uppercase">
                                                            {{ $pago->metodo_pago }}
                                                        </span>
                                                        <span class="text-[10px] font-bold" style="color: var(--text-muted);">${{ number_format($pago->monto, 2) }}</span>
                                                        @if(!empty($pago->referencia))
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono border" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
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
                                                        default         => 'emerald',
                                                    };
                                                @endphp
                                                <span class="px-2 sm:px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-black tracking-wider bg-{{ $colorMetodo }}-500/10 border border-{{ $colorMetodo }}-500/20 text-{{ $colorMetodo }}-500 uppercase whitespace-nowrap">
                                                    {{ $primera->metodo_pago }}
                                                </span>
                                                @if(!empty($primera->referencia))
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold border uppercase tracking-wide whitespace-nowrap shadow-inner" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                                                        <i class="fas fa-hashtag text-[8px] mr-0.5 opacity-60"></i>Ref: {{ $primera->referencia }}
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
                                            <button type="button"
                                                class="btn-ver-venta w-8 h-8 rounded-lg border transition-colors flex items-center justify-center hover:text-[#b74309] hover:border-[#b74309] cursor-pointer"
                                                style="border-color: var(--border-color); color: var(--text-muted);"
                                                data-venta="{{ $primera->id }}"
                                                title="Ver detalle">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                            @if($ordenIdReal)
                                                <a href="{{ route('admin.caja.ticket.imprimir.orden', $ordenIdReal) }}"
                                                   target="_blank"
                                                   class="w-8 h-8 rounded-lg border transition-colors flex items-center justify-center hover:text-amber-500 hover:border-amber-500 cursor-pointer"
                                                   style="border-color: var(--border-color); color: var(--text-muted);"
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
        <div class="rounded-2xl shadow-xl overflow-hidden w-full border" style="background-color: var(--card-color); border-color: var(--border-color);">
            <div class="bg-gradient-to-r from-rose-500/10 to-transparent p-3 sm:p-4 border-b flex flex-wrap gap-2 justify-between items-center w-full" style="border-bottom-color: var(--border-color);">
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center" style="color: var(--text-color);">
                    <i class="fas fa-hand-holding-usd text-rose-500 mr-2"></i> Gastos y Salidas
                </h3>
                <span class="text-[10px] sm:text-xs font-black bg-rose-500/10 text-rose-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-rose-500/20 whitespace-nowrap">
                    Total: ${{ number_format($totalGastos, 2) }}
                </span>
            </div>

            <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch">
                @if($historicoGastos->isEmpty())
                    <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                        <i class="fas fa-receipt text-2xl sm:text-3xl mb-3 opacity-40" style="color: var(--text-muted);"></i>
                        <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">No hay gastos o salidas registrados en este turno.</p>
                    </div>
                @else
                    <table class="w-full text-xs sm:text-sm text-center border-collapse">
                        <thead>
                            <tr class="font-bold text-[10px] sm:text-xs border-b uppercase tracking-wider" style="background-color: var(--input-bg); border-bottom-color: var(--border-color); color: var(--text-muted);">
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Categoría</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4 text-left">Concepto / Descripción</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--border-color); color: var(--text-color);">
                            @foreach($historicoGastos as $gasto)
                                <tr class="transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="border-bottom: 1px solid var(--border-color);">
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium whitespace-nowrap" style="color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($gasto->fecha)->format('H:i') }} hrs
                                    </td>
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

        {{-- BLOQUE 3: Cuentas canceladas --}}
        @if(($historicoCancelaciones ?? collect())->isNotEmpty())
            <div class="rounded-2xl shadow-xl overflow-hidden w-full border border-rose-500/30" style="background-color: var(--card-color);">
                <div class="bg-gradient-to-r from-rose-500/15 to-transparent p-3 sm:p-4 border-b flex flex-wrap gap-2 justify-between items-center w-full" style="border-bottom-color: var(--border-color);">
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider flex items-center" style="color: var(--text-color);">
                        <i class="fas fa-ban text-rose-500 mr-2"></i> Cuentas canceladas (no cobradas)
                    </h3>
                    <span class="text-[10px] sm:text-xs font-black bg-rose-500/10 text-rose-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-rose-500/20 whitespace-nowrap">
                        Total: ${{ number_format($totalCancelaciones ?? 0, 2) }}
                    </span>
                </div>

                <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch">
                    <table class="w-full text-xs sm:text-sm text-center border-collapse">
                        <thead>
                            <tr class="font-bold text-[10px] sm:text-xs border-b uppercase tracking-wider" style="background-color: var(--input-bg); border-bottom-color: var(--border-color); color: var(--text-muted);">
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Hora</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4 text-left">Mesa y motivo</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Autorizó</th>
                                <th class="py-2.5 sm:py-3.5 px-2 sm:px-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--border-color); color: var(--text-color);">
                            @foreach($historicoCancelaciones as $cancelacion)
                                <tr class="transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="border-bottom: 1px solid var(--border-color);">
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-medium whitespace-nowrap" style="color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($cancelacion->fecha)->format('H:i') }} hrs
                                    </td>
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-left font-semibold">{{ $cancelacion->concepto }}</td>
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs whitespace-nowrap" style="color: var(--text-muted);">
                                        {{ $cancelacion->referencia }}
                                    </td>
                                    <td class="py-3 sm:py-4 px-2 sm:px-4 font-black text-rose-500 whitespace-nowrap">-${{ number_format($cancelacion->monto, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="px-3 sm:px-4 py-2.5 text-[10px] sm:text-[11px] border-t leading-snug" style="border-top-color: var(--border-color); color: var(--text-muted);">
                    Este dinero nunca entró al cajón, así que no cuenta como venta ni afecta el efectivo esperado del corte.
                </p>
            </div>
        @endif

    </div>
</div>

{{-- MODAL: DETALLE DE UNA VENTA DEL TURNO --}}
<div id="modal-detalle-venta" class="hidden fixed inset-0 z-[9998] items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-cerrar-venta></div>

    <div class="modal-container relative w-full max-w-lg rounded-3xl border shadow-2xl overflow-hidden max-h-[85vh] flex flex-col"
         style="background-color: var(--card-color); border-color: var(--border-color);">
        <div class="px-5 py-4 border-b flex items-start justify-between gap-3" style="border-bottom-color: var(--border-color);">
            <div>
                <h3 class="text-base font-black" style="color: var(--text-color);" id="venta-titulo">Detalle de la venta</h3>
                <p class="text-[11px]" style="color: var(--text-muted);" id="venta-subtitulo"></p>
            </div>
            <button type="button" data-cerrar-venta
                class="w-8 h-8 rounded-lg border hover:opacity-80 shrink-0 cursor-pointer flex items-center justify-center"
                style="border-color: var(--border-color); color: var(--text-muted);">&times;</button>
        </div>

        <div class="overflow-y-auto flex-1" id="venta-contenido">
            <p class="p-8 text-center text-sm" style="color: var(--text-muted);">Cargando...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-detalle-venta');
    if (!modal) return;

    const contenido = document.getElementById('venta-contenido');
    const cerrar = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    modal.querySelectorAll('[data-cerrar-venta]').forEach(el => el.addEventListener('click', cerrar));

    const dinero = n => '$' + Number(n).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const urlBase = @json(url('/caja/venta'));

    document.querySelectorAll('.btn-ver-venta').forEach(btn => {
        btn.addEventListener('click', async () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            contenido.innerHTML = '<p class="p-8 text-center text-sm" style="color: var(--text-muted);">Cargando...</p>';

            try {
                const res = await fetch(urlBase + '/' + btn.dataset.venta + '/detalle', {
                    headers: { 'Accept': 'application/json' }
                });
                const d = await res.json();

                if (!res.ok || !d.success) {
                    contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">No se pudo cargar el detalle.</p>';
                    return;
                }

                document.getElementById('venta-titulo').textContent =
                    d.mesa ? ('Mesa ' + d.mesa) : d.concepto;
                document.getElementById('venta-subtitulo').textContent =
                    [d.orden, d.hora, d.personas ? d.personas + ' pers.' : null].filter(Boolean).join(' \u00b7 ');

                let html = '<div class="p-5 space-y-4">';

               // Quien atendio y quien cobro
                html += '<div class="grid grid-cols-2 gap-3">'
                    + '<div class="rounded-xl border p-3" style="border-color: var(--border-color);">'
                    + '<p class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Mesero que atendió</p>'
                    + '<p class="text-sm font-bold mt-0.5" style="color: var(--text-color);">' + d.mesero + '</p></div>'
                    + '<div class="rounded-xl border p-3" style="border-color: var(--border-color);">'
                    + '<p class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Cajero que cobró</p>'
                    + '<p class="text-sm font-bold mt-0.5" style="color: var(--text-color);">' + d.cajero + '</p>'
                    + (d.cajero_aproximado
                        ? '<p class="text-[9px] text-amber-500 mt-0.5 leading-tight">Cobro anterior al registro de cajero: se muestra quien abrió el turno.</p>'
                        : '')
                    + '</div></div>';

                // Cobro: Si es pago mixto muestra el desglose completo, si es simple muestra la pastilla única
                if (d.es_mixto && d.pagos && d.pagos.length > 1) {
                    html += '<div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/20 p-4 space-y-3">'
                        + '<div class="flex items-center justify-between pb-2 border-b border-emerald-500/20">'
                        + '  <div>'
                        + '    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-500/20 border border-amber-500/30 text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pago Mixto</span>'
                        + '    <p class="text-[11px] mt-1" style="color: var(--text-muted);">' + d.concepto + '</p>'
                        + '  </div>'
                        + '  <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">' + dinero(d.monto) + '</span>'
                        + '</div>'
                        + '<div class="grid grid-cols-1 sm:grid-cols-3 gap-2">';

                    d.pagos.forEach(p => {
                        let color = p.metodo === 'tarjeta' ? 'sky' : (p.metodo === 'transferencia' ? 'indigo' : 'emerald');
                        html += '<div class="p-2.5 rounded-xl border flex flex-col justify-center items-center text-center" style="background-color: var(--card-color); border-color: var(--border-color);">'
                            + '<span class="text-[9px] font-black uppercase tracking-wider text-' + color + '-500">' + p.metodo + '</span>'
                            + '<span class="text-sm font-black text-[var(--text-color)] mt-0.5">' + dinero(p.monto) + '</span>'
                            + (p.referencia ? '<span class="text-[8px] font-mono px-1 py-0.5 rounded border mt-1" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">#' + p.referencia + '</span>' : '')
                            + '</div>';
                    });

                    html += '</div></div>';
                } else {
                    html += '<div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 flex items-center justify-between">'
                        + '<div><p class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">'
                        + d.metodo + (d.referencia ? ' \u00b7 ref ' + d.referencia : '') + '</p>'
                        + '<p class="text-[11px]" style="color: var(--text-muted);">' + d.concepto + '</p></div>'
                        + '<span class="text-xl font-black text-emerald-600 dark:text-emerald-400">' + dinero(d.monto) + '</span></div>';
                }

                // Consumo
                if (d.productos.length) {
                    html += '<div><p class="text-[10px] font-black uppercase tracking-wider mb-1.5" style="color: var(--text-muted);">Consumo de la mesa</p>'
                        + '<table class="w-full text-xs"><tbody class="divide-y" style="border-color: var(--border-color);">';
                    d.productos.forEach(p => {
                        html += '<tr class="' + (p.cancelado ? 'line-through opacity-50' : '') + '">'
                            + '<td class="py-2" style="color: var(--text-color);">' + p.producto
                            + (p.cancelado ? ' <span class="text-rose-500 font-bold text-[10px] no-underline">CANCELADO</span>' : '')
                            + (p.notas ? '<div class="text-[10px] italic" style="color: var(--text-muted);">' + p.notas + '</div>' : '')
                            + '</td>'
                            + '<td class="py-2 text-center w-12" style="color: var(--text-muted);">x' + p.cantidad + '</td>'
                            + '<td class="py-2 text-right w-24 font-bold" style="color: var(--text-color);">' + dinero(p.importe) + '</td></tr>';
                    });
                    html += '</tbody><tfoot><tr class="border-t" style="border-top-color: var(--border-color);">'
                        + '<td colspan="2" class="py-2 text-right text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Consumo</td>'
                        + '<td class="py-2 text-right font-black" style="color: var(--text-color);">' + dinero(d.consumo) + '</td>'
                        + '</tr></tfoot></table></div>';

                    if (Math.abs(d.consumo - d.monto) > 0.01) {
                        html += '<p class="text-[10px] leading-snug" style="color: var(--text-muted);">'
                            + 'El consumo y el cobro no coinciden porque esta cuenta se pagó en varias partes '
                            + '(pago combinado o cuenta dividida), o incluye IVA, propina o descuento.</p>';
                    }
                } else {
                    html += '<p class="text-xs" style="color: var(--text-muted);">Sin productos ligados a este movimiento.</p>';
                }

                html += '</div>';
                contenido.innerHTML = html;

            } catch (e) {
                console.error('Error al cargar la venta:', e);
                contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">Error de conexión.</p>';
            }
        });
    });
});
</script>

@include('admin.caja.corte')
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnAbrir = document.getElementById('btnAbrirCierreCaja');
    const modal = document.getElementById('modalCierreCaja');
    const btnCerrarX = document.getElementById('btnCerrarModalX');
    const btnCancelar = document.getElementById('btnCancelarModal');
    const backdrop = document.getElementById('backdropCierreCaja');
    const inputMonto = document.getElementById('monto_final_real');

    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', () => {
            modal.classList.remove('hidden');
            if (inputMonto) {
                setTimeout(() => inputMonto.focus(), 50);
            }
        });
    }

    const ocultarModal = () => {
        if (modal) modal.classList.add('hidden');
    };

    if (btnCerrarX) btnCerrarX.addEventListener('click', ocultarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', ocultarModal);
    if (backdrop) backdrop.addEventListener('click', ocultarModal);

    const cajaEsperado = document.getElementById('efectivoEsperado');
    const cajaDiferencia = document.getElementById('diferenciaCorte');

    if (inputMonto && cajaEsperado && cajaDiferencia) {
        const esperado = parseFloat(cajaEsperado.dataset.esperado) || 0;

        const pintarDiferencia = () => {
            const crudo = (inputMonto.value || '').trim().replace(',', '.');

            if (crudo === '') {
                cajaDiferencia.classList.add('hidden');
                return;
            }

            const contado = parseFloat(crudo);
            if (isNaN(contado)) {
                cajaDiferencia.classList.add('hidden');
                return;
            }

            const diferencia = Math.round((contado - esperado) * 100) / 100;
            cajaDiferencia.classList.remove('hidden');
            cajaDiferencia.className = 'mt-2 px-3 py-2 rounded-xl text-sm font-black flex items-center justify-between';

            if (Math.abs(diferencia) < 0.01) {
                cajaDiferencia.classList.add('bg-emerald-500/15', 'text-emerald-600', 'dark:text-emerald-400');
                cajaDiferencia.innerHTML = '<span>Caja cuadrada</span><span>$0.00</span>';
            } else if (diferencia < 0) {
                cajaDiferencia.classList.add('bg-rose-500/15', 'text-rose-600', 'dark:text-rose-400');
                cajaDiferencia.innerHTML = '<span>FALTANTE</span><span>-$' + Math.abs(diferencia).toFixed(2) + '</span>';
            } else {
                cajaDiferencia.classList.add('bg-amber-500/15', 'text-amber-600', 'dark:text-amber-400');
                cajaDiferencia.innerHTML = '<span>SOBRANTE</span><span>+$' + diferencia.toFixed(2) + '</span>';
            }
        };

        inputMonto.addEventListener('input', pintarDiferencia);
        inputMonto.addEventListener('change', pintarDiferencia);

        setInterval(() => {
            if (modal && !modal.classList.contains('hidden')) pintarDiferencia();
        }, 300);
    }
});
</script>