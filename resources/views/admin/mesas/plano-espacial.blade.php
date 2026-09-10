@php
    // Se calculan aquí (no en el controlador) porque este mismo parcial se
    // reutiliza tanto en /mesas (admin) como en /mesero/dashboard, y así
    // funciona sin importar cuál controlador lo esté renderizando.
    $puedeCrearMesa    = auth()->user()->tienePermiso('Mesas', 'crear');
    $puedeEditarMesa   = auth()->user()->tienePermiso('Mesas', 'editar');
    $puedeEliminarMesa = auth()->user()->tienePermiso('Mesas', 'eliminar');
@endphp

{{-- Le pasamos los permisos a mesas.js para que sepa qué botones/acciones habilitar --}}
<script>
    window.permisosMesas = {
        crear: @json($puedeCrearMesa),
        editar: @json($puedeEditarMesa),
        eliminar: @json($puedeEliminarMesa),
    };
</script>

{{-- ESTILOS DIRECTOS PARA EL TECLADO VIRTUAL Y PLANO --}}
<style>
    body.teclado-virtual-abierto #modalCrearMesa {
        align-items: flex-start !important;
        padding-top: 10px !important;
    }

    body.teclado-virtual-abierto #modalCrearMesaContent {
        max-height: 45dvh !important;
        transform: translateY(0) scale(0.95) !important;
    }

    /* Scroll táctil suave (inercia) */
    #planoContenedor {
        -webkit-overflow-scrolling: touch;
        touch-action: none !important;
        cursor: grab;
        user-select: none;
    }

    #planoContenedor.is-dragging {
        cursor: grabbing !important;
        user-select: none !important;
    }

    /* Hoja inferior de propiedades en móvil */
    #panelPropiedades {
        max-height: 85dvh;
    }

    @media (min-width: 1024px) {
        #panelPropiedades {
            max-height: calc(100dvh - 9rem);
        }
    }
</style>

