<style>
    @media (prefers-reduced-motion: no-preference) {
        .modal-overlay:not(.hidden) { animation: modalFadeIn .18s ease-out; }
        .modal-sheet { animation: sheetSlideUp .22s cubic-bezier(.25,.8,.35,1); }
        @media (min-width: 640px) {
            .modal-sheet { animation: sheetPopIn .18s cubic-bezier(.25,.8,.35,1); }
        }
    }
    @keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes sheetSlideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes sheetPopIn { from { transform: scale(.97); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>

{{-- ==========================================
     13. MODAL SELECCIÓN DE VARIANTE / PROTEÍNA
     ========================================== --}}
<div id="modalVariantesProducto" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-[#b74309]/15 to-[#b74309]/5 border border-[#b74309]/20 flex items-center justify-center">
                    <i class="fas fa-layer-group text-[#b74309] text-xs"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-[#b74309] font-bold">Selección</p>
                    <h2 id="modalVariantesTitulo" class="text-base sm:text-lg font-bold text-[var(--text-main)] leading-tight truncate">Platillo</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModalVariantes()" class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <p class="text-[12px] text-[var(--text-muted)] mb-3">Elige la opción o proteína deseada para este platillo:</p>

        {{-- Contenedor dinámico de las variantes --}}
        <div id="modalVariantesLista" class="flex flex-col gap-2 max-h-[45vh] sm:max-h-[300px] overflow-y-auto hide-scroll pb-1"></div>

        <div class="mt-5 flex justify-end">
            <button type="button" onclick="cerrarModalVariantes()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 transition-all duration-150">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- ==========================================
     1. MODAL NIP CAPITÁN (Teclado Numérico Virtual)
     ========================================== --}}
<div id="modalNip" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/15 to-blue-500/5 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-lock text-blue-500 text-xs"></i>
                </span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">NIP Administrador</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalNip')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        
        <input type="password" id="nipInput"
               maxlength="6" autocomplete="off"
               data-solo-numeros="true"
               data-teclado-virtual="true"
               class="w-full min-h-[64px] rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-2xl sm:text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 tracking-[0.3em]"
               placeholder="••••">
               
        {{-- Teclado Numérico --}}
        <div class="grid grid-cols-3 gap-1.5 mt-4">
            @foreach(['1','2','3','4','5','6','7','8','9'] as $key)
                <button type="button" onclick="escribirNumVirtual('nipInput', '{{ $key }}')" class="min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="escribirNumVirtual('nipInput', '0')" class="col-span-2 min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">0</button>
            <button type="button" onclick="borrarNumVirtual('nipInput')" class="min-h-[44px] rounded-lg bg-red-500/10 border border-red-500/15 text-red-500 hover:bg-red-500 hover:text-white active:scale-90 text-sm font-bold transition-all duration-150"><i class="fas fa-backspace"></i></button>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModal('modalNip')" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 text-xs font-bold transition-all duration-150">Cancelar</button>
            <button type="button" onclick="confirmarNipCapitan()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl bg-gradient-to-b from-blue-500 to-blue-600 text-white text-xs font-bold active:scale-95 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-150">Aceptar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     2. MODAL CAPITÁN (Selección destino)
     ========================================== --}}
<div id="modalCapitan" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    @php $mesasAbiertas = $mesasAbiertas ?? collect(); @endphp
    <div class="modal-sheet w-full sm:max-w-md max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-indigo-500/15 to-indigo-500/5 border border-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-indigo-500 text-xs"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Autorización</p>
                    <h2 class="text-lg sm:text-xl font-semibold text-[var(--text-main)] leading-tight">Selecciona mesero destino</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalCapitan')" class="flex-shrink-0 text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="capitanMeserosContainer" class="grid gap-2 max-h-[45vh] sm:max-h-[300px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 flex justify-end">
            <button type="button" onclick="cerrarModal('modalCapitan')" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 transition-all duration-150">Cancelar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     3. MODAL NOTAS (Teclado Nativo del teléfono)
     ========================================== --}}
