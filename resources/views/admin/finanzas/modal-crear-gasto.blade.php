<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearGasto {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #createGastoContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }

    #categoria-gasto-dropdown, #metodo-gasto-dropdown, #estado-gasto-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes dropdownOpen {
        from { opacity: 0; transform: scaleY(0.9) translateY(-8px); }
        to   { opacity: 1; transform: scaleY(1) translateY(0); }
    }
</style>

<div id="modalCrearGasto" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div id="createGastoContainer" class="w-full max-w-md rounded-2xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[92dvh]"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        <div class="p-5 sm:p-8 pb-4 flex justify-between items-center flex-shrink-0 gap-3"
            style="border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-[#b74309]/10 flex items-center justify-center text-[#b74309] border border-[#b74309]/20 shrink-0">
                    <i class="fas fa-money-bill-wave text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black tracking-tight uppercase truncate" style="color: var(--text-color);">Nuevo Gasto</h3>
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--text-muted);">Registrar egreso</p>
                </div>
            </div>
            <button onclick="closeCreateGastoModal()" class="w-9 h-9 rounded-xl flex items-center justify-center hover:text-[#b74309] hover:bg-[#b74309]/10 transition-all outline-none flex-shrink-0" style="background-color: var(--input-bg); color: var(--text-muted);">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.gastos.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf

            <div class="p-5 sm:p-8 pt-5 sm:pt-6 space-y-4 overflow-y-auto flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">

                {{-- Concepto --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-pen opacity-40"></i> Concepto
                    </label>
                    <input type="text" name="concepto" required data-teclado="texto"
                        class="w-full h-11 rounded-xl px-5 text-base sm:text-sm font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Ej: Compra de tomates">
                </div>

                {{-- Dropdown Categoría --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-folder opacity-40"></i> Categoría
                    </label>
                    <input type="hidden" name="categoria" id="categoria-gasto-hidden">
                    <div class="relative" id="categoria-gasto-wrapper">
                        <button type="button" onclick="toggleGastoDropdown('categoria')"
                            class="w-full h-11 rounded-xl px-5 text-sm font-bold flex items-center justify-between gap-2 focus:border-[#b74309] outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                            <span id="categoria-gasto-label">Selecciona una categoría</span>
                            <i id="categoria-gasto-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="categoria-gasto-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach(['Compra Insumos' => ['label'=>'Compra de Insumos','icon'=>'fa-box','color'=>'text-emerald-500'],
                                      'Servicios'      => ['label'=>'Servicios',          'icon'=>'fa-plug','color'=>'text-[#b74309]'],
                                      'Renta'          => ['label'=>'Renta',              'icon'=>'fa-home','color'=>'text-purple-500'],
                                      'Mantenimiento'  => ['label'=>'Mantenimiento',      'icon'=>'fa-wrench','color'=>'text-amber-500'],
                                      'Otro'           => ['label'=>'Otro',               'icon'=>'fa-ellipsis-h','color'=>'text-gray-400']] as $val => $op)
                            <button type="button" onclick="seleccionarGastoDropdown('categoria', '{{ $val }}', '{{ $op['label'] }}')"
                                class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5">
                                <i class="fas {{ $op['icon'] }} {{ $op['color'] }} text-sm w-5 text-center"></i>
                                <span class="text-sm font-semibold" style="color: var(--text-color);">{{ $op['label'] }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Monto --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-dollar-sign opacity-40"></i> Monto
                    </label>
                    <input type="text" name="monto" required data-teclado="numerico" data-teclado-decimales="true"
                        class="w-full h-11 rounded-xl px-5 text-base sm:text-sm font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="0.00">
                </div>

                {{-- Dropdown Método de Pago --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <input type="hidden" name="metodo_pago" id="metodo-gasto-hidden">
                    <div class="relative" id="metodo-gasto-wrapper">
                        <button type="button" onclick="toggleGastoDropdown('metodo')"
                            class="w-full h-11 rounded-xl px-5 text-sm font-bold flex items-center justify-between gap-2 focus:border-[#b74309] outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                            <span id="metodo-gasto-label">Selecciona método</span>
                            <i id="metodo-gasto-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="metodo-gasto-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach(['Efectivo'=>['icon'=>'fa-money-bill','color'=>'text-emerald-500'],
                                      'Tarjeta'=>['icon'=>'fa-credit-card','color'=>'text-[#b74309]'],
                                      'Transferencia'=>['icon'=>'fa-exchange-alt','color'=>'text-purple-500']] as $val => $op)
                            <button type="button" onclick="seleccionarGastoDropdown('metodo', '{{ $val }}', '{{ $val }}')"
                                class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5">
                                <i class="fas {{ $op['icon'] }} {{ $op['color'] }} text-sm w-5 text-center"></i>
                                <span class="text-sm font-semibold" style="color: var(--text-color);">{{ $val }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dropdown Estado del Pago --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-check-circle opacity-40"></i> Estado del Pago
                    </label>
                    <input type="hidden" name="estado" id="estado-gasto-hidden" value="pagado">
                    <div class="relative" id="estado-gasto-wrapper">
                        <button type="button" onclick="toggleGastoDropdown('estado')"
                            class="w-full h-11 rounded-xl px-5 text-sm font-bold flex items-center justify-between gap-2 focus:border-[#b74309] outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <span id="estado-gasto-label">Pagado (afecta caja hoy)</span>
                            <i id="estado-gasto-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="estado-gasto-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            <button type="button" onclick="seleccionarGastoDropdown('estado', 'pagado', 'Pagado (afecta caja hoy)')"
                                class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5">
                                <i class="fas fa-check-circle text-emerald-500 text-sm w-5 text-center"></i>
                                <span class="text-sm font-semibold" style="color: var(--text-color);">Pagado (afecta caja hoy)</span>
                            </button>
                            <button type="button" onclick="seleccionarGastoDropdown('estado', 'pendiente', 'Pendiente de pago')"
                                class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-[#b74309]/5">
                                <i class="fas fa-clock text-amber-500 text-sm w-5 text-center"></i>
                                <span class="text-sm font-semibold" style="color: var(--text-color);">Pendiente de pago</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 flex-shrink-0"
                style="border-top: 1px solid var(--border-color); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateGastoModal()" class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none hover:opacity-70" style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit" class="flex-[1.5] h-12 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#b74309]/20 transition-all active:scale-95 outline-none">
                    Guardar Gasto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') TecladoVirtual.attachAll();
    });

    const _gastoDropdowns = { categoria: false, metodo: false, estado: false };

    function toggleGastoDropdown(tipo) {
        Object.keys(_gastoDropdowns).forEach(k => {
            if (k !== tipo) cerrarGastoDropdown(k);
        });
        const dd = document.getElementById(tipo + '-gasto-dropdown');
        const chevron = document.getElementById(tipo + '-gasto-chevron');
        const isHidden = dd.classList.contains('hidden');
        if (isHidden) {
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarGastoDropdown(tipo);
        }
    }

    function cerrarGastoDropdown(tipo) {
        document.getElementById(tipo + '-gasto-dropdown')?.classList.add('hidden');
        const chevron = document.getElementById(tipo + '-gasto-chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    function seleccionarGastoDropdown(tipo, valor, etiqueta) {
        document.getElementById(tipo + '-gasto-hidden').value = valor;
        const label = document.getElementById(tipo + '-gasto-label');
        label.textContent = etiqueta;
        label.style.color = 'var(--text-color)';
        cerrarGastoDropdown(tipo);
    }

    document.addEventListener('click', function(e) {
        ['categoria', 'metodo', 'estado'].forEach(tipo => {
            const wrapper = document.getElementById(tipo + '-gasto-wrapper');
            if (wrapper && !wrapper.contains(e.target)) cerrarGastoDropdown(tipo);
        });
    });

    function closeCreateGastoModal() {
        const modal = document.getElementById('modalCrearGasto');
        const container = document.getElementById('createGastoContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>