<div class="py-3 sm:py-6 lg:py-8">
    {{-- CABECERA Y FILTROS --}}
    <div class="bg-[var(--card-color)] border-b border-[var(--border-color)] shadow-sm rounded-xl mb-4">
        <div class="max-w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-color)]">Plano Espacial de Mesas</h1>
                    <p class="text-sm sm:text-base text-[var(--text-muted)] mt-1">Gestiona el layout y posición de mesas interactivamente</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- MIS MESAS --}}
                    <button type="button" id="btnMisMesas"
                        class="flex-1 sm:flex-none justify-center px-4 py-2 rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] text-sm font-semibold transition hover:border-[#b74309] hover:text-[#b74309] flex items-center gap-2 shadow-sm cursor-pointer">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Mis mesas</span>
                    </button>

                    {{-- Aviso de caja cerrada --}}
                    @unless($cajaAbierta ?? true)
                        <span class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-xs sm:text-sm font-bold">
                            <i class="fas fa-triangle-exclamation"></i>
                            Caja cerrada — no se pueden levantar pedidos
                        </span>
                    @endunless

                    {{-- DELIVERY --}}
                    @foreach(($plataformasDelivery ?? collect()) as $plataforma)
                        <button type="button"
                            class="btn-delivery flex-1 sm:flex-none justify-center px-3 sm:px-4 py-2 rounded-lg text-white text-sm sm:text-base font-semibold transition shadow-sm active:scale-95 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                            style="background-color: {{ $plataforma->color }}"
                            data-plataforma-id="{{ $plataforma->id }}"
                            data-plataforma-nombre="{{ $plataforma->nombre }}"
                            @unless($cajaAbierta ?? true) disabled title="La caja está cerrada" @else title="Nuevo pedido de {{ $plataforma->nombre }}" @endunless>
                            <i class="fas fa-motorcycle"></i>
                            <span>{{ $plataforma->nombre }}</span>
                        </button>
                    @endforeach

                    {{-- Modo Editar --}}
                    @if($puedeEditarMesa || $puedeCrearMesa || $puedeEliminarMesa)
                        <button type="button" id="btnEditar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 shadow-sm cursor-pointer active:scale-95">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Editar
                        </button>
                    @endif

                    {{-- Guardar posiciones --}}
                    @if($puedeEditarMesa)
                        <button type="button" id="btnGuardar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 hidden shadow-sm cursor-pointer active:scale-95">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7.707 9.293a1 1 0 010 1.414L4.414 14h11.172a1 1 0 110 2H4.414l3.293 3.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z"></path>
                            </svg>
                            Guardar
                        </button>
                    @endif

                    @if($puedeEditarMesa || $puedeCrearMesa || $puedeEliminarMesa)
                        <button type="button" id="btnCancelar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm sm:text-base font-semibold transition hidden shadow-sm flex items-center gap-2 cursor-pointer active:scale-95">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Cancelar
                        </button>
                    @endif
                </div>
            </div>

            {{-- FILA INFERIOR: CONTADOR DE MESAS Y DROPDOWNS DE ZONA Y ESTADO --}}
            <div class="mt-4 flex flex-wrap gap-2.5 items-center">
                {{-- Contador Mesas --}}
                <span id="totalMesas" class="text-xs sm:text-sm font-semibold text-[var(--text-color)] bg-[var(--input-bg)] border border-[var(--border-color)] px-3 py-2 rounded-lg shadow-sm">
                    Mesas: 0
                </span>

                {{-- DROPDOWN: FILTRAR POR ZONA (Entrada, Salón, 2do Piso) --}}
                <div class="relative inline-block text-left" id="dropdownZonaWrapper">
                    <button type="button" 
                            onclick="toggleDropdownZona()" 
                            id="btnDropdownZona"
                            class="px-3 py-2 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-color)] text-xs sm:text-sm font-semibold transition hover:border-[#b74309] hover:text-[#b74309] flex items-center gap-2 shadow-sm cursor-pointer outline-none active:scale-95">
                        <i class="fas fa-layer-group text-[#b74309]"></i>
                        <span id="labelZonaSeleccionada">Zona: Todas</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                    </button>

                    <div id="menuDropdownZona" 
                         class="hidden absolute left-0 mt-1.5 w-44 rounded-xl bg-[var(--card-color)] border border-[var(--border-color)] shadow-2xl z-50 p-1.5 backdrop-blur">
                        <button type="button" onclick="seleccionarZonaDropdown('todas', 'Todas')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Todas las zonas</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-zona" data-val="todas"></i>
                        </button>
                        <button type="button" onclick="seleccionarZonaDropdown('Entrada', 'Entrada')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Entrada</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-zona hidden" data-val="Entrada"></i>
                        </button>
                        <button type="button" onclick="seleccionarZonaDropdown('Salón', 'Salón')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Salón</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-zona hidden" data-val="Salón"></i>
                        </button>
                        <button type="button" onclick="seleccionarZonaDropdown('2do Piso', '2do Piso')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>2do Piso</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-zona hidden" data-val="2do Piso"></i>
                        </button>
                    </div>
                </div>

                {{-- DROPDOWN: FILTRAR POR ESTADO --}}
                <div class="relative inline-block text-left" id="dropdownEstadoWrapper">
                    <button type="button" 
                            onclick="toggleDropdownEstado()" 
                            id="btnDropdownEstado"
                            class="px-3 py-2 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-color)] text-xs sm:text-sm font-semibold transition hover:border-[#b74309] hover:text-[#b74309] flex items-center gap-2 shadow-sm cursor-pointer outline-none active:scale-95">
                        <i class="fas fa-filter text-[#b74309]"></i>
                        <span id="labelEstadoSeleccionado">Estado: Todos</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                    </button>

                    <div id="menuDropdownEstado" 
                         class="hidden absolute left-0 mt-1.5 w-44 rounded-xl bg-[var(--card-color)] border border-[var(--border-color)] shadow-2xl z-50 p-1.5 backdrop-blur">
                        <button type="button" onclick="seleccionarEstadoDropdown('todos', 'Todos')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Todos los estados</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-estado" data-val="todos"></i>
                        </button>
                        <button type="button" onclick="seleccionarEstadoDropdown('libre', 'Libres')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Libres</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-estado hidden" data-val="libre"></i>
                        </button>
                        <button type="button" onclick="seleccionarEstadoDropdown('ocupada', 'Ocupadas')" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-bold text-[var(--text-color)] hover:bg-[#b74309]/10 hover:text-[#b74309] transition-colors flex items-center justify-between cursor-pointer">
                            <span>Ocupadas</span>
                            <i class="fas fa-check text-[10px] text-[#b74309] check-estado hidden" data-val="ocupada"></i>
                        </button>
                    </div>
                </div>

                @if($puedeCrearMesa)
                    <div id="modosEdicion" class="hidden flex gap-2 w-full sm:w-auto sm:ml-auto">
                        <button type="button" id="btnAgregarMesa" class="w-full sm:w-auto justify-center px-4 py-2 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-lg text-sm font-semibold transition flex items-center gap-1 shadow-sm cursor-pointer active:scale-95">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            Agregar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL: MAPA Y PROPIEDADES --}}
    <div class="py-3 sm:py-6 lg:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- MAPA --}}
            <div class="lg:col-span-3">
                <div class="relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl overflow-hidden shadow-lg">

                    {{-- Barra de zoom --}}
                    <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-[var(--border-color)] bg-[var(--input-bg)]">
                        <span class="text-xs font-semibold text-[var(--text-muted)] hidden sm:inline">Toca y arrastra para mover el plano</span>
                        <span class="text-xs font-semibold text-[var(--text-muted)] sm:hidden">Desliza para mover el plano</span>
                        <div class="flex items-center gap-1 ml-auto">
                            <button type="button" id="btnZoomOut" class="w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition font-bold text-lg cursor-pointer">−</button>
                            <span id="zoomLabel" class="text-xs font-semibold text-[var(--text-color)] w-12 text-center select-none">100%</span>
                            <button type="button" id="btnZoomIn" class="w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition font-bold text-lg cursor-pointer">+</button>
                            <button type="button" id="btnZoomReset" class="h-11 sm:h-9 px-3 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition text-xs font-semibold cursor-pointer">Ajustar</button>
                        </div>
                    </div>

                    {{-- SIMBOLOGIA --}}
                    <div class="absolute top-14 right-3 z-20 select-none">
                        <button type="button" id="btnSimbologia"
                            class="w-full flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-t-lg bg-[var(--card-color)]/95 backdrop-blur border border-[var(--border-color)] text-[10px] font-black uppercase tracking-wider text-[var(--text-muted)] hover:text-[var(--text-color)] transition-colors cursor-pointer">
                            <span><i class="fas fa-palette mr-1"></i> Simbología</span>
                            <i id="iconoSimbologia" class="fas fa-chevron-up text-[9px]"></i>
                        </button>

                        <div id="listaSimbologia"
                             class="bg-[var(--card-color)]/95 backdrop-blur border border-t-0 border-[var(--border-color)] rounded-b-lg px-2.5 py-2 space-y-1.5 shadow-lg">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0 border border-black/10"></span>
                                <span class="text-[11px] font-semibold text-[var(--text-color)]">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0 border border-black/10"></span>
                                <span class="text-[11px] font-semibold text-[var(--text-color)]">Mis mesas</span>
                            </div>
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0 border border-black/10"></span>
                                <span class="text-[11px] font-semibold text-[var(--text-color)]">De otro mesero</span>
                            </div>
                        </div>
                    </div>

                    {{-- CONTENEDOR CON SOPORTE DE DESPLAZAMIENTO FLUIDO --}}
                    <div id="planoContenedor"
                         class="relative w-full h-[400px] sm:h-[500px] lg:h-[650px] bg-[var(--input-bg)] overflow-auto shadow-inner">

                        <div id="planoLienzo" class="relative origin-top-left" style="width:1400px; height:900px; transition: transform 0.15s ease-out;">
                            <div id="planoVacio" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center px-6">
                                <svg class="w-14 h-14 mb-3 text-[var(--text-muted)] opacity-40" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zm10 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-[var(--text-muted)] font-semibold text-sm sm:text-base">Todavía no hay mesas en el plano</p>
                                <p class="text-[var(--text-muted)] text-xs sm:text-sm mt-1 opacity-80">Toca "Editar" y luego "Agregar" para crear tu primera mesa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL DE PROPIEDADES --}}
            <div class="lg:col-span-1">
                <div id="panelBackdrop" class="hidden lg:hidden fixed inset-0 bg-black/50 z-30"></div>

                <div id="panelPropiedades"
                    class="fixed inset-x-0 bottom-0 z-40 flex flex-col translate-y-full transition-transform duration-300 ease-out bg-[var(--card-color)] border-t border-[var(--border-color)] rounded-t-2xl shadow-2xl
                    lg:static lg:inset-auto lg:translate-y-0 lg:transition-none lg:z-auto lg:rounded-xl lg:border lg:shadow-lg"
                    style="max-height: 85dvh;">

                    <div class="lg:hidden flex justify-center pt-2 pb-1 shrink-0">
                        <span class="w-10 h-1.5 rounded-full bg-[var(--border-color)]"></span>
                    </div>

                    <div class="flex items-center justify-between px-5 pt-2 lg:pt-5 pb-2 border-b border-[var(--border-color)] shrink-0">
                        <h3 class="text-lg font-bold text-[var(--text-color)]">Propiedades</h3>
                        <button type="button" id="btnCerrarPanelMovil" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-full text-[var(--text-muted)] active:bg-[var(--input-bg)]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-5 py-4 overflow-y-auto flex-1 min-h-0 pb-[calc(env(safe-area-inset-bottom)+1rem)] lg:pb-5">
                        <div id="panelVacio" class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto mb-3 text-[var(--text-muted)] opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-[var(--text-muted)] font-medium">Selecciona una mesa</p>
                        </div>

                        <div id="formularioMesa" class="hidden space-y-4 mt-2">
                            <div>
                                <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Zona / Sección</label>
                                <select id="propSeccion"
                                    class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-[#b74309] shadow-inner transition-colors cursor-pointer">
                                    <option value="Entrada">Entrada</option>
                                    <option value="Salón">Salón</option>
                                    <option value="2do Piso">2do Piso</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Número</label>
                                <input type="text" id="propNumero"
                                    autocomplete="off"
                                    data-teclado="texto"
                                    data-teclado-titulo="Número de Mesa"
                                    data-teclado-max="10"
                                    class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-[#b74309] shadow-inner transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">No. Comensales</label>
                                <input type="text" id="propCapacidad"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    data-teclado="numerico"
                                    data-teclado-titulo="Capacidad"
                                    data-teclado-max="2"
                                    class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-[#b74309] shadow-inner transition-colors">
                            </div>

                            @if($puedeEliminarMesa || $puedeEditarMesa)
                                <div id="botonesAccion" class="pt-4 flex flex-col gap-2 border-t border-[var(--border-color)] mt-2">
                                    @if($puedeEliminarMesa)
                                        <button type="button" id="btnEliminar" class="flex-1 px-3 py-2.5 lg:py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold transition shadow-sm cursor-pointer active:scale-95">
                                            Eliminar
                                        </button>
                                    @endif
                                    @if($puedeEditarMesa)
                                        <button type="button" id="btnActualizar" class="flex-1 px-3 py-2.5 lg:py-2 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-lg text-sm font-semibold transition shadow-sm cursor-pointer active:scale-95">
                                            Actualizar
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR MESA --}}
    @if($puedeCrearMesa)
    <div id="modalCrearMesa" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] p-4 backdrop-blur-sm transition-all duration-300">

        <div id="modalCrearMesaContent" class="bg-[var(--card-color)] rounded-xl shadow-2xl max-w-md w-full border border-[var(--border-color)] max-h-[90vh] flex flex-col transition-all duration-200">

            {{-- HEADER --}}
            <div class="px-6 py-4 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-color)]/50 shrink-0">
                <h2 class="text-xl font-bold text-[var(--text-color)]">Crear Nueva Mesa</h2>
                <button type="button" class="btnCerrarModal text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none cursor-pointer">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 space-y-4 overflow-y-visible">
                {{-- DROPDOWN ZONA / SECCIÓN (Vanilla JS) --}}
                <div class="relative text-left" id="dropdownNewSeccionWrapper">
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">
                        Zona / Sección <span class="text-rose-500">*</span>
                    </label>
                    
                    {{-- Input oculto que lee mesas.js --}}
                    <input type="hidden" id="newSeccion" value="Entrada">

                    <button type="button" 
                            onclick="toggleDropdownNewSeccion(event)" 
                            id="btnNewSeccion"
                            class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] flex items-center justify-between text-sm font-medium outline-none transition-all focus:border-[#b74309] cursor-pointer">
                        <span id="labelNewSeccion">Entrada</span>
                        <i id="iconoNewSeccion" class="fas fa-chevron-down text-xs transition-transform duration-200" style="color: var(--text-muted);"></i>
                    </button>

                    <div id="menuNewSeccion" 
                         class="hidden absolute z-[90] w-full mt-1.5 rounded-lg shadow-2xl py-1.5 border"
                         style="background-color: var(--card-color); border-color: var(--border-color);">
                        <div onclick="seleccionarNewSeccion('Entrada', 'Entrada')"
                             class="item-seccion-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors bg-[#b74309]/15 text-[#b74309] font-bold"
                             data-val="Entrada"
                             style="color: var(--text-color);">
                            <span>Entrada</span>
                        </div>
                        <div onclick="seleccionarNewSeccion('Salón', 'Salón')"
                             class="item-seccion-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors hover:bg-[#b74309]/10"
                             data-val="Salón"
                             style="color: var(--text-color);">
                            <span>Salón</span>
                        </div>
                        <div onclick="seleccionarNewSeccion('2do Piso', '2do Piso')"
                             class="item-seccion-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors hover:bg-[#b74309]/10"
                             data-val="2do Piso"
                             style="color: var(--text-color);">
                            <span>2do Piso</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Número de Mesa <span class="text-rose-500">*</span></label>
                    <input type="text" id="newNumero"
                        autocomplete="off"
                        data-teclado="texto"
                        data-teclado-titulo="Número de Mesa"
                        data-teclado-max="10"
                        class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-[#b74309] shadow-inner transition-all" placeholder="ej: M1, Mesa-1, Table 1" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Capacidad <span class="text-rose-500">*</span></label>
                    <input type="text" id="newCapacidad"
                        inputmode="numeric"
                        autocomplete="off"
                        data-teclado="numerico"
                        data-teclado-titulo="Capacidad"
                        data-teclado-max="2"
                        class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-[#b74309] shadow-inner transition-all" placeholder="4" required>
                </div>

                {{-- DROPDOWN ESTADO INICIAL (Vanilla JS sin Alpine) --}}
                <div class="relative text-left" id="dropdownNewEstadoWrapper">
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Estado Inicial</label>
                    
                    {{-- Input oculto que lee mesas.js --}}
                    <input type="hidden" id="newEstado" value="disponible">

                    <button type="button" 
                            onclick="toggleDropdownNewEstado(event)" 
                            id="btnNewEstado"
                            class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] flex items-center justify-between text-sm font-medium outline-none transition-all focus:border-[#b74309] cursor-pointer">
                        <span id="labelNewEstado">Disponible</span>
                        <i id="iconoNewEstado" class="fas fa-chevron-down text-xs transition-transform duration-200" style="color: var(--text-muted);"></i>
                    </button>

                    <div id="menuNewEstado" 
                         class="hidden absolute z-[80] w-full mt-1.5 rounded-lg shadow-2xl py-1.5 border"
                         style="background-color: var(--card-color); border-color: var(--border-color);">
                        <div onclick="seleccionarNewEstado('disponible', 'Disponible')"
                             class="item-estado-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors bg-[#b74309]/15 text-[#b74309] font-bold"
                             data-val="disponible"
                             style="color: var(--text-color);">
                            <span>Disponible</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-[var(--text-muted)] mt-3">La mesa aparecerá en el plano lista para ser posicionada.</p>
            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-[var(--border-color)] flex gap-3 justify-end bg-[var(--bg-color)]/50 shrink-0">
                <button type="button" class="btnCerrarModal px-4 py-2 bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-color)] hover:opacity-80 rounded-lg font-semibold transition shadow-sm cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarNueva" class="px-4 py-2 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-lg font-semibold transition shadow-sm cursor-pointer active:scale-95">
                    Crear Mesa
                </button>
            </div>
        </div>
    </div>
    @endif

    <div id="notificacion" class="fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold hidden z-50 transition-all shadow-xl"></div>
</div>

{{-- MODAL: MIS MESAS DEL DIA --}}
<div id="modal-mis-mesas" class="hidden fixed inset-0 z-[9998] items-center justify-center p-3 sm:p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-cerrar-mis-mesas></div>

    <div class="relative w-full max-w-2xl bg-[var(--card-color)] rounded-3xl border border-[var(--border-color)] shadow-2xl overflow-hidden max-h-[88vh] flex flex-col">
        <div class="px-5 py-4 border-b border-[var(--border-color)] flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-black text-[var(--text-color)]">Mis mesas</h3>
                <p class="text-[11px] text-[var(--text-muted)]" id="mis-mesas-fecha"></p>
            </div>
            <button type="button" data-cerrar-mis-mesas
                class="w-8 h-8 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-color)] shrink-0 cursor-pointer flex items-center justify-center">&times;</button>
        </div>

        <div class="overflow-y-auto flex-1" id="mis-mesas-contenido">
            <p class="p-8 text-center text-sm text-[var(--text-muted)]">Cargando...</p>
        </div>
    </div>
</div>

<script>
// Control del Dropdown Zona / Sección en el Modal de Crear Mesa (Vanilla JS)
function toggleDropdownNewSeccion(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('menuNewSeccion');
    const icono = document.getElementById('iconoNewSeccion');
    
    // Cierra el de estado si está abierto
    document.getElementById('menuNewEstado')?.classList.add('hidden');
    document.getElementById('iconoNewEstado')?.classList.remove('rotate-180');

    if (!menu) return;
    const estaOculto = menu.classList.toggle('hidden');
    if (icono) icono.classList.toggle('rotate-180', !estaOculto);
}

function seleccionarNewSeccion(valor, etiqueta) {
    const input = document.getElementById('newSeccion');
    const label = document.getElementById('labelNewSeccion');
    const menu = document.getElementById('menuNewSeccion');
    const icono = document.getElementById('iconoNewSeccion');

    if (input) {
        input.value = valor;
        input.dispatchEvent(new Event('change'));
    }
    if (label) label.textContent = etiqueta;
    if (menu) menu.classList.add('hidden');
    if (icono) icono.classList.remove('rotate-180');

    document.querySelectorAll('.item-seccion-option').forEach(item => {
        const coincide = item.getAttribute('data-val') === valor;
        if (coincide) {
            item.className = "item-seccion-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors bg-[#b74309]/15 text-[#b74309] font-bold";
        } else {
            item.className = "item-seccion-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors hover:bg-[#b74309]/10";
        }
    });
}

// Control del Dropdown Estado Inicial en el Modal de Crear Mesa (Vanilla JS)
function toggleDropdownNewEstado(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('menuNewEstado');
    const icono = document.getElementById('iconoNewEstado');
    
    // Cierra el de sección si está abierto
    document.getElementById('menuNewSeccion')?.classList.add('hidden');
    document.getElementById('iconoNewSeccion')?.classList.remove('rotate-180');

    if (!menu) return;
    const estaOculto = menu.classList.toggle('hidden');
    if (icono) icono.classList.toggle('rotate-180', !estaOculto);
}

function seleccionarNewEstado(valor, etiqueta) {
    const input = document.getElementById('newEstado');
    const label = document.getElementById('labelNewEstado');
    const menu = document.getElementById('menuNewEstado');
    const icono = document.getElementById('iconoNewEstado');

    if (input) {
        input.value = valor;
        input.dispatchEvent(new Event('change'));
    }
    if (label) label.textContent = etiqueta;
    if (menu) menu.classList.add('hidden');
    if (icono) icono.classList.remove('rotate-180');

    document.querySelectorAll('.item-estado-option').forEach(item => {
        const coincide = item.getAttribute('data-val') === valor;
        if (coincide) {
            item.className = "item-estado-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors bg-[#b74309]/15 text-[#b74309] font-bold";
        } else {
            item.className = "item-estado-option px-3 py-2 text-sm font-medium cursor-pointer transition-colors hover:bg-[#b74309]/10";
        }
    });
}

// Control de apertura y cierre de Dropdowns en cabecera
function toggleDropdownZona() {
    const menu = document.getElementById('menuDropdownZona');
    const menuEstado = document.getElementById('menuDropdownEstado');
    if (menuEstado) menuEstado.classList.add('hidden');
    if (menu) menu.classList.toggle('hidden');
}

function toggleDropdownEstado() {
    const menu = document.getElementById('menuDropdownEstado');
    const menuZona = document.getElementById('menuDropdownZona');
    if (menuZona) menuZona.classList.add('hidden');
    if (menu) menu.classList.toggle('hidden');
}

// Cerrar cualquier dropdown al hacer click fuera
document.addEventListener('click', (e) => {
    const dropZona = document.getElementById('dropdownZonaWrapper');
    const dropEstado = document.getElementById('dropdownEstadoWrapper');
    const dropNewSeccion = document.getElementById('dropdownNewSeccionWrapper');
    const dropNewEstado = document.getElementById('dropdownNewEstadoWrapper');

    if (dropZona && !dropZona.contains(e.target)) {
        document.getElementById('menuDropdownZona')?.classList.add('hidden');
    }
    if (dropEstado && !dropEstado.contains(e.target)) {
        document.getElementById('menuDropdownEstado')?.classList.add('hidden');
    }
    if (dropNewSeccion && !dropNewSeccion.contains(e.target)) {
        document.getElementById('menuNewSeccion')?.classList.add('hidden');
        document.getElementById('iconoNewSeccion')?.classList.remove('rotate-180');
    }
    if (dropNewEstado && !dropNewEstado.contains(e.target)) {
        document.getElementById('menuNewEstado')?.classList.add('hidden');
        document.getElementById('iconoNewEstado')?.classList.remove('rotate-180');
    }
});

// Selección de Zona
function seleccionarZonaDropdown(valor, etiqueta) {
    const label = document.getElementById('labelZonaSeleccionada');
    if (label) label.textContent = `Zona: ${etiqueta}`;
    document.getElementById('menuDropdownZona')?.classList.add('hidden');

    document.querySelectorAll('.check-zona').forEach(el => {
        el.classList.toggle('hidden', el.getAttribute('data-val') !== valor);
    });

    if (typeof window.filtrarPorSeccion === 'function') {
        window.filtrarPorSeccion(valor, null);
    }
}

// Selección de Estado
function seleccionarEstadoDropdown(valor, etiqueta) {
    const label = document.getElementById('labelEstadoSeleccionado');
    if (label) label.textContent = `Estado: ${etiqueta}`;
    document.getElementById('menuDropdownEstado')?.classList.add('hidden');

    document.querySelectorAll('.check-estado').forEach(el => {
        el.classList.toggle('hidden', el.getAttribute('data-val') !== valor);
    });

    if (typeof window.filtrarMesasPorEstado === 'function') {
        window.filtrarMesasPorEstado(valor, null);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // --- CONTROL DE ARRASTRE BIDIRECCIONAL EN EL PLANO (PANNING) ---
    const contenedor = document.getElementById('planoContenedor');
    if (contenedor) {
        let isDown = false;
        let startX, startY, scrollLeft, scrollTop;

        const onStart = (clientX, clientY) => {
            isDown = true;
            contenedor.classList.add('is-dragging');
            startX = clientX - contenedor.offsetLeft;
            startY = clientY - contenedor.offsetTop;
            scrollLeft = contenedor.scrollLeft;
            scrollTop = contenedor.scrollTop;
        };

        const onMove = (clientX, clientY) => {
            if (!isDown) return;
            const x = clientX - contenedor.offsetLeft;
            const y = clientY - contenedor.offsetTop;
            const walkX = (x - startX);
            const walkY = (y - startY);
            contenedor.scrollLeft = scrollLeft - walkX;
            contenedor.scrollTop = scrollTop - walkY;
        };

        const onEnd = () => {
            isDown = false;
            contenedor.classList.remove('is-dragging');
        };

        // Eventos de ratón
        contenedor.addEventListener('mousedown', (e) => {
            if (e.target.closest('.mesa-item, .mesa-elemento') && document.body.classList.contains('modo-edicion')) return;
            onStart(e.pageX, e.pageY);
        });
        window.addEventListener('mousemove', (e) => onMove(e.pageX, e.pageY));
        window.addEventListener('mouseup', onEnd);

        // Eventos táctiles (Móviles / Tablets)
        contenedor.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                if (e.target.closest('.mesa-item, .mesa-elemento') && document.body.classList.contains('modo-edicion')) return;
                onStart(e.touches[0].pageX, e.touches[0].pageY);
            }
        }, { passive: true });

        contenedor.addEventListener('touchmove', (e) => {
            if (e.touches.length === 1 && isDown) {
                onMove(e.touches[0].pageX, e.touches[0].pageY);
            }
        }, { passive: true });

        contenedor.addEventListener('touchend', onEnd);
        contenedor.addEventListener('touchcancel', onEnd);
    }

    // Modal Mis Mesas
    const modal = document.getElementById('modal-mis-mesas');
    const btn = document.getElementById('btnMisMesas');
    if (!modal || !btn) return;

    const contenido = document.getElementById('mis-mesas-contenido');
    const cerrar = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    modal.querySelectorAll('[data-cerrar-mis-mesas]').forEach(el => el.addEventListener('click', cerrar));

    const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const URL_MIS_MESAS = @json(route('mesero.mis-mesas'));
    const URL_DETALLE_BASE = @json(url('/mesero/mis-mesas'));

    function pintarGrupo(titulo, icono, color, filas, tipo) {
        if (!filas.length) return '';

        let html = '<div class="px-4 pt-4">'
            + '<p class="text-[10px] font-black uppercase tracking-widest ' + color + ' mb-2">'
            + '<i class="fas ' + icono + ' mr-1"></i>' + titulo + ' (' + filas.length + ')</p>'
            + '<div class="space-y-2">';

        filas.forEach(f => {
            html += '<div class="border border-[var(--border-color)] rounded-xl overflow-hidden">'
                + '<button type="button" class="btn-mi-mesa w-full px-3 py-2.5 flex items-center justify-between gap-2 text-left hover:bg-[var(--input-bg)] transition-colors cursor-pointer" data-orden="' + f.orden_id + '">'
                + '<div class="min-w-0">'
                + '<span class="font-black text-[var(--text-color)]">Mesa ' + f.mesa + '</span>'
                + '<span class="text-[10px] text-[var(--text-muted)] ml-2">' + (f.numero || '') + '</span>'
                + '<div class="text-[10px] text-[var(--text-muted)] mt-0.5">'
                + (f.personas ? f.personas + ' pers. · ' : '')
                + 'abierta ' + (f.abierta || '--')
                + (f.cerrada ? ' · cerrada ' + f.cerrada : '')
                + (tipo === 'canceladas' && f.motivo ? '<br><span class="text-rose-500">' + f.motivo + '</span>' : '')
                + '</div></div>'
                + '<div class="text-right shrink-0">'
                + '<div class="font-black text-[var(--text-color)]">' + dinero(f.total) + '</div>'
                + (tipo === 'cobradas'
                    ? '<div class="text-[9px] text-[var(--text-muted)]">'
                        + (f.efectivo > 0 ? 'efvo ' + dinero(f.efectivo) + ' ' : '')
                        + (f.tarjeta > 0 ? 'tarj ' + dinero(f.tarjeta) + ' ' : '')
                        + (f.transferencia > 0 ? 'transf ' + dinero(f.transferencia) : '')
                        + '</div>'
                    : '')
                + '</div></button>'
                + '<div class="detalle-mi-mesa hidden border-t border-[var(--border-color)]"></div>'
                + '</div>';
        });

        return html + '</div></div>';
    }

    async function abrirDetalle(bloque, ordenId) {
        bloque.classList.remove('hidden');
        bloque.innerHTML = '<p class="px-3 py-3 text-xs text-[var(--text-muted)]">Cargando...</p>';

        try {
            const res = await fetch(URL_DETALLE_BASE + '/' + ordenId + '/detalle', { headers: {'Accept':'application/json'} });
            const d = await res.json();

            if (!res.ok || !d.success) {
                bloque.innerHTML = '<p class="px-3 py-3 text-xs text-rose-500">' + (d.message || 'No se pudo cargar.') + '</p>';
                return;
            }

            if (!d.productos.length) {
                bloque.innerHTML = '<p class="px-3 py-3 text-xs text-[var(--text-muted)]">Sin productos capturados todavía.</p>';
                return;
            }

            let html = '<table class="w-full text-xs"><tbody class="divide-y divide-[var(--border-color)]">';
            d.productos.forEach(p => {
                html += '<tr class="' + (p.cancelado ? 'line-through opacity-50' : '') + '">'
                    + '<td class="py-2 px-3 text-[var(--text-color)]">' + p.producto
                    + (p.cancelado ? ' <span class="text-rose-500 font-bold text-[9px] no-underline">CANCELADO</span>' : '')
                    + (p.notas ? '<div class="text-[10px] text-[var(--text-muted)] italic">' + p.notas + '</div>' : '')
                    + '</td>'
                    + '<td class="py-2 px-2 text-center w-12 text-[var(--text-muted)]">x' + p.cantidad + '</td>'
                    + '<td class="py-2 px-3 text-right w-24 font-bold text-[var(--text-color)]">' + dinero(p.importe) + '</td>'
                    + '</tr>';
            });
            html += '</tbody><tfoot><tr class="border-t border-[var(--border-color)]">'
                + '<td colspan="2" class="py-2 px-3 text-right text-[10px] font-black uppercase tracking-wider text-[var(--text-muted)]">Consumo</td>'
                + '<td class="py-2 px-3 text-right font-black text-[var(--text-color)]">' + dinero(d.consumo) + '</td></tr>';
            if (d.propina > 0) {
                html += '<tr><td colspan="2" class="py-1.5 px-3 text-right text-[10px] font-black uppercase tracking-wider text-emerald-500">Propina</td>'
                    + '<td class="py-1.5 px-3 text-right font-black text-emerald-500">' + dinero(d.propina) + '</td></tr>';
            }
            html += '</tfoot></table>';
            bloque.innerHTML = html;

        } catch (e) {
            console.error('Error al cargar el detalle de la mesa:', e);
            bloque.innerHTML = '<p class="px-3 py-3 text-xs text-rose-500">Error de conexión.</p>';
        }
    }

    btn.addEventListener('click', async () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        contenido.innerHTML = '<p class="p-8 text-center text-sm text-[var(--text-muted)]">Cargando...</p>';

        try {
            const res = await fetch(URL_MIS_MESAS, { headers: {'Accept':'application/json'} });
            const d = await res.json();

            if (!res.ok || !d.success) {
                contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">No se pudo cargar.</p>';
                return;
            }

            document.getElementById('mis-mesas-fecha').textContent = d.fecha;
            const t = d.totales;

            let html = '<div class="px-4 pt-4 grid grid-cols-2 sm:grid-cols-4 gap-2">'
                + '<div class="rounded-xl border border-[var(--border-color)] p-2.5 text-center"><p class="text-[9px] font-black uppercase tracking-wider text-[var(--text-muted)]">Atendidas</p><p class="text-lg font-black text-[var(--text-color)]">' + t.mesas_atendidas + '</p></div>'
                + '<div class="rounded-xl border border-[var(--border-color)] p-2.5 text-center"><p class="text-[9px] font-black uppercase tracking-wider text-[var(--text-muted)]">Abiertas</p><p class="text-lg font-black text-amber-500">' + t.abiertas + '</p></div>'
                + '<div class="rounded-xl border border-[var(--border-color)] p-2.5 text-center"><p class="text-[9px] font-black uppercase tracking-wider text-[var(--text-muted)]">Vendido</p><p class="text-lg font-black text-emerald-500">' + dinero(t.vendido) + '</p></div>'
                + '<div class="rounded-xl border border-[var(--border-color)] p-2.5 text-center"><p class="text-[9px] font-black uppercase tracking-wider text-[var(--text-muted)]">Por cobrar</p><p class="text-lg font-black text-[#b74309]">' + dinero(t.por_cobrar) + '</p></div>'
                + '</div>';

            html += pintarGrupo('Atendiendo ahora', 'fa-utensils', 'text-amber-500', d.grupos.atendiendo, 'atendiendo');
            html += pintarGrupo('Ya cobradas', 'fa-circle-check', 'text-emerald-500', d.grupos.cobradas, 'cobradas');
            html += pintarGrupo('Canceladas', 'fa-ban', 'text-rose-500', d.grupos.canceladas, 'canceladas');

            if (t.mesas_atendidas === 0) {
                html += '<div class="p-10 text-center"><i class="fas fa-mug-hot text-3xl text-[var(--text-muted)] opacity-30 mb-2"></i>'
                    + '<p class="text-sm font-bold text-[var(--text-muted)]">Todavía no has abierto mesas hoy</p></div>';
            }

            html += '<div class="h-4"></div>';
            contenido.innerHTML = html;

            contenido.querySelectorAll('.btn-mi-mesa').forEach(b => {
                b.addEventListener('click', () => {
                    const bloque = b.nextElementSibling;
                    if (!bloque.classList.contains('hidden')) { bloque.classList.add('hidden'); return; }
                    abrirDetalle(bloque, b.dataset.orden);
                });
            });

        } catch (e) {
            console.error('Error al cargar mis mesas:', e);
            contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">Error de conexión.</p>';
        }
    });
});
</script>