<div id="modalNota" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-md max-h-[94vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-4 sm:p-6 pb-[calc(1rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>

        <div class="flex items-center justify-between mb-4 sm:mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/15 to-blue-500/5 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-pen text-blue-500 text-xs"></i>
                </span>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">Instrucción Especial</h2>
                    <p class="text-[11px] text-[var(--text-muted)] font-semibold">
                        Para: <span id="notaModalProducto" class="text-blue-500 font-bold">-</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalNota')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>

        <textarea id="notaTextarea" rows="4"
            readonly
            inputmode="none"
            onclick="abrirTecladoNota()"
            class="w-full rounded-2xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-sm font-medium text-[var(--text-main)] outline-none resize-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 mb-4 cursor-pointer"
            placeholder="Toca aquí para escribir...">
        </textarea>

        <div class="flex items-center gap-2 mb-4 overflow-x-auto hide-scroll pb-1">
            @foreach(['Sin cebolla', 'Salsa aparte', 'Bien cocido', 'Término medio', 'Para llevar', 'Sin picante'] as $notaRapida)
                <button type="button" onclick="agregarTextoRapidoNota('{{ $notaRapida }}')"
                    class="shrink-0 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-500 hover:bg-blue-500 hover:text-white active:scale-90 select-none text-[11px] font-bold shadow-sm transition-all duration-150">
                    {{ $notaRapida }}
                </button>
            @endforeach
        </div>

        <div class="mt-2 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="limpiarNota()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl bg-[var(--text-muted)]/10 border border-[var(--border-color)] text-[var(--text-muted)] hover:bg-[var(--text-muted)] hover:text-white active:scale-95 select-none text-[11px] sm:text-xs font-bold transition-all duration-150">Limpiar</button>
            <button type="button" onclick="cerrarModal('modalNota')" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 text-xs font-bold transition-all duration-150">Cancelar</button>
            <button type="button" onclick="guardarNota()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl bg-gradient-to-b from-[var(--text-main)] to-[var(--text-main)] text-[var(--bg-base)] text-xs font-bold active:scale-95 shadow-md hover:shadow-lg transition-all duration-150 hover:opacity-90">Confirmar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     5. MODAL PERSONAS (Teclado Numérico Virtual)
     ========================================== --}}
<div id="modalPersonas" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-[var(--text-main)]/10 to-[var(--text-main)]/5 border border-[var(--border-color)] flex items-center justify-center">
                    <i class="fas fa-users text-[var(--text-main)] text-xs"></i>
                </span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">Personas en Mesa</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalPersonas')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        
        <input id="personasInput" type="text" data-solo-numeros="true" data-teclado-virtual="true"
               maxlength="3" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3)"
               class="w-full min-h-[64px] rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-2xl sm:text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[var(--text-main)] focus:ring-4 focus:ring-[var(--text-main)]/10 transition-all duration-200">

        {{-- Teclado Numérico --}}
        <div class="grid grid-cols-3 gap-1.5 mt-4">
            @foreach(['1','2','3','4','5','6','7','8','9'] as $key)
                <button type="button" onclick="escribirNumVirtual('personasInput', '{{ $key }}')" class="min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--text-main)]/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="escribirNumVirtual('personasInput', '0')" class="col-span-2 min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--text-main)]/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">0</button>
            <button type="button" onclick="borrarNumVirtual('personasInput')" class="min-h-[44px] rounded-lg bg-red-500/10 border border-red-500/15 text-red-500 hover:bg-red-500 hover:text-white active:scale-90 text-sm font-bold transition-all duration-150"><i class="fas fa-backspace"></i></button>
        </div>

        <div class="mt-6">
            <button type="button" onclick="guardarPersonas()" class="w-full min-h-[48px] rounded-xl bg-gradient-to-b from-[var(--text-main)] to-[var(--text-main)] text-[var(--bg-base)] text-sm font-bold active:scale-95 shadow-md hover:shadow-lg transition-all duration-150">Guardar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     6. MODAL GRAMAJE (Teclado Numérico Virtual propio)
     ========================================== --}}
<div id="modalGramaje" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[94vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-orange-500/15 to-orange-500/5 border border-orange-500/20 flex items-center justify-center">
                    <i class="fas fa-weight-scale text-orange-500 text-xs"></i>
                </span>
                <h2 id="modalGramajeTitulo" class="text-base sm:text-lg font-bold text-[var(--text-main)] truncate">Gramaje</h2>
            </div>
            <button type="button" onclick="cerrarModalGramaje()" class="flex-shrink-0 text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex items-center gap-3 mb-2">
            <input id="gramajeInput" type="text"
                   inputmode="decimal" autocomplete="off"
                   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                   class="flex-1 min-w-0 rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-2xl font-black text-[var(--text-main)] text-center outline-none" placeholder="0">
            <span class="shrink-0 text-[var(--text-muted)] font-bold text-lg">g</span>
        </div>

        <p id="gramajePrecioPreview" class="text-center text-sm font-black text-orange-500 mb-4 h-5"></p>

        <div id="botonesRapidosGramaje" class="grid grid-cols-5 gap-1.5 mb-4">
            @foreach([['100','100g'],['250','250g'],['500','500g'],['700','700g'],['1000','1kg']] as [$valor, $etiqueta])
                <button type="button" onclick="seleccionarGramajeRapido({{ $valor }})" class="min-h-[40px] py-2 rounded-lg bg-orange-500/10 border border-orange-500/20 text-orange-500 hover:bg-orange-500 hover:text-white active:scale-90 select-none text-[11px] font-bold shadow-sm transition-all duration-150">{{ $etiqueta }}</button>
            @endforeach
        </div>

        <div id="tecladoGramaje" class="grid grid-cols-3 gap-2">
            @foreach(['1','2','3','4','5','6','7','8','9','.','0'] as $key)
                <button type="button" onclick="anadirNumeroGramaje('{{ $key }}')" class="min-h-[48px] py-3 rounded-xl bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-orange-500/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="borrarNumeroGramaje()" class="min-h-[48px] py-3 rounded-xl bg-red-500/10 border border-red-500/15 text-red-500 hover:bg-red-500 hover:text-white active:scale-90 select-none text-sm font-bold transition-all duration-150"><i class="fas fa-backspace"></i></button>
        </div>
        
        <div class="mt-6">
            <button type="button" onclick="guardarGramajeDelItem()" class="w-full min-h-[48px] rounded-xl bg-gradient-to-b from-orange-500 to-orange-600 text-white text-sm font-bold active:scale-95 shadow-md hover:shadow-lg transition-all duration-150">Confirmar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     7. MODAL TIPO DE TRASPASO
     ========================================== --}}
<div id="modalTipoTraspaso" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-indigo-500/15 to-indigo-500/5 border border-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-indigo-500 text-xs"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Traspaso</p>
                    <h2 id="tituloTipoTraspaso" class="text-base sm:text-lg font-bold text-[var(--text-main)] leading-tight">¿Qué deseas traspasar?</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalTipoTraspaso')" class="flex-shrink-0 text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex flex-col gap-3">
            <button type="button" onclick="elegirTraspasoProducto()" class="w-full flex items-center gap-3 text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-blue-500/40 hover:shadow-md active:scale-[0.98] transition-all duration-150">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-utensils text-blue-500 text-xs"></i>
                </span>
                <span>
                    <p class="text-sm font-bold text-[var(--text-main)]">Producto Individual</p>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5">Elige uno o varios platillos específicos</p>
                </span>
            </button>
            <button type="button" onclick="elegirTraspasoCompleto()" class="w-full flex items-center gap-3 text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-blue-500/40 hover:shadow-md active:scale-[0.98] transition-all duration-150">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-500 text-xs"></i>
                </span>
                <span>
                    <p class="text-sm font-bold text-[var(--text-main)]">Pedido Completo</p>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5">Envía toda la orden a la mesa destino</p>
                </span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================
     8. MODAL SELECCIÓN DE PRODUCTOS
     ========================================== --}}
<div id="modalSeleccionProductos" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-md max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-indigo-500/15 to-indigo-500/5 border border-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-list-check text-indigo-500 text-xs"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Traspaso</p>
                    <h2 class="text-lg font-bold text-[var(--text-main)] leading-tight">Selecciona productos</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalSeleccionProductos')" class="flex-shrink-0 text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="listaProductosTraspaso" class="grid gap-2 max-h-[45vh] sm:max-h-[320px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModal('modalSeleccionProductos')" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 transition-all duration-150">Cancelar</button>
            <button type="button" onclick="confirmarTraspasoProductos()" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-gradient-to-b from-blue-500 to-blue-600 text-white text-xs font-bold active:scale-95 shadow-md transition-all duration-150">Traspasar</button>
        </div>
    </div>
</div>

{{-- ==========================================
     9. MODAL SELECCIÓN DE COMBO
     ========================================== --}}
<div id="modalSeleccionCombo" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-md max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-blue-500/15 to-blue-500/5 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-tags text-blue-500 text-xs"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-blue-500 font-bold">Combo</p>
                    <h2 id="tituloSeleccionCombo" class="text-lg font-bold text-[var(--text-main)] leading-tight">Selecciona los productos</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalSeleccionCombo')" class="flex-shrink-0 text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-[12px] text-[var(--text-muted)] mb-3">Marca cuáles productos del ticket quieres que cuenten para este combo. Solo puedes marcar los que ya están agregados.</p>
        <div id="listaSeleccionCombo" class="grid gap-2 max-h-[40vh] sm:max-h-[320px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModal('modalSeleccionCombo')" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 transition-all duration-150">Cancelar</button>
            <button type="button" onclick="confirmarSeleccionCombo()" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-gradient-to-b from-blue-500 to-blue-600 text-white text-xs font-bold active:scale-95 shadow-md transition-all duration-150">Aplicar combo</button>
        </div>
    </div>
</div>

{{-- ==========================================
     10. MODAL PROMOCIONES
     ========================================== --}}
<div id="modalPromociones" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-md max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <!-- Contenido de promociones -->
    </div>
</div>

{{-- ==========================================
     11. MODAL NIP CANCELACIÓN DE PRODUCTO
     ========================================== --}}
<div id="modalNipCancelacion" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500/15 to-red-500/5 border border-red-500/20 flex items-center justify-center">
                    <i class="fas fa-ban text-red-500 text-xs"></i>
                </span>
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-red-500 font-bold">Autorización</p>
                    <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">NIP para Cancelar</h2>
                </div>
            </div>
            <button type="button" onclick="cerrarModalCancelacion()" class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-9 h-9 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200"><i class="fas fa-times text-lg"></i></button>
        </div>

        <p class="text-[12px] text-[var(--text-muted)] mb-3">Ingresa el NIP del Administrador para autorizar la cancelación de este producto.</p>

        <input type="password" id="nipCancelacionInput" data-solo-numeros="true" data-teclado-virtual="true"
               maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
               class="w-full min-h-[64px] rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-2xl sm:text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all duration-200 tracking-[0.3em]"
               placeholder="••••">

        <div class="grid grid-cols-3 gap-1.5 mt-4">
            @foreach(['1','2','3','4','5','6','7','8','9'] as $key)
                <button type="button" onclick="escribirNumVirtual('nipCancelacionInput', '{{ $key }}')" class="min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-red-500/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="escribirNumVirtual('nipCancelacionInput', '0')" class="col-span-2 min-h-[44px] rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-red-500/30 hover:bg-[var(--hover-bg)] active:scale-90 text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100">0</button>
            <button type="button" onclick="borrarNumVirtual('nipCancelacionInput')" class="min-h-[44px] rounded-lg bg-red-500/10 border border-red-500/15 text-red-500 hover:bg-red-500 hover:text-white active:scale-90 text-sm font-bold transition-all duration-150"><i class="fas fa-backspace"></i></button>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModalCancelacion()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 text-xs font-bold transition-all duration-150">Cancelar</button>
            <button type="button" id="btnConfirmarNipCancelacion" onclick="confirmarNipCancelacion()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl bg-gradient-to-b from-red-500 to-red-600 text-white text-xs font-bold active:scale-95 shadow-md shadow-red-500/20 hover:shadow-lg hover:shadow-red-500/25 transition-all duration-150">Autorizar Cancelación</button>
        </div>
    </div>
</div>

{{-- ==========================================
     12. MODAL CONFIRMAR CANCELACIÓN DE PRODUCTO
     ========================================== --}}
<div id="modalConfirmarCancelacion" class="modal-overlay hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-sheet w-full sm:max-w-sm max-h-[92vh] overflow-y-auto hide-scroll rounded-t-[28px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-2xl ring-1 ring-black/5">
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>

        <div class="flex flex-col items-center text-center gap-3 mb-2">
            <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500/15 to-red-500/5 border border-red-500/20 flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-red-500 text-xl"></i>
            </span>
            <div>
                <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">¿Cancelar este producto?</h2>
                <p class="text-[12px] text-[var(--text-muted)] mt-1.5 leading-relaxed">Esta acción no se puede deshacer y ya no se cobrará al cliente.</p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModalConfirmarCancelacion()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 text-xs font-bold transition-all duration-150">No, mantener</button>
            <button type="button" onclick="continuarCancelacionConNip()" class="w-full sm:w-auto min-h-[44px] px-6 rounded-xl bg-gradient-to-b from-red-500 to-red-600 text-white text-xs font-bold active:scale-95 shadow-md shadow-red-500/20 hover:shadow-lg hover:shadow-red-500/25 transition-all duration-150">Sí, cancelar</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     TECLADO VIRTUAL — Modal de Nota / Instrucción
     ════════════════════════════════════════════════ --}}
<div id="teclado-nota-overlay"
     class="hidden fixed inset-0 z-[99999]"
     onclick="if(event.target===this) cerrarTecladoNota()">

    <div class="absolute bottom-0 inset-x-0 bg-[var(--bg-base)] border-t border-[var(--border-color)] shadow-2xl rounded-t-3xl">

        <div class="flex items-start gap-3 px-4 pt-4 pb-3 border-b border-[var(--border-color)]">
            <div class="flex-1 bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl px-3 py-2.5 min-h-[48px] max-h-24 overflow-y-auto">
                <span id="tn-display" class="text-sm font-medium text-[var(--text-main)] break-words whitespace-pre-wrap"></span><span class="inline-block w-0.5 h-4 bg-blue-500 animate-pulse rounded-full align-middle ml-0.5"></span>
            </div>
            <button type="button" onclick="cerrarTecladoNota()"
                class="w-10 h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] flex items-center justify-center shrink-0 active:scale-95 mt-0.5">
                <i class="fas fa-check text-sm text-blue-500"></i>
            </button>
        </div>

        <div class="flex gap-2 px-3 pt-2.5 overflow-x-auto hide-scroll pb-1">
            @foreach(['Sin cebolla','Salsa aparte','Bien cocido','Término medio','Para llevar','Sin picante'] as $nota)
                <button type="button" onclick="tnRapida('{{ $nota }}')"
                    class="shrink-0 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-500 text-[11px] font-bold active:scale-90 transition-all">
                    {{ $nota }}
                </button>
            @endforeach
        </div>

        <div class="px-2 py-2 space-y-1.5 select-none">
            @php
                $filasNota = [
                    ['Q','W','E','R','T','Y','U','I','O','P'],
                    ['A','S','D','F','G','H','J','K','L'],
                    ['Z','X','C','V','B','N','M'],
                ];
            @endphp
            @foreach($filasNota as $fila)
                <div class="flex justify-center gap-1">
                    @foreach($fila as $letra)
                        <button type="button" onclick="tnEscribir('{{ $letra }}')"
                            class="flex-1 max-w-[38px] h-10 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 active:bg-blue-100 dark:active:bg-blue-500/20 transition-all duration-75">
                            {{ $letra }}
                        </button>
                    @endforeach
                </div>
            @endforeach
            <div class="flex justify-center gap-1 mt-1">
                <button type="button" onclick="tnEscribir(' ')"
                    class="flex-1 h-10 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 font-bold text-xs shadow-sm active:scale-95 transition-all duration-75">
                    ESPACIO
                </button>
                <button type="button" onclick="tnEscribir('.')"
                    class="w-12 h-10 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-white font-black text-sm shadow-sm active:scale-95 transition-all duration-75">
                    .
                </button>
                <button type="button" onclick="tnBorrar()"
                    class="w-14 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-500 shadow-sm active:scale-95 flex items-center justify-center transition-all duration-75">
                    <i class="fas fa-delete-left text-base"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let tnValor = '';
    const overlay   = document.getElementById('teclado-nota-overlay');
    const display   = document.getElementById('tn-display');
    const textarea  = document.getElementById('notaTextarea');

    window.abrirTecladoNota = function () {
        tnValor = textarea.value || '';
        display.textContent = tnValor;
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.cerrarTecladoNota = function () {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        textarea.value = tnValor;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    };

    window.tnEscribir = function (char) {
        tnValor += char;
        display.textContent = tnValor;
    };

    window.tnBorrar = function () {
        tnValor = tnValor.slice(0, -1);
        display.textContent = tnValor;
    };

    window.tnRapida = function (texto) {
        if (tnValor && !tnValor.endsWith(' ')) tnValor += ' ';
        tnValor += texto;
        display.textContent = tnValor;
    };

    window.agregarTextoRapidoNota = function (texto) {
        const ta = document.getElementById('notaTextarea');
        if (ta) {
            if (ta.value && !ta.value.endsWith(' ')) ta.value += ' ';
            ta.value += texto;
        }
    };
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.esPantallaTactil !== 'function') return;

    const esPantalla = window.esPantallaTactil();

    document.querySelectorAll('[data-teclado-virtual]').forEach(function (input) {
        if (esPantalla) {
            input.setAttribute('inputmode', 'none');
            input.addEventListener('focus', function () {
                window.setInputVirtualActivo && window.setInputVirtualActivo(input.id);
            });
            input.addEventListener('blur', function () {
                setTimeout(function () {
                    const hayFoco = document.querySelector('[data-teclado-virtual]:focus');
                    if (!hayFoco) window.clearInputVirtualActivo && window.clearInputVirtualActivo();
                }, 100);
            });
        } else {
            input.removeAttribute('inputmode');
            input.removeAttribute('readonly');
        }
    });

    const notaTextarea = document.getElementById('notaTextarea');
    if (notaTextarea) {
        if (!esPantalla) {
            notaTextarea.removeAttribute('readonly');
            notaTextarea.removeAttribute('inputmode');
            notaTextarea.removeAttribute('onclick');
        }
    }
});

// Controladores para abrir y cerrar el modal de variantes
window.abrirModalVariante = function(productoId, nombreProducto, variantes) {
    const modal = document.getElementById('modalVariantesProducto');
    if (!modal) return;

    const titulo = modal.querySelector('#modalVariantesTitulo');
    // Buscamos la lista dentro del propio contenedor del modal
    const lista = modal.querySelector('#modalVariantesLista');

    if (titulo) titulo.innerText = nombreProducto;
    if (!lista) return;

    lista.innerHTML = '';

    // Asegurar que variantes sea un array procesable
    let itemsVariantes = variantes;
    if (typeof itemsVariantes === 'string') {
        try { itemsVariantes = JSON.parse(itemsVariantes); } catch (e) { itemsVariantes = []; }
    }

    if (!itemsVariantes || itemsVariantes.length === 0) {
        lista.innerHTML = '<p class="text-xs text-center text-[var(--text-muted)] py-4">No hay opciones disponibles.</p>';
    } else {
        itemsVariantes.forEach(v => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full flex items-center justify-between p-3.5 rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] hover:border-[#b74309] hover:bg-[#b74309]/5 active:scale-[0.98] transition-all shadow-sm text-left group cursor-pointer';
            btn.innerHTML = `
                <span class="text-sm font-bold text-[var(--text-main)] group-hover:text-[#b74309] transition-colors">${v.nombre}</span>
                <span class="text-sm font-black text-[#b74309]">$${parseFloat(v.precio).toFixed(2)}</span>
            `;

            btn.onclick = function() {
                if (typeof window.agregarProductoConVariante === 'function') {
                    window.agregarProductoConVariante(productoId, v);
                }
                cerrarModalVariantes();
            };

            lista.appendChild(btn);
        });
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.cerrarModalVariantes = function() {
    const modal = document.getElementById('modalVariantesProducto');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};
</script>