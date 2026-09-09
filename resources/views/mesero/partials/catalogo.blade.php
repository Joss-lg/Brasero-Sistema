{{-- ============================================================
     CATÁLOGO — Panel de productos (Minimalista, sin imágenes)
     ============================================================ --}}
<section id="col-catalogo"
    class="col-mobile-panel flex w-full md:flex-1 md:min-w-[280px] h-full flex-col bg-[var(--bg-base)] border-l md:border-l-0 md:border-r border-[var(--border-color)] z-20">

    {{-- Encabezado: título + categorías --}}
    <div class="p-3 sm:p-4 border-b border-[var(--border-color)] bg-[var(--bg-base)]/95 backdrop-blur supports-[backdrop-filter]:bg-[var(--bg-base)]/80 sticky top-0 z-10">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">Menú</p>
                <h2 class="text-base sm:text-lg font-black text-[var(--text-main)] leading-tight truncate">Catálogo</h2>
            </div>
            <div class="flex-shrink-0 rounded-full border border-[var(--border-color)] bg-[var(--bg-panel)] px-3 py-1.5 text-[10px] font-semibold text-[var(--text-muted)] whitespace-nowrap">
                <i class="fas fa-utensils mr-1"></i> {{ count($productos ?? []) }} Productos
            </div>
        </div>

        {{-- BUSCADOR --}}
        <div class="mt-3 relative">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] text-xs pointer-events-none"></i>
            <input type="text" id="buscadorProductos"
                   placeholder="Buscar platillo..."
                   autocomplete="off"
                   class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--bg-panel)] text-sm font-semibold text-[var(--text-main)] placeholder:text-[var(--text-muted)] placeholder:font-normal outline-none focus:border-[#3b82f6] transition-colors">
            <button type="button" id="limpiarBusquedaProductos"
                    class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"
                    title="Limpiar búsqueda">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- Categorías --}}
        <div id="menuCategorias"
             class="mt-3 flex items-center gap-2 overflow-x-auto hide-scroll pb-1 -mx-1 px-1 snap-x snap-mandatory">
            {{-- Los botones de categoría se inyectan dinámicamente --}}
        </div>
    </div>

    {{-- Aviso sin resultados --}}
    <div id="catalogoSinResultados" class="hidden px-4 py-10 text-center">
        <i class="fas fa-magnifying-glass text-3xl text-[var(--text-muted)] opacity-30 mb-2"></i>
        <p class="text-sm font-bold text-[var(--text-muted)]">Sin resultados</p>
        <p class="text-xs text-[var(--text-muted)] opacity-70 mt-0.5">Prueba con otras letras o cambia de categoría.</p>
    </div>

    {{-- Cuadrícula de productos --}}
    <div id="gridProductos"
         class="flex-1 min-h-0 overflow-y-auto hide-scroll overscroll-contain
                p-3 sm:p-4 pb-[calc(6.5rem+env(safe-area-inset-bottom))] md:pb-4
                grid grid-cols-2 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-[repeat(auto-fill,minmax(140px,1fr))] lg:grid-cols-[repeat(auto-fill,minmax(160px,1fr))]
                gap-3 sm:gap-4
                content-start auto-rows-min">

        @forelse($productos ?? [] as $producto)
        @php
            $variantesList = $producto->variantes ?? collect();
            $tieneVariantes = (bool)($producto->tiene_variantes || $variantesList->isNotEmpty());
            
            if ($tieneVariantes && $variantesList->isNotEmpty()) {
                $precioMostrar = $variantesList->min('precio') ?? 0;
            } else {
                $precioMostrar = $producto->precio ?? $producto->precio_por_100g ?? 0;
            }

            $variantesJsonAttr = htmlspecialchars($variantesList->toJson(), ENT_QUOTES, 'UTF-8');
            $nombreAttr = htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8');
        @endphp

        <button type="button"
            data-producto-id="{{ $producto->id }}"
            data-producto-nombre="{{ $nombreAttr }}"
            data-tiene-variantes="{{ $tieneVariantes ? '1' : '0' }}"
            data-variantes="{{ $variantesJsonAttr }}"
            onclick="gestionarClickProducto(this)"
            class="btn-producto group relative flex flex-col justify-between text-left rounded-[20px] border border-blue-200/60
                   bg-white p-4 shadow-sm min-h-[130px]
                   hover:border-blue-500 hover:shadow-md hover:-translate-y-0.5
                   active:scale-[0.97] active:translate-y-0
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60
                   transition-all duration-150">

            {{-- Nombre del producto y Badge de Variantes --}}
            <div>
                <h3 class="text-[14px] sm:text-[15px] font-black text-slate-900 leading-tight uppercase mb-1 pr-2">
                    {{ $producto->nombre }}
                </h3>
                @if($tieneVariantes)
                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md uppercase tracking-wider mb-2">
                        <i class="fas fa-layer-group text-[8px]"></i> Elige proteína
                    </span>
                @endif
            </div>

            {{-- Precio y Botón Agregar --}}
            <div class="mt-auto flex items-center justify-between gap-2 w-full">
                <div>
                    @if($tieneVariantes)
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Desde</span>
                    @endif
                    <p class="text-[16px] sm:text-[18px] font-black text-slate-900 leading-none tracking-tight">
                        ${{ number_format($precioMostrar, 2) }}
                    </p>
                </div>

                <span class="flex-shrink-0 w-9 h-9 rounded-full bg-[#3b82f6] text-white
                             flex items-center justify-center text-sm font-bold
                             shadow-sm group-hover:bg-blue-600 group-active:scale-90
                             transition-all duration-150">
                    <i class="fas {{ $tieneVariantes ? 'fa-list-ul' : 'fa-plus' }}"></i>
                </span>
            </div>
        </button>
        @empty
        <div class="col-span-full flex flex-col items-center justify-center gap-3 py-16 text-center">
            <span class="w-14 h-14 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center">
                <i class="fas fa-box-open text-xl text-[var(--text-muted)]"></i>
            </span>
            <div>
                <p class="text-sm font-bold text-[var(--text-main)]">Sin productos en esta categoría</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">Prueba con otra categoría del menú superior.</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- MINI-BARRA DE ORDEN ACTIVA (solo móvil) --}}
    <button type="button" id="miniCartBar" onclick="toggleOrdenMobile()"
        class="hidden md:hidden fixed left-3 right-3 z-40 items-center justify-between gap-3
               rounded-2xl bg-[var(--text-main)] text-[var(--bg-base)]
               px-4 py-3 shadow-[0_10px_30px_-8px_rgba(0,0,0,0.4)]
               active:scale-[0.98] transition-transform duration-150"
        style="bottom: calc(68px + env(safe-area-inset-bottom));">
        <span class="flex items-center gap-2 min-w-0">
            <span id="miniCartCount" class="shrink-0 w-6 h-6 rounded-full bg-[var(--bg-base)] text-[var(--text-main)] text-[11px] font-black flex items-center justify-center">0</span>
            <span class="text-[12px] font-bold truncate">Ver orden</span>
        </span>
        <span class="flex items-center gap-2 shrink-0">
            <span id="miniCartTotal" class="text-[13px] font-black">$0.00</span>
            <i class="fas fa-chevron-up text-[11px] opacity-70"></i>
        </span>
    </button>

    {{-- ============================================================
         TECLADO VIRTUAL INTEGRADO (Touch POS)
         ============================================================ --}}
    <div id="teclado-virtual-overlay"
         class="hidden fixed inset-0 z-[9999]"
         onclick="if(event.target===this) cerrarTecladoVirtual()">

        <div id="teclado-virtual"
             class="absolute bottom-0 inset-x-0 bg-[var(--bg-base)] border-t border-[var(--border-color)] shadow-2xl rounded-t-3xl pb-safe">

            {{-- Barra superior del teclado --}}
            <div class="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-[var(--border-color)]">
                <div class="flex-1 flex items-center gap-2 bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl px-3 py-2.5 min-h-[40px]">
                    <i class="fas fa-magnifying-glass text-[var(--text-muted)] text-xs shrink-0"></i>
                    <span id="tv-display" class="flex-1 text-sm font-semibold text-[var(--text-main)] break-all"></span>
                    <span class="w-0.5 h-4 bg-blue-500 animate-pulse rounded-full"></span>
                </div>
                <button type="button" onclick="cerrarTecladoVirtual()"
                    class="w-10 h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] flex items-center justify-center shrink-0 active:scale-95">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Teclado QWERTY --}}
            <div class="px-2 py-3 space-y-1.5 select-none">
                @php
                    $filas = [
                        ['Q','W','E','R','T','Y','U','I','O','P'],
                        ['A','S','D','F','G','H','J','K','L'],
                        ['Z','X','C','V','B','N','M'],
                    ];
                @endphp

                @foreach($filas as $fila)
                    <div class="flex justify-center gap-1">
                        @foreach($fila as $letra)
                            <button type="button"
                                onclick="tvEscribir('{{ $letra }}')"
                                class="tv-key flex-1 max-w-[38px] h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 active:bg-blue-100 dark:active:bg-blue-500/20 transition-all duration-75">
                                {{ $letra }}
                            </button>
                        @endforeach
                    </div>
                @endforeach

                {{-- Fila numérica e inferior --}}
                <div class="flex justify-center gap-1 mt-1">
                    <button type="button" onclick="tvEscribir('1')" class="tv-key w-10 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">1</button>
                    <button type="button" onclick="tvEscribir('2')" class="tv-key w-10 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">2</button>
                    <button type="button" onclick="tvEscribir('3')" class="tv-key w-10 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">3</button>
                    <button type="button" onclick="tvEscribir(' ')"
                        class="tv-key flex-1 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 font-bold text-xs shadow-sm active:scale-95 transition-all duration-75">
                        ESPACIO
                    </button>
                    <button type="button" onclick="tvEscribir('4')" class="tv-key w-10 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">4</button>
                    <button type="button" onclick="tvEscribir('5')" class="tv-key w-10 h-11 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">5</button>
                    <button type="button"
                        onclick="tvBorrar()"
                        class="tv-key w-14 h-11 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-500 shadow-sm active:scale-95 flex items-center justify-center transition-all duration-75">
                        <i class="fas fa-delete-left text-base"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
    let tvValor = '';
    const overlay   = document.getElementById('teclado-virtual-overlay');
    const display   = document.getElementById('tv-display');
    const inputReal = document.getElementById('buscadorProductos');

    window.abrirTecladoVirtual = function () {
        tvValor = inputReal.value || '';
        display.textContent = tvValor;
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.cerrarTecladoVirtual = function () {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        inputReal.value = tvValor;
        inputReal.dispatchEvent(new Event('input', { bubbles: true }));
    };

    function necesitaTecladoVirtual() {
        return window.innerWidth > 768;
    }

    if (inputReal) {
        inputReal.addEventListener('focus', function (e) {
            if (necesitaTecladoVirtual()) {
                e.preventDefault();
                inputReal.blur();
                abrirTecladoVirtual();
            }
        });

        inputReal.addEventListener('click', function (e) {
            if (necesitaTecladoVirtual()) {
                e.preventDefault();
                inputReal.blur();
                abrirTecladoVirtual();
            }
        });
    }

    window.abrirTecladoVirtual_orig = window.abrirTecladoVirtual;
    window.abrirTecladoVirtual = function () {
        window.abrirTecladoVirtual_orig();
        window.setInputVirtualActivo && window.setInputVirtualActivo('buscadorProductos');
        document._tvKeyHandler = function (e) {
            if (!document.getElementById('teclado-virtual-overlay') ||
                document.getElementById('teclado-virtual-overlay').classList.contains('hidden')) return;
            if (e.key === 'Backspace') { e.preventDefault(); tvBorrar(); }
            else if (e.key === 'Escape') { e.preventDefault(); cerrarTecladoVirtual(); }
            else if (e.key === 'Enter') { e.preventDefault(); cerrarTecladoVirtual(); }
            else if (e.key.length === 1) { e.preventDefault(); tvEscribir(e.key.toUpperCase()); }
        };
        document.addEventListener('keydown', document._tvKeyHandler);
    };

    window.cerrarTecladoVirtual_orig = window.cerrarTecladoVirtual;
    window.cerrarTecladoVirtual = function () {
        window.cerrarTecladoVirtual_orig();
        window.clearInputVirtualActivo && window.clearInputVirtualActivo();
        if (document._tvKeyHandler) {
            document.removeEventListener('keydown', document._tvKeyHandler);
            document._tvKeyHandler = null;
        }
    };

    window.tvEscribir = function (char) {
        tvValor += char;
        display.textContent = tvValor;
        inputReal.value = tvValor;
        inputReal.dispatchEvent(new Event('input', { bubbles: true }));
    };

    window.tvBorrar = function () {
        tvValor = tvValor.slice(0, -1);
        display.textContent = tvValor;
        inputReal.value = tvValor;
        inputReal.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const btnLimpiar = document.getElementById('limpiarBusquedaProductos');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => { tvValor = ''; });
    }
})();