{{-- SCRIPTS --}}
@push('scripts')
   @vite(['resources/js/mesas.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TecladoVirtual !== 'undefined') {
                TecladoVirtual.attachAll();
            }

            const btnSimbologia = document.getElementById('btnSimbologia');
            const listaSimbologia = document.getElementById('listaSimbologia');
            const iconoSimbologia = document.getElementById('iconoSimbologia');

            if (btnSimbologia && listaSimbologia) {
                btnSimbologia.addEventListener('click', () => {
                    const oculta = listaSimbologia.classList.toggle('hidden');
                    btnSimbologia.classList.toggle('rounded-b-lg', oculta);
                    if (iconoSimbologia) {
                        iconoSimbologia.classList.toggle('fa-chevron-up', !oculta);
                        iconoSimbologia.classList.toggle('fa-chevron-down', oculta);
                    }
                });

                if (window.innerWidth < 640) btnSimbologia.click();
            }

            document.querySelectorAll('.btn-delivery').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const plataformaId = btn.dataset.plataformaId;
                    const nombre = btn.dataset.plataformaNombre;

                    document.querySelectorAll('.btn-delivery').forEach(b => b.disabled = true);
                    const textoOriginal = btn.innerHTML;
                    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Abriendo ${nombre}...`;

                    try {
                        const res = await fetch('{{ route("mesero.delivery.crear") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ plataforma_delivery_id: plataformaId }),
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || `No se pudo abrir el pedido de ${nombre}.`);
                            document.querySelectorAll('.btn-delivery').forEach(b => b.disabled = false);
                            btn.innerHTML = textoOriginal;
                        }
                    } catch (e) {
                        console.error('Error al crear pedido de delivery:', e);
                        alert(`Error de conexión al abrir el pedido de ${nombre}.`);
                        document.querySelectorAll('.btn-delivery').forEach(b => b.disabled = false);
                        btn.innerHTML = textoOriginal;
                    }
                });
            });
        });
    </script>
@endpush