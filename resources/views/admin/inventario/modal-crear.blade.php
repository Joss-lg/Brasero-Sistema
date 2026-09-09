<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrear {
            align-items: flex-start !important;
            padding-top: 10px !important;
        }
        body.teclado-virtual-abierto #createContainer {
            max-height: 45dvh !important;
            transform: translateY(0) scale(0.95) !important;
        }
    }

    #categoria-inv-dropdown, #unidad-inv-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes dropdownOpen {
        from { opacity: 0; transform: scaleY(0.9) translateY(-8px); }
        to   { opacity: 1; transform: scaleY(1) translateY(0); }
    }
</style>

<div id="modalCrear" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div id="createContainer" class="w-full max-w-md rounded-[1.5rem] sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[95dvh] sm:max-h-[92dvh]"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        
        <div class="p-5 sm:p-8 pb-4 shrink-0 flex justify-between items-center" style="border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-[#b74309]/10 flex items-center justify-center text-[#b74309] border border-[#b74309]/20 shrink-0">
                    <i class="fas fa-layer-group text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black tracking-tight uppercase m-0 leading-tight truncate" style="color: var(--text-color);">Nuevo Artículo</h3>
                    <p class="text-[8px] sm:text-[9px] font-bold uppercase tracking-[0.2em] mt-1" style="color: var(--text-muted);">Añadir al Inventario</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center hover:text-[#b74309] hover:bg-[#b74309]/10 transition-all outline-none shrink-0" style="background-color: var(--input-bg); color: var(--text-muted);">
                <i class="fas fa-times text-xs sm:text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.inventario.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf

            <div class="p-5 sm:p-8 pt-4 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain hide-scroll" style="-webkit-overflow-scrolling: touch;">

                {{-- Nombre --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Nombre del Artículo</label>
                    <input type="text" name="nombre" required data-teclado="texto"
                        class="w-full h-11 rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Ej: Harina">
                </div>
                
                {{-- Dropdown Categoría --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Categoría</label>
                    <input type="hidden" name="categoria_id" id="categoria-inv-hidden">
                    <div class="relative" id="categoria-inv-wrapper">
                        <button type="button" onclick="toggleInvDropdown('categoria')"
                            class="w-full h-11 rounded-xl px-4 sm:px-5 text-sm font-bold flex items-center justify-between gap-2 outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                            <span id="categoria-inv-label">Selecciona una categoría</span>
                            <i id="categoria-inv-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="categoria-inv-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach($categorias as $categoria)
                            <button type="button" onclick="seleccionarInvDropdown('categoria', '{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                class="w-full px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-semibold" style="color: var(--text-color);">
                                {{ $categoria->nombre }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Dropdown Unidad de Medida --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Unidad de Medida</label>
                    <input type="hidden" name="unidad_medida" id="unidad-inv-hidden" value="g">
                    <div class="relative" id="unidad-inv-wrapper">
                        <button type="button" onclick="toggleInvDropdown('unidad')"
                            class="w-full h-11 rounded-xl px-4 sm:px-5 text-sm font-bold flex items-center justify-between gap-2 outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <span id="unidad-inv-label">Gramos (g)</span>
                            <i id="unidad-inv-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="unidad-inv-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach(['g' => 'Gramos (g)', 'ml' => 'Mililitros (ml)', 'pz' => 'Piezas (pz)'] as $val => $label)
                            <button type="button" onclick="seleccionarInvDropdown('unidad', '{{ $val }}', '{{ $label }}')"
                                class="w-full px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-semibold" style="color: var(--text-color);">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Precio Compra</label>
                        <input type="text" name="precio_compra" data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 rounded-xl px-4 sm:px-5 text-base sm:text-xs font-black outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                            placeholder="0.00">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Stock Mínimo</label>
                        <input type="text" name="stock_minimo" required data-teclado="numerico"
                            class="w-full h-11 rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                            placeholder="0">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 shrink-0" style="border-top: 1px solid var(--border-color); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 h-11 sm:h-12 rounded-xl text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] transition-all outline-none hover:opacity-70"
                    style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-[1.5] h-11 sm:h-12 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#b74309]/20 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') TecladoVirtual.attachAll();
    });

    function toggleInvDropdown(tipo) {
        ['categoria', 'unidad'].forEach(k => { if (k !== tipo) cerrarInvDropdown(k); });
        const dd = document.getElementById(tipo + '-inv-dropdown');
        const chevron = document.getElementById(tipo + '-inv-chevron');
        const isHidden = dd.classList.contains('hidden');
        if (isHidden) {
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarInvDropdown(tipo);
        }
    }

    function cerrarInvDropdown(tipo) {
        document.getElementById(tipo + '-inv-dropdown')?.classList.add('hidden');
        const chevron = document.getElementById(tipo + '-inv-chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    function seleccionarInvDropdown(tipo, valor, etiqueta) {
        document.getElementById(tipo + '-inv-hidden').value = valor;
        const label = document.getElementById(tipo + '-inv-label');
        label.textContent = etiqueta;
        label.style.color = 'var(--text-color)';
        cerrarInvDropdown(tipo);
    }

    document.addEventListener('click', function(e) {
        ['categoria', 'unidad'].forEach(tipo => {
            const wrapper = document.getElementById(tipo + '-inv-wrapper');
            if (wrapper && !wrapper.contains(e.target)) cerrarInvDropdown(tipo);
        });
    });
</script>