// ============================================================
// LÓGICA DE DETECCIÓN Y SELECCIÓN DE VARIANTES EN COMANDAS
// ============================================================
function gestionarClickProducto(btn) {
    const productoId     = btn.getAttribute('data-producto-id');
    const productoNombre = btn.getAttribute('data-producto-nombre');
    const variantesRaw   = btn.getAttribute('data-variantes');

    let variantes = [];
    try {
        variantes = JSON.parse(variantesRaw || '[]');
    } catch (e) {
        variantes = [];
    }

    const tieneVariantes = btn.getAttribute('data-tiene-variantes') === '1' || variantes.length > 0;

    if (tieneVariantes && variantes.length > 0) {
        abrirModalVariante(productoId, productoNombre, variantes);
    } else {
        if (typeof window.seleccionarItemCatalogo === 'function') {
            window.seleccionarItemCatalogo(parseInt(productoId, 10));
        } else if (typeof window.agregarAlTicket === 'function') {
            window.agregarAlTicket(productoId, productoNombre, 0, '', []);
        }
    }
}

function abrirModalVariante(productoId, nombrePlatillo, variantes) {
    const modal = document.getElementById('modalVariantesProducto');
    if (!modal) return;

    const titulo = modal.querySelector('#modalVariantesTitulo');
    const lista  = modal.querySelector('#modalVariantesLista');

    if (titulo) titulo.textContent = nombrePlatillo;
    if (!lista) return;

    lista.innerHTML = '';

    let items = variantes;
    if (typeof items === 'string') {
        try { items = JSON.parse(items); } catch(e) { items = []; }
    }

    if (!items || items.length === 0) {
        lista.innerHTML = '<p class="text-xs text-center text-[var(--text-muted)] py-4">No hay opciones registradas.</p>';
    } else {
        items.forEach(variante => {
            const itemBtn = document.createElement('button');
            itemBtn.type = 'button';
            itemBtn.className = 'w-full flex items-center justify-between p-3.5 rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] hover:border-[#b74309] hover:bg-[#b74309]/5 active:scale-[0.98] transition-all shadow-sm text-left group cursor-pointer';
            itemBtn.innerHTML = `
                <span class="text-sm font-bold text-[var(--text-main)] group-hover:text-[#b74309] transition-colors uppercase">
                    ${variante.nombre}
                </span>
                <span class="text-sm font-black text-[#b74309]">
                    $${parseFloat(variante.precio).toFixed(2)}
                </span>
            `;

            itemBtn.onclick = () => {
                seleccionarVariante(productoId, variante);
            };

            lista.appendChild(itemBtn);
        });
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModalVariantes() {
    const modal = document.getElementById('modalVariantesProducto');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function seleccionarVariante(productoId, variante) {
    cerrarModalVariantes();

    if (typeof window.agregarProductoConVariante === 'function') {
        window.agregarProductoConVariante(productoId, variante);
    } else if (typeof window.agregarAlTicket === 'function') {
        window.agregarAlTicket(productoId, variante.nombre, parseFloat(variante.precio), '', []);
    }
}
</script>
</section>