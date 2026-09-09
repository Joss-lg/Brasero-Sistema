<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto .modal-editar-insumo {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto .modal-editar-insumo-container {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

<div id="modalEditar-{{ $item->id }}" class="modal-editar-insumo hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
    <div id="modalContainer-{{ $item->id }}" class="modal-editar-insumo-container relative rounded-2xl w-full max-w-md sm:mx-4 shadow-2xl scale-95 opacity-0 transition-all duration-200 overflow-hidden flex flex-col max-h-[92dvh]"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        <div class="flex items-center gap-3 sm:gap-4 p-5 sm:p-7 pb-4 sm:pb-5 shrink-0">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0 bg-[#b74309]/10 border border-[#b74309]/20">
                <i class="fas fa-pen text-[#b74309]"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl font-black truncate" style="color: var(--text-color);">Editar Insumo</h2>
                <p class="text-[11px] sm:text-xs uppercase tracking-widest mt-0.5 truncate" style="color: var(--text-muted);">{{ strtoupper($item->nombre) }}</p>
            </div>
            <button onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg hover:text-[#b74309] hover:bg-[#b74309]/10 transition-colors outline-none shrink-0" style="color: var(--text-muted);">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="mx-5 sm:mx-7 shrink-0" style="border-top: 1px solid var(--border-color);"></div>

        <form action="{{ route('admin.inventario.update', $item->id) }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            @method('PUT')

            <div class="p-5 sm:p-7 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">

                {{-- Nombre --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Nombre del Artículo</label>
                    <input type="text" name="nombre" value="{{ $item->nombre }}" required data-teclado="texto" inputmode="none"
                        class="w-full h-12 rounded-xl px-4 text-base sm:text-sm font-semibold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                </div>

                {{-- Dropdown Unidad de Medida --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Unidad de Medida</label>
                    <input type="hidden" name="unidad_medida" id="unidad-edit-{{ $item->id }}-hidden" value="{{ $item->unidad_medida }}">
                    <div class="relative" id="unidad-edit-{{ $item->id }}-wrapper">
                        <button type="button"
                            onclick="toggleInsumoDropdown({{ $item->id }}, 'unidad')"
                            class="w-full h-12 rounded-xl px-4 text-sm font-semibold flex items-center justify-between gap-2 outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <span id="unidad-edit-{{ $item->id }}-label">
                                @if($item->unidad_medida === 'g') Gramos (g)
                                @elseif($item->unidad_medida === 'ml') Mililitros (ml)
                                @else Piezas (pz)
                                @endif
                            </span>
                            <i id="unidad-edit-{{ $item->id }}-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="unidad-edit-{{ $item->id }}-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach(['g' => 'Gramos (g)', 'ml' => 'Mililitros (ml)', 'pz' => 'Piezas (pz)'] as $val => $lbl)
                            <button type="button"
                                onclick="seleccionarInsumoDropdown({{ $item->id }}, 'unidad', '{{ $val }}', '{{ $lbl }}')"
                                class="w-full px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-semibold" style="color: var(--text-color);">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dropdown Categoría --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Categoría</label>
                    <input type="hidden" name="categoria_id" id="categoria-edit-{{ $item->id }}-hidden" value="{{ $item->categoria_id }}">
                    <div class="relative" id="categoria-edit-{{ $item->id }}-wrapper">
                        <button type="button"
                            onclick="toggleInsumoDropdown({{ $item->id }}, 'categoria')"
                            class="w-full h-12 rounded-xl px-4 text-sm font-semibold flex items-center justify-between gap-2 outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <span id="categoria-edit-{{ $item->id }}-label">
                                {{ collect($categorias ?? [])->firstWhere('id', $item->categoria_id)?->nombre ?? 'Sin categoría' }}
                            </span>
                            <i id="categoria-edit-{{ $item->id }}-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="categoria-edit-{{ $item->id }}-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            <button type="button"
                                onclick="seleccionarInsumoDropdown({{ $item->id }}, 'categoria', '', 'Sin categoría')"
                                class="w-full px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-semibold" style="color: var(--text-muted);">
                                Sin categoría
                            </button>
                            @foreach($categorias ?? [] as $cat)
                            <button type="button"
                                onclick="seleccionarInsumoDropdown({{ $item->id }}, 'categoria', '{{ $cat->id }}', '{{ $cat->nombre }}')"
                                class="w-full px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-semibold" style="color: var(--text-color);">
                                {{ $cat->nombre }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Stock Actual y Stock Mínimo --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div class="space-y-2 min-w-0">
                        <label class="text-[10px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Stock Actual</label>
                        <div class="flex items-center h-12 rounded-xl px-4 cursor-not-allowed opacity-60" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                            <span class="text-sm font-black text-[#b74309] truncate">{{ number_format($item->stock_actual, 2) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 min-w-0">
                        <label class="text-[10px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Stock Mínimo</label>
                        <input type="text" name="stock_minimo" value="{{ $item->stock_minimo }}" pattern="[0-9]*\.?[0-9]*" required data-teclado="numerico" inputmode="none"
                            class="w-full h-12 rounded-xl px-4 text-base sm:text-sm font-semibold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between px-5 sm:px-7 py-4 gap-4 shrink-0" style="border-top: 1px solid var(--border-color); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                    class="text-sm font-bold uppercase tracking-widest outline-none px-2 py-3 shrink-0 hover:opacity-70 transition-opacity" style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 h-12 bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-sm uppercase tracking-widest rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-[#b74309]/20 outline-none">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const ITEM_ID = {{ $item->id }};

        function getId(tipo) {
            return tipo + '-edit-' + ITEM_ID;
        }

        function cerrarDropdown(tipo) {
            const dd      = document.getElementById(getId(tipo) + '-dropdown');
            const chevron = document.getElementById(getId(tipo) + '-chevron');
            if (dd)      dd.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }

        window.toggleInsumoDropdown = window.toggleInsumoDropdown || function(itemId, tipo) {};
        window.seleccionarInsumoDropdown = window.seleccionarInsumoDropdown || function(itemId, tipo, valor, etiqueta) {};

        // Sobrescribir con versión que entiende este item
        const prevToggle = window.toggleInsumoDropdown;
        window.toggleInsumoDropdown = function(itemId, tipo) {
            if (itemId !== ITEM_ID) { prevToggle(itemId, tipo); return; }
            const dd      = document.getElementById(getId(tipo) + '-dropdown');
            const chevron = document.getElementById(getId(tipo) + '-chevron');
            if (!dd) return;
            const otroTipo = tipo === 'unidad' ? 'categoria' : 'unidad';
            cerrarDropdown(otroTipo);
            const isHidden = dd.classList.contains('hidden');
            dd.classList.toggle('hidden', !isHidden);
            if (chevron) chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        };

        const prevSeleccionar = window.seleccionarInsumoDropdown;
        window.seleccionarInsumoDropdown = function(itemId, tipo, valor, etiqueta) {
            if (itemId !== ITEM_ID) { prevSeleccionar(itemId, tipo, valor, etiqueta); return; }
            const hidden = document.getElementById(getId(tipo) + '-hidden');
            const label  = document.getElementById(getId(tipo) + '-label');
            if (hidden) hidden.value = valor;
            if (label)  { label.textContent = etiqueta; label.style.color = 'var(--text-color)'; }
            cerrarDropdown(tipo);
        };

        document.addEventListener('click', function(e) {
            ['unidad', 'categoria'].forEach(tipo => {
                const wrapper = document.getElementById(getId(tipo) + '-wrapper');
                if (wrapper && !wrapper.contains(e.target)) cerrarDropdown(tipo);
            });
        });
    })();
</script>