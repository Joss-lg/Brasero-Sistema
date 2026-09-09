{{-- BACKDROP PARA MÓVIL (Fondo oscuro al abrir el carrito flotante) --}}
<div id="backdropOrdenMobile" onclick="toggleOrdenMobile()"
     class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-300">
</div>

{{-- ========================================== --}}
{{-- COLUMNA 1: ACCIONES RÁPIDAS (SOLO DESKTOP ≥1024px) --}}
{{-- ========================================== --}}
<aside id="col-acciones" class="hidden lg:flex w-full lg:w-[190px] xl:w-[220px] flex-shrink-0 h-full flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 pb-4 z-20 overflow-y-auto hide-scroll">

    <div class="flex items-center gap-2 mb-6">
        <button type="button" onclick="salirComanda()" class="flex-1 h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-semibold text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] hover:shadow-md transition-all duration-150 active:scale-95 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50">
            <i class="fas fa-arrow-left text-[var(--text-muted)] text-[10px]"></i> Mesas
        </button>
        <button type="button" onclick="toggleTheme()" class="w-10 h-10 shrink-0 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50">
            <i id="themeIcon" class="fas fa-sun text-[11px] group-hover:rotate-45 transition-transform duration-500"></i>
        </button>
    </div>

    <div class="flex items-center justify-between mb-6 p-3 rounded-2xl bg-gradient-to-br from-[var(--bg-panel)] to-transparent border border-[var(--border-color)] shadow-sm">
        <div class="flex flex-col">
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] mb-1">Activa</span>
            <h3 class="text-2xl font-black tracking-tight text-[var(--text-main)] leading-none">
                @if($mesa->esDelivery())
                    <i class="fas fa-motorcycle text-orange-500"></i> {{ $mesa->plataformaDelivery->nombre ?? 'Delivery' }}
                @else
                    Mesa {{ $mesa->numero ?? '12M' }}
                @endif
            </h3>
        </div>
        <div class="relative flex items-center justify-center">
            <div class="absolute w-4 h-4 rounded-full bg-emerald-500/30 animate-pulse"></div>
            <div class="relative w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.6)] border border-[var(--bg-base)]"></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 flex-1 overflow-y-auto hide-scroll pb-2">
        <button type="button" onclick="ajustarPersonas()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[#b74309]/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50">
            <i class="fas fa-users text-[var(--text-muted)] group-hover:text-[#b74309] mb-2 text-sm transition-colors duration-150"></i>
            <span class="text-[10px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Personas</span>
            <span id="txtPersonas" class="text-xs font-bold text-[var(--text-main)] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
        </button>

        <button type="button" onclick="imprimirPrecuenta()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[#b74309]/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50">
            <i class="fas fa-receipt text-[var(--text-muted)] group-hover:text-[#b74309] mb-2 text-sm transition-colors duration-150"></i>
            <span class="text-[10px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors leading-tight text-center">Pre<br>Cuenta</span>
        </button>

        <button type="button" onclick="agregarNota()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[#b74309]/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50">
            <i class="fas fa-pen text-[var(--text-muted)] group-hover:text-[#b74309] mb-2 text-sm transition-colors duration-150"></i>
            <span class="text-[10px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Nota</span>
        </button>

        <button type="button" onclick="llamarCapitan()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-indigo-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50">
            <i class="fas fa-exchange-alt text-[var(--text-muted)] group-hover:text-indigo-500 mb-2 text-sm transition-colors duration-150"></i>
            <span class="text-[10px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Traspaso</span>
        </button>

        @if($esCapitan ?? false)
            <button type="button" onclick="llamarCapitan()" class="col-span-2 mt-1 h-12 flex items-center justify-center gap-2 rounded-[16px] bg-gradient-to-b from-[var(--accent)] to-[var(--accent)] border border-[var(--border-color)] hover:opacity-90 hover:shadow-lg transition-all duration-150 active:scale-95 group text-white shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--accent)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-base)]">
                <i class="fas fa-shield-alt text-[11px]"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Capitán</span>
            </button>
        @endif
    </div>

    <button type="button" onclick="limpiarTicket()" class="mt-4 w-full h-12 rounded-[16px] border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white hover:shadow-md hover:shadow-red-500/20 transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 font-bold text-[10px] uppercase tracking-widest shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50">
        <i class="fas fa-trash-alt text-[11px]"></i> Eliminar Todo
    </button>
</aside>

{{-- ========================================== --}}
{{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
{{-- ========================================== --}}
<div id="barra-acciones-mobile"
     class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-[var(--bg-base)] border-t border-[var(--border-color)] shadow-[0_-4px_20px_rgba(0,0,0,0.12)]"
     style="padding-bottom: env(safe-area-inset-bottom)">

    <div class="flex items-center gap-2 px-3 pt-2.5 pb-1.5 overflow-x-auto hide-scroll">
        <button type="button" onclick="ajustarPersonas()"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-[var(--text-main)] shadow-sm active:scale-95 whitespace-nowrap">
            <i class="fas fa-users text-[#b74309] text-[10px]"></i>
            <span id="txtPersonasMobile">{{ $mesa->capacidad ?? 1 }}</span> Pax
        </button>
        <button type="button" onclick="agregarNota()"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-[var(--text-main)] shadow-sm active:scale-95 whitespace-nowrap">
            <i class="fas fa-pen text-[#b74309] text-[10px]"></i> Nota
        </button>
        <button type="button" onclick="llamarCapitan()"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-[var(--text-main)] shadow-sm active:scale-95 whitespace-nowrap">
            <i class="fas fa-exchange-alt text-indigo-500 text-[10px]"></i> Traspaso
        </button>
        <button type="button" onclick="imprimirPrecuenta()"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-[var(--text-main)] shadow-sm active:scale-95 whitespace-nowrap">
            <i class="fas fa-receipt text-[#b74309] text-[10px]"></i> Precuenta
        </button>
        <button type="button" onclick="limpiarTicket()"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-red-500 shadow-sm active:scale-95 whitespace-nowrap">
            <i class="fas fa-trash-alt text-[10px]"></i> Limpiar
        </button>
    </div>

    <div class="px-3 pb-2">
        <button type="button" onclick="toggleOrdenMobile()"
            class="w-full h-12 rounded-2xl bg-[var(--text-main)] text-[var(--bg-base)] flex items-center justify-between px-4 shadow-md active:scale-[0.98] transition-transform">
            <div class="flex items-center gap-2.5">
                <span id="barra-mobile-count"
                    class="w-6 h-6 rounded-full bg-[var(--bg-base)] text-[var(--text-main)] text-[11px] font-black flex items-center justify-center">0</span>
                <span class="text-[13px] font-black tracking-wide">Ver orden</span>
            </div>
            <div class="flex items-center gap-2">
                <span id="barra-mobile-total" class="text-[13px] font-black">$0.00</span>
                <i class="fas fa-chevron-up text-[10px] opacity-60"></i>
            </div>
        </button>
    </div>
</div>

<script>
(function () {
    window.salirComanda = async function () {
        const config    = window.ComandaConfig || {};
        const mesa      = config.mesa || {};
        const csrf      = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const dashboard = config.rutas?.dashboard || '/mesero/dashboard';

        if (mesa.esDelivery) {
            const ticketItems = document.querySelectorAll('#listaTicket .ticket-item').length;
            const enviadosDB  = (window.platillosEnviadosDB || []).length;

            if (ticketItems === 0 && enviadosDB === 0) {
                try {
                    await fetch(`/mesero/delivery/${mesa.id}/cancelar-vacio`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });
                } catch (e) {}
                window.location.href = dashboard;
                return;
            }
        }

        window.location.href = dashboard;
    };
})();
</script>

<section id="col-ticket" class="
    md:flex md:w-[300px] lg:w-[320px] xl:w-[360px] md:flex-shrink-0 md:h-full md:flex-col md:bg-[var(--bg-base)] md:border-r md:border-[var(--border-color)] md:relative md:z-10 md:shadow-[20px_0_40px_-15px_rgba(0,0,0,0.15)] md:translate-y-0 md:rounded-none
    fixed inset-x-0 bottom-0 z-50 flex flex-col bg-[var(--bg-base)] rounded-t-[24px]
    h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.3)]
    transition-transform duration-300 translate-y-full
">

    <div class="md:hidden w-full flex items-center justify-between px-4 pt-3 pb-2 border-b border-[var(--border-color)]">
        <button type="button" onclick="window.location.href='{{ route('mesero.dashboard') }}'" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-[11px] font-bold text-red-500 shadow-sm active:scale-95">
            <i class="fas fa-sign-out-alt"></i> Salir
        </button>

        <div class="flex-1 flex justify-center cursor-pointer px-4 py-2" onclick="toggleOrdenMobile()">
            <div class="w-12 h-1.5 rounded-full bg-[var(--border-color)]"></div>
        </div>

        <div class="flex items-center gap-1.5 text-[12px] font-black tracking-tight text-[var(--text-main)]">
            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></div>
            @if($mesa->esDelivery())
                <i class="fas fa-motorcycle text-orange-500"></i> {{ strtoupper($mesa->plataformaDelivery->nombre ?? 'DELIVERY') }}
            @else
                MESA {{ $mesa->numero ?? '12M' }}
            @endif
        </div>
    </div>

    <div class="hidden md:flex lg:hidden items-center justify-between px-4 py-3 border-b border-[var(--border-color)]">
        <button type="button" onclick="salirComanda()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[11px] font-bold text-[var(--text-main)] shadow-sm hover:bg-[var(--hover-bg)] active:scale-95">
            <i class="fas fa-arrow-left text-[var(--text-muted)]"></i> Mesas
        </button>
        <div class="flex items-center gap-1.5 text-[12px] font-black tracking-tight text-[var(--text-main)]">
            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></div>
            @if($mesa->esDelivery())
                <i class="fas fa-motorcycle text-orange-500"></i> {{ strtoupper($mesa->plataformaDelivery->nombre ?? 'DELIVERY') }}
            @else
                MESA {{ $mesa->numero ?? '12M' }}
            @endif
        </div>
        <button type="button" onclick="toggleTheme()" class="w-8 h-8 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] active:scale-95">
            <i id="themeIconTablet" class="fas fa-sun text-[11px]"></i>
        </button>
    </div>

    <div class="p-4 border-b border-[var(--border-color)] flex flex-col gap-3 bg-[var(--bg-base)]">
        <div id="barraModificadores" class="lg:hidden rounded-2xl border border-[var(--border-color)] bg-[var(--bg-panel)] p-3 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Acciones del platillo</span>
                <span id="barraModificadores-hint" class="text-[9px] font-semibold text-[var(--text-muted)]">Toca un producto para modificar</span>
            </div>
            <div id="contenedorBotonesModificadores" class="flex flex-wrap gap-2"></div>
        </div>

        <div class="relative flex w-full bg-[var(--bg-panel)] p-1 rounded-xl border border-[var(--border-color)] shadow-inner">
            <div class="absolute inset-y-1 left-1 right-1 pointer-events-none">
                <div id="tab-slider" class="h-full w-1/2 rounded-lg bg-[var(--text-main)] shadow-md transition-transform duration-300 ease-out"></div>
            </div>
            <button type="button" onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="relative z-10 flex-1 py-2.5 md:py-1.5 text-[11px] md:text-[10px] font-bold text-[var(--bg-base)] transition-colors outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/60 rounded-lg">Orden</button>
            <button type="button" onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="relative z-10 flex-1 py-2.5 md:py-1.5 text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/60 rounded-lg">Total</button>
        </div>
    </div>

    {{-- VISTA 1: NUEVA ORDEN (Sin la sección de "Ya enviado") --}}
    <div id="vista-nueva-orden" class="panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex flex-col relative bg-[var(--bg-base)]">
        <div class="flex justify-between text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] mb-3 px-1">
            <span>CANT. / PLATILLO</span>
            <span>MONTO</span>
        </div>

        <div id="listaTicket" class="flex flex-col gap-2.5 pb-4"></div>

        <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10 transition-opacity duration-300">
            <i class="fas fa-plate-wheat text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">Sin productos.<br>Comienza a agregar.</p>
        </div>
    </div>

    @php
        $todasLasOrdenes = \App\Models\Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', \App\Models\Orden::getEstadosActivos())
            ->with(['detalles.producto', 'detalles.variante'])
            ->get();

        $platillosEnviados = $todasLasOrdenes->flatMap(function ($orden) {
            return $orden->detalles->map(function ($detalle) {
                $nombreBase = optional($detalle->producto)->nombre ?? 'Platillo';
                $nombreVar  = optional($detalle->variante)->nombre;
                $nombreCompleto = $nombreVar ? "{$nombreBase} - {$nombreVar}" : $nombreBase;

                return (object) [
                    'id'       => $detalle->id,
                    'nombre'   => $nombreCompleto,
                    'cantidad' => $detalle->cantidad,
                    'precio'   => $detalle->precio_unitario,
                    'estado'   => $detalle->estado,
                ];
            });
        });
    @endphp

    {{-- VISTA 2: ENVIADOS --}}
    <div id="vista-enviados" class="hidden panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
            <div class="flex flex-col gap-2">
                @foreach($platillosEnviados as $item)
                    @php $cancelado = ($item->estado ?? '') === 'cancelado'; @endphp
                    <div id="enviado-item-{{ $item->id }}" class="bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 flex justify-between items-center shadow-sm hover:shadow-md transition-shadow duration-150 {{ $cancelado ? 'opacity-60' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 md:w-6 md:h-6 rounded-lg bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-xs md:text-[11px] font-bold flex items-center justify-center shadow-sm">{{ $item->cantidad ?? 1 }}</span>
                            <span class="text-[13px] md:text-[12px] font-medium {{ $cancelado ? 'line-through text-[var(--text-muted)]' : 'text-[var(--text-main)]' }}">{{ $item->nombre ?? 'Platillo' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($cancelado)
                                <span class="text-[11px] md:text-[10px] font-bold text-red-500 uppercase tracking-wide">Cancelado</span>
                            @else
                                @if(($item->estado ?? 'enviado') == 'preparando')
                                    <span class="text-[11px] md:text-[10px] font-bold text-orange-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 shadow-[0_0_5px_rgba(249,115,22,0.5)]"></span> Cocina</span>
                                @elseif(($item->estado ?? 'enviado') == 'listo')
                                    <span class="text-[11px] md:text-[10px] font-bold text-emerald-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span> Listo</span>
                                @else
                                    <span class="text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[var(--text-muted)]"></span> Enviado</span>
                                @endif
                                <button type="button" onclick="cancelarProductoEnviado({{ $item->id }}, this)" class="w-7 h-7 rounded-lg text-red-500 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10">
                <i class="fas fa-clock text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
                <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">No hay órdenes en proceso.</p>
            </div>
        @endif
    </div>

    {{-- VISTA 3: COMANDA TOTAL --}}
    <div id="vista-comanda" class="hidden panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        <div id="items-db-total" class="flex flex-col gap-2">
            @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                <div class="text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] mb-2 px-1 uppercase tracking-wider">Consumo Procesado</div>
                @foreach($platillosEnviados as $item)
                    @php $cancelado = ($item->estado ?? '') === 'cancelado'; @endphp
                    <div class="flex justify-between items-center p-2 rounded-xl hover:bg-[var(--hover-bg)] transition-colors duration-150 {{ $cancelado ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-bold">{{ $item->cantidad ?? 1 }}x</span>
                            <span class="text-[13px] md:text-[12px] font-medium {{ $cancelado ? 'line-through text-[var(--text-muted)]' : 'text-[var(--text-main)]' }}">{{ $item->nombre ?? 'Platillo' }}</span>
                            @if($cancelado)
                                <span class="text-[10px] font-bold text-red-500 uppercase">Cancelado</span>
                            @endif
                        </div>
                        <span class="text-[13px] md:text-[12px] font-bold {{ $cancelado ? 'line-through text-[var(--text-muted)]' : 'text-[var(--text-main)]' }}">${{ number_format(($item->precio ?? 0) * ($item->cantidad ?? 1), 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center mt-2 pt-3 border-t border-[var(--border-color)] px-2">
                    <span class="text-xs md:text-[11px] font-medium text-[var(--text-muted)]">Subtotal en mesa:</span>
                    <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]">
                        ${{ number_format(collect($platillosEnviados)->where('estado', '!=', 'cancelado')->sum(function($i) { return ($i->precio ?? 0) * ($i->cantidad ?? 1); }), 2) }}
                    </span>
                </div>
            @endif
        </div>

        <div id="lista-comanda-total" class="flex flex-col gap-2 mt-4"></div>

        <div id="estadoVacioComanda" class="flex-1 flex-col items-center justify-center opacity-40 mt-10 {{ (isset($platillosEnviados) && count($platillosEnviados) > 0) ? 'hidden' : 'flex' }}">
            <i class="fas fa-receipt text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">No hay cuenta registrada.</p>
        </div>
    </div>

    {{-- FOOTER DE TOTALES --}}
    <div class="p-5 pb-[calc(1.5rem+env(safe-area-inset-bottom))] md:pb-5 border-t border-[var(--border-color)] bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-panel)] flex-shrink-0 z-20 shadow-[0_-10px_30px_rgba(0,0,0,0.06)] relative">
        <div class="flex justify-between items-center mb-1">
            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">Subtotal</span>
            <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
        </div>

        <div class="flex justify-between items-center mb-4">
            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">Propina</span>
            <span class="text-[13px] md:text-[12px] font-bold text-emerald-500" id="txtPropina">$0.00</span>
        </div>

        <div id="bloqueComisionDelivery" class="hidden mb-4 p-2.5 rounded-xl bg-orange-500/10 border border-orange-500/20 space-y-1">
            <p class="text-[10px] font-black uppercase tracking-widest text-orange-500 flex items-center gap-1.5">
                <i class="fas fa-motorcycle"></i>
                <span id="txtPlataformaNombre">Delivery</span>
            </p>
            <div class="flex justify-between items-center">
                <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">
                    Comisión (<span id="txtComisionPorcentaje">0</span>%)
                </span>
                <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]" id="txtComision">$0.00</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">
                    IVA comisión (<span id="txtComisionIvaPorcentaje">0</span>%)
                </span>
                <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]" id="txtComisionIva">$0.00</span>
            </div>
        </div>

        <div class="flex justify-between items-center mb-4 pt-2 border-t border-[var(--border-color)]">
            <span class="text-[13px] md:text-xs text-[var(--text-main)] font-black uppercase tracking-wide">Total</span>
            <span class="text-base md:text-sm font-black text-[var(--text-main)]" id="txtTotalComanda">$0.00</span>
        </div>

        <button type="button" id="btn-enviar" onclick="enviarACocina()" class="w-full h-13 md:h-12 rounded-xl bg-gradient-to-r from-[#b74309] to-[#8f3207] text-white text-[13px] md:text-[12px] font-bold tracking-wide transition-all duration-150 shadow-[0_8px_20px_-5px_rgba(183,67,9,0.5)] hover:shadow-[0_10px_28px_-5px_rgba(183,67,9,0.6)] active:scale-95 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[#b74309] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-panel)]">
            <i class="fas fa-paper-plane text-sm"></i>
            <span>Enviar Orden</span>
        </button>
    </div>
</section>

<style>
    @media (prefers-reduced-motion: no-preference) {
        .panel-fade:not(.hidden) { animation: panelFadeIn .18s ease-out; }
    }
    @keyframes panelFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>