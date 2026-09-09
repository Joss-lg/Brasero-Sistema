{{-- detalle-cuenta.blade.php --}}
@php
    $division = $division ?? null; // null = mesa sin dividir
    $esDividida = !is_null($division);
    $tipoDivision = $division['tipo'] ?? null;
    $totalPartes = $division['total_partes'] ?? 1;
@endphp
<div class="flex flex-col h-full transition-colors duration-300" style="background-color: var(--card-color); color: var(--text-color);">

    <div class="p-4 pb-2">
        @if($esDividida)
            <div class="p-3 rounded-xl border" style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.2);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2 mb-0.5 text-[#b74309] dark:text-[#e8946a]">
                            <i class="fas fa-users"></i> Cuenta Dividida
                            · {{ $tipoDivision === 'equitativa' ? 'Partes iguales' : 'Por consumo' }}
                        </p>
                        <p class="text-xs font-bold" style="color: var(--text-color);">Dividida entre {{ $totalPartes }} personas</p>
                    </div>
                    <button type="button" id="btn-cancelar-division"
                        class="text-[10px] font-black uppercase text-rose-500 hover:text-rose-600 whitespace-nowrap cursor-pointer">
                        <i class="fas fa-times"></i> Cancelar división
                    </button>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--text-muted);">
                    Personas: {{ $mesa->capacidad ?? 'N/A' }}
                </p>
                <button type="button" id="btn-abrir-division"
                    class="text-[10px] font-black uppercase tracking-widest text-[#b74309] dark:text-[#e8946a] hover:opacity-80 flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-users"></i> Dividir cuenta
                </button>
            </div>

            {{-- Panel para configurar la división --}}
            <div id="panel-iniciar-division" class="hidden mt-4 p-4 rounded-2xl border space-y-3" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <div class="flex gap-2">
                    <button type="button" data-tipo-division="equitativa" class="tipo-division-btn flex-1 py-2 rounded-xl border-2 font-bold text-xs uppercase cursor-pointer" style="border-color: #b74309; background-color: rgba(183, 67, 9, 0.1); color: #b74309;">
                        Partes iguales
                    </button>
                    <button type="button" data-tipo-division="por_producto" class="tipo-division-btn flex-1 py-2 rounded-xl border-2 font-bold text-xs uppercase cursor-pointer" style="border-color: var(--border-color); color: var(--text-muted);">
                        Por consumo
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold" style="color: var(--text-muted);">N.º de personas</label>
                    <input type="number" id="input-numero-personas" min="2" max="20" value="2"
                        class="w-20 rounded-lg border p-2 text-center font-bold outline-none focus:border-[#b74309]"
                        style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);" />
                    <button type="button" id="btn-confirmar-division"
                        class="ml-auto px-4 py-2 rounded-xl bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-xs uppercase active:scale-95 transition-all shadow-sm cursor-pointer">
                        Dividir
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if($esDividida)
        <div class="px-4 pb-2">
            <div class="flex gap-1.5 overflow-x-auto pb-1.5 -mx-1 px-1" id="tabs-cuentas-division">
                @foreach($division['cuentas'] as $cuenta)
                    @php $esPagada = $cuenta['estado_orden'] === 'pagada'; @endphp
                    <button
                        type="button"
                        class="btn-cuenta px-3 py-1.5 rounded-lg font-bold text-[11px] whitespace-nowrap transition-all flex items-center gap-1.5 border-2 cursor-pointer {{ $esPagada ? 'bg-emerald-500/10 border-emerald-500 text-emerald-600 dark:text-emerald-400 opacity-70 cursor-not-allowed' : 'hover:bg-[#b74309]/10' }}"
                        style="{{ !$esPagada ? 'background-color: var(--input-bg); border-color: #b74309; color: var(--text-color);' : '' }}"
                        data-cuenta-id="{{ $cuenta['id'] }}"
                        data-numero="{{ $cuenta['numero_cuenta'] }}"
                        data-subtotal="{{ number_format($cuenta['subtotal'], 2, '.', '') }}"
                        data-iva="{{ number_format($cuenta['iva'], 2, '.', '') }}"
                        data-propina="{{ number_format($cuenta['propina'], 2, '.', '') }}"
                        data-total="{{ number_format($cuenta['total'], 2, '.', '') }}"
                        {{ $esPagada ? 'disabled' : '' }}>
                        @if($esPagada) <i class="fas fa-check text-[10px]"></i> @endif
                        <span class="texto-cuenta">P{{ $cuenta['numero_cuenta'] }} · <span class="valor-cuenta">${{ number_format($cuenta['total'], 2) }}</span></span>
                    </button>
                @endforeach
            </div>
            @if($tipoDivision === 'por_producto')
                <p class="text-[9px] font-bold uppercase mt-1" style="color: var(--text-muted);">
                    Usa + / − para repartir unidades entre personas.
                </p>
            @else
                <p class="text-[9px] font-bold uppercase mt-1" style="color: var(--text-muted);">
                    Selecciona una persona para cobrar su parte.
                </p>
            @endif
        </div>
    @endif

    <div class="px-4 pb-3 space-y-1 flex-1 min-h-0 overflow-y-auto" id="productos-container">
        @foreach($ordenes as $ordenActual)
            @foreach($ordenActual->detalles->where('estado', '!=', 'cancelado') as $detalle)
                @php
                    $nombrePlatillo = $detalle->producto->nombre ?? 'Producto sin nombre';
                    $varianteNombre = $detalle->variante?->nombre ?? null;
                @endphp
                <div class="producto-row py-1.5 px-2 rounded-lg transition-colors hover:bg-black/5 dark:hover:bg-white/5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 shrink-0 font-black text-[10px] rounded-md flex items-center justify-center border text-[#b74309] dark:text-[#e8946a]"
                                 style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.2);">
                                {{ $detalle->cantidad }}x
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-[13px] leading-tight truncate" style="color: var(--text-color);">
                                    {{ $nombrePlatillo }}@if($varianteNombre) - <span class="text-[#b74309] dark:text-[#e8946a] font-black uppercase">{{ $varianteNombre }}</span>@endif
                                </p>
                                <p class="text-[9px] font-semibold leading-tight" style="color: var(--text-muted);">Unit: ${{ number_format($detalle->precio_unitario, 2) }}</p>

                                @if($detalle->notas)
                                    <p class="text-[9px] font-bold uppercase italic truncate leading-tight opacity-75" style="color: var(--text-muted);">{{ $detalle->notas }}</p>
                                @endif

                                @if($detalle->promocionAplicada)
                                    <p class="text-[9px] text-emerald-600 dark:text-emerald-400 font-black uppercase flex items-center gap-1 leading-tight">
                                        <i class="fas fa-tag"></i> {{ $detalle->promocionAplicada->promocion->nombre ?? 'Promo' }}
                                        (-${{ number_format($detalle->promocionAplicada->monto_descuento, 2) }})
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0 flex items-center gap-2">
                            <div>
                                @if($detalle->promocionAplicada)
                                    <span class="text-[9px] line-through block leading-tight opacity-60" style="color: var(--text-muted);">
                                        ${{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}
                                    </span>
                                @endif
                                <span class="font-black text-[13px]" style="color: var(--text-color);">
                                    ${{ number_format(($detalle->precio_unitario * $detalle->cantidad) - ($detalle->promocionAplicada->monto_descuento ?? 0), 2) }}
                                </span>
                            </div>
                            @if(!$esDividida)
                                <button type="button"
                                    onclick="cancelarProductoCaja({{ $detalle->id }}, this, {{ $detalle->cantidad }})"
                                    class="w-7 h-7 rounded-lg text-rose-500 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm cursor-pointer"
                                    title="Cancelar producto">
                                    <i class="fas fa-trash-alt text-[9px]"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($esDividida && $tipoDivision === 'por_producto')
                        @php
                            $asig = $division['asignacionesPorDetalle'][$detalle->id] ?? ['por_persona' => [], 'sin_asignar' => $detalle->cantidad];
                        @endphp
                        <div class="mt-1 pl-9 producto-asignacion" data-detalle-id="{{ $detalle->id }}" data-cantidad-total="{{ $detalle->cantidad }}">
                            <div class="flex flex-wrap items-center gap-1">
                                @for($p = 1; $p <= $totalPartes; $p++)
                                    @php $cantidadPersona = $asig['por_persona'][$p] ?? 0; @endphp
                                    <div class="flex items-center gap-0.5 rounded-md pl-1.5 pr-0.5 py-0.5 stepper-persona border"
                                        style="background-color: var(--input-bg); border-color: var(--border-color);"
                                        data-detalle-id="{{ $detalle->id }}" data-numero="{{ $p }}">
                                        <span class="text-[8px] font-black" style="color: var(--text-muted);">P{{ $p }}</span>
                                        <button type="button" class="btn-stepper-restar w-4 h-4 rounded border text-[10px] font-black leading-none flex items-center justify-center cursor-pointer" style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);">−</button>
                                        <span class="stepper-valor w-3 text-center text-[10px] font-black" style="color: var(--text-color);">{{ $cantidadPersona }}</span>
                                        <button type="button" class="btn-stepper-sumar w-4 h-4 rounded border text-[10px] font-black leading-none flex items-center justify-center cursor-pointer" style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);">+</button>
                                    </div>
                                @endfor
                                <span class="sin-asignar-badge text-[8px] font-black uppercase {{ $asig['sin_asignar'] > 0 ? 'text-amber-500' : 'hidden' }}">
                                    {{ $asig['sin_asignar'] }} sin asignar
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    <div class="mt-auto px-4 py-2.5 border-t shadow-sm transition-colors" style="background-color: var(--input-bg); border-color: var(--border-color);">
        <div class="space-y-1">
            <div class="space-y-0.5">
                <div class="flex justify-between text-[11px] font-semibold" style="color: var(--text-muted);">
                    <span>Subtotal</span>
                    <span class="font-bold" style="color: var(--text-color);" id="resumen-subtotal">${{ number_format($subtotalBruto ?? 0, 2) }}</span>
                </div>

                @if(($descuentoPromociones ?? 0) > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 text-[11px] font-semibold">
                        <span>Descuento (promociones)</span>
                        <span class="font-bold">-${{ number_format($descuentoPromociones, 2) }}</span>
                    </div>
                @endif

                @if(($descuentoCaja ?? 0) > 0)
                    <div class="flex justify-between text-[11px] font-semibold text-[#b74309] dark:text-[#e8946a]">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-percent text-[10px]"></i>
                            Descuento ({{ rtrim(rtrim(number_format($descuentoPorcentaje ?? 0, 2), '0'), '.') }}%)
                        </span>
                        <span class="font-bold">-${{ number_format($descuentoCaja, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-amber-600 dark:text-amber-400 text-[11px] font-semibold {{ ($propina ?? 0) > 0 ? '' : 'hidden' }}" id="resumen-propina-row">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-hand-holding-dollar text-[10px]"></i> Propina
                    </span>
                    <span class="font-bold" id="resumen-propina">${{ number_format($propina ?? 0, 2) }}</span>
                </div>

                @if($esDelivery ?? false)
                    <div class="mt-1 p-2 rounded-lg space-y-0.5 border" style="background-color: rgba(249, 115, 22, 0.1); border-color: rgba(249, 115, 22, 0.2);">
                        <p class="text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 text-orange-500">
                            <i class="fas fa-motorcycle"></i> {{ $plataformaNombre ?? 'Delivery' }}
                        </p>
                        <div class="flex justify-between text-[11px] font-semibold" style="color: var(--text-muted);">
                            <span>Comisión ({{ number_format($comisionPorcentaje ?? 0, 0) }}%)</span>
                            <span class="font-bold" style="color: var(--text-color);">${{ number_format($comisionMonto ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] font-semibold" style="color: var(--text-muted);">
                            <span>IVA de la comisión ({{ number_format($comisionIvaPorcentaje ?? 0, 0) }}%)</span>
                            <span class="font-bold" style="color: var(--text-color);">${{ number_format($comisionIvaMonto ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-orange-500 text-[11px] font-black pt-0.5 border-t" style="border-color: rgba(249, 115, 22, 0.2);">
                            <span>Total comisión (se suma al pedido)</span>
                            <span>${{ number_format($comisionTotal ?? 0, 2) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-t pt-1 flex justify-between items-center" style="border-color: var(--border-color);">
                <span class="font-black uppercase tracking-[0.15em] text-[10px]" style="color: var(--text-muted);" id="resumen-total-label">
                    {{ $esDividida ? 'Total mesa' : 'Total' }}
                </span>
                <span class="text-xl sm:text-2xl font-black tracking-tighter italic" style="color: var(--text-color);" id="resumen-total">
                    ${{ number_format($totalPagar ?? 0, 2) }}
                </span>
            </div>
            @if($esDividida)
                <p class="text-right text-[10px] font-bold text-[#b74309] dark:text-[#e8946a]" id="resumen-persona-seleccionada"></p>
            @endif
        </div>
    </div>
</div>

{{-- MODAL: Cancelar producto desde Caja --}}
<div id="modal-cancelar-producto" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarModalCancelarProducto()"></div>
    <div class="modal-container relative w-full max-w-sm rounded-3xl shadow-2xl border overflow-hidden"
         style="background-color: var(--card-color); border-color: var(--border-color);">

        {{-- Header --}}
        <div class="bg-rose-500/10 border-b border-rose-500/20 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-500/15 border border-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-trash-alt text-rose-500 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black" style="color: var(--text-color);" id="mcp-titulo">Cancelar producto</h3>
                    <p class="text-[11px]" style="color: var(--text-muted);" id="mcp-subtitulo">Requiere autorización</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalCancelarProducto()"
                class="w-8 h-8 rounded-xl border flex items-center justify-center transition-colors cursor-pointer"
                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- Paso 1: Cantidad --}}
        <div id="mcp-paso-cantidad" class="px-6 py-5 space-y-4">
            <p class="text-sm text-center" style="color: var(--text-muted);">
                ¿Cuántas unidades deseas cancelar?
            </p>
            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="mcpAjustarCantidad(-1)"
                    class="w-11 h-11 rounded-2xl border font-black text-xl active:scale-95 transition-all cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                    −
                </button>
                <div class="flex flex-col items-center">
                    <span id="mcp-cantidad-display" class="text-4xl font-black tabular-nums" style="color: var(--text-color);">1</span>
                    <span id="mcp-cantidad-max" class="text-[10px] font-medium mt-0.5" style="color: var(--text-muted);">de 1</span>
                </div>
                <button type="button" onclick="mcpAjustarCantidad(1)"
                    class="w-11 h-11 rounded-2xl border font-black text-xl active:scale-95 transition-all cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                    +
                </button>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="cerrarModalCancelarProducto()"
                    class="flex-1 h-11 rounded-2xl border font-bold text-sm transition-colors cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="button" onclick="mcpIrANip()"
                    class="flex-1 h-11 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm active:scale-95 transition-all cursor-pointer">
                    Continuar →
                </button>
            </div>
        </div>

        {{-- Paso 2: NIP --}}
        <div id="mcp-paso-nip" class="hidden px-6 py-5 space-y-4">
            <p class="text-sm text-center" style="color: var(--text-muted);">
                NIP del <span class="font-black" style="color: var(--text-color);">Administrador</span>
            </p>

            {{-- Display NIP --}}
            <div class="flex justify-center gap-3 py-1">
                @for($i = 0; $i < 4; $i++)
                    <div class="mcp-nip-dot w-4 h-4 rounded-full border-2 bg-transparent transition-all duration-150" style="border-color: var(--border-color);"></div>
                @endfor
            </div>

            {{-- Teclado numérico --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button type="button" onclick="mcpNipEscribir('{{ $k }}')"
                        class="h-12 rounded-2xl border font-black text-lg active:scale-95 transition-all cursor-pointer"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        {{ $k }}
                    </button>
                @endforeach
                <button type="button" onclick="mcpNipBorrar()"
                    class="h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 font-black hover:bg-rose-500/20 active:scale-95 transition-all flex items-center justify-center cursor-pointer">
                    <i class="fas fa-delete-left text-base"></i>
                </button>
                <button type="button" onclick="mcpNipEscribir('0')"
                    class="h-12 rounded-2xl border font-black text-lg active:scale-95 transition-all cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                    0
                </button>
                <button type="button" id="mcp-btn-confirmar" onclick="mcpConfirmar()"
                    class="h-12 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-md">
                    <i class="fas fa-check text-xs"></i> OK
                </button>
            </div>

            {{-- Error --}}
            <p id="mcp-error" class="hidden text-center text-xs font-bold text-rose-500 bg-rose-500/10 border border-rose-500/20 rounded-xl py-2 px-3"></p>

            <button type="button" onclick="mcpVolverCantidad()"
                class="w-full text-center text-xs font-medium transition-colors py-1 cursor-pointer" style="color: var(--text-muted);">
                ← Volver
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    let _mcpDetalleId   = null;
    let _mcpBtn         = null;
    let _mcpCantTotal   = 1;
    let _mcpCantSel     = 1;
    let _mcpNip         = '';

    const modal         = () => document.getElementById('modal-cancelar-producto');
    const pasoCantidad  = () => document.getElementById('mcp-paso-cantidad');
    const pasoNip       = () => document.getElementById('mcp-paso-nip');
    const displayCant   = () => document.getElementById('mcp-cantidad-display');
    const maxLabel      = () => document.getElementById('mcp-cantidad-max');
    const errorEl       = () => document.getElementById('mcp-error');
    const dots          = () => document.querySelectorAll('.mcp-nip-dot');

    function actualizarDots() {
        dots().forEach((d, i) => {
            if (i < _mcpNip.length) {
                d.style.backgroundColor = 'var(--text-color)';
                d.style.borderColor = 'var(--text-color)';
            } else {
                d.style.backgroundColor = 'transparent';
                d.style.borderColor = 'var(--border-color)';
            }
        });
    }

    window.cancelarProductoCaja = function (detalleId, btn, cantidadTotal) {
        _mcpDetalleId = detalleId;
        _mcpBtn       = btn;
        _mcpCantTotal = cantidadTotal;
        _mcpCantSel   = 1;
        _mcpNip       = '';

        if (cantidadTotal <= 1) {
            pasoCantidad().classList.add('hidden');
            pasoNip().classList.remove('hidden');
            document.getElementById('mcp-titulo').textContent = 'Cancelar producto';
            document.getElementById('mcp-subtitulo').textContent = 'Ingresa el NIP del Administrador';
        } else {
            pasoCantidad().classList.remove('hidden');
            pasoNip().classList.add('hidden');
            displayCant().textContent = '1';
            maxLabel().textContent = `de ${cantidadTotal}`;
            document.getElementById('mcp-titulo').textContent = 'Cancelar unidades';
            document.getElementById('mcp-subtitulo').textContent = `Máximo ${cantidadTotal} unidades`;
        }

        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        modal().classList.remove('hidden');
    };

    window.cerrarModalCancelarProducto = function () {
        modal().classList.add('hidden');
        _mcpNip = '';
    };

    window.mcpAjustarCantidad = function (delta) {
        _mcpCantSel = Math.min(_mcpCantTotal, Math.max(1, _mcpCantSel + delta));
        displayCant().textContent = _mcpCantSel;
    };

    window.mcpIrANip = function () {
        pasoCantidad().classList.add('hidden');
        pasoNip().classList.remove('hidden');
        document.getElementById('mcp-subtitulo').textContent = `Cancelar ${_mcpCantSel} unidad(es)`;
        _mcpNip = '';
        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
    };

    window.mcpVolverCantidad = function () {
        if (_mcpCantTotal <= 1) { cerrarModalCancelarProducto(); return; }
        pasoNip().classList.add('hidden');
        pasoCantidad().classList.remove('hidden');
    };

    window.mcpNipEscribir = function (digit) {
        if (_mcpNip.length >= 4) return;
        _mcpNip += digit;
        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        if (_mcpNip.length === 4) mcpConfirmar();
    };

    window.mcpNipBorrar = function () {
        _mcpNip = _mcpNip.slice(0, -1);
        actualizarDots();
    };

    window.mcpConfirmar = async function () {
        if (_mcpNip.length < 1) {
            mostrarErrorMcp('Ingresa el NIP del Administrador.');
            return;
        }

        const btnConfirmar = document.getElementById('mcp-btn-confirmar');
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(`/mesero/comanda/detalle/${_mcpDetalleId}/cancelar`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ nip: _mcpNip, cantidad_cancelar: _mcpCantSel })
            });
            const data = await res.json().catch(() => null);

            if (!res.ok || !data?.success) {
                mostrarErrorMcp(data?.message || 'NIP incorrecto o sin permisos.');
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="fas fa-check text-xs"></i> OK';
                _mcpNip = '';
                actualizarDots();
                return;
            }

            window.location.reload();
        } catch (err) {
            mostrarErrorMcp('Error de conexión. Intenta de nuevo.');
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="fas fa-check text-xs"></i> OK';
        }
    };

    function mostrarErrorMcp(msg) {
        const el = errorEl();
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarModalCancelarProducto();
    });
})();
</script>