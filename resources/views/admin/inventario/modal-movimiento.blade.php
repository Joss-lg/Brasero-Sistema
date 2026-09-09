<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalMovimiento {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #movimientoContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }

    #tipo-mov-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>

<div id="modalMovimiento" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div class="w-full max-w-md rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[95dvh] sm:max-h-[92dvh]" id="movimientoContainer"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        
        <div class="p-5 sm:p-8 pb-4 sm:pb-5 flex justify-between items-center shrink-0 gap-3" style="border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0">
                    <i class="fas fa-exchange-alt text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black tracking-tight uppercase m-0 leading-tight truncate max-w-[150px] sm:max-w-[200px]" id="movimientoNombreInsumo" style="color: var(--text-color);">Cargando...</h3>
                    <p class="text-[8px] sm:text-[9px] font-bold uppercase tracking-[0.2em] mt-1" style="color: var(--text-muted);">Registrar Entrada o Salida</p>
                </div>
            </div>
            <button type="button" onclick="closeModalMovimiento()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center hover:text-[#b74309] hover:bg-[#b74309]/10 transition-all outline-none shrink-0" style="background-color: var(--input-bg); color: var(--text-muted);">
                <i class="fas fa-times text-xs sm:text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.inventario.movimiento') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            <input type="hidden" name="insumo_id" id="movimientoInsumoId">

            <div class="p-5 sm:p-8 pt-4 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain hide-scroll" style="-webkit-overflow-scrolling: touch;">

                {{-- Dropdown Tipo de Movimiento --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Tipo de Movimiento</label>
                    <input type="hidden" name="tipo" id="tipo-mov-hidden" value="entrada">
                    <div class="relative" id="tipo-mov-wrapper">
                        <button type="button" onclick="toggleMovDropdown()"
                            class="w-full h-11 sm:h-12 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-sm font-bold flex items-center justify-between gap-2 outline-none transition-all hover:border-[#b74309]/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <span id="tipo-mov-label">🟢 ENTRADA (Suma al stock)</span>
                            <i id="tipo-mov-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="tipo-mov-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach([
                                'entrada' => '🟢 ENTRADA (Suma al stock)',
                                'salida'  => '🔴 SALIDA (Resta al stock)',
                                'ajuste'  => '🟠 MERMA / DESPERDICIO',
                            ] as $val => $label)
                            <button type="button" onclick="seleccionarMovDropdown('{{ $val }}', '{{ $label }}')"
                                class="w-full px-4 py-3 transition-colors text-left hover:bg-[#b74309]/5 text-sm font-bold" style="color: var(--text-color);">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Cantidad --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Cantidad a mover</label>
                    <input type="text" name="cantidad" pattern="[0-9]*\.?[0-9]*" required data-teclado="numerico" inputmode="none"
                        class="w-full h-11 sm:h-12 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-base sm:text-xs font-black outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Ej: 50">
                </div>

                {{-- Motivo --}}
                <div class="space-y-2">
                    <label class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">Motivo o Justificación</label>
                    <input type="text" name="motivo" required data-teclado="texto" inputmode="none"
                        class="w-full h-11 sm:h-12 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-base sm:text-xs font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Ej: Factura #1234 / Se rompió">
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 shrink-0" style="border-top: 1px solid var(--border-color); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeModalMovimiento()" 
                    class="flex-1 h-11 sm:h-12 rounded-xl sm:rounded-2xl text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] transition-all outline-none hover:opacity-70" style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-11 sm:h-12 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleMovDropdown() {
        const dd = document.getElementById('tipo-mov-dropdown');
        const chevron = document.getElementById('tipo-mov-chevron');
        const isHidden = dd.classList.contains('hidden');
        if (isHidden) {
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarMovDropdown();
        }
    }

    function cerrarMovDropdown() {
        document.getElementById('tipo-mov-dropdown').classList.add('hidden');
        document.getElementById('tipo-mov-chevron').style.transform = 'rotate(0deg)';
    }

    function seleccionarMovDropdown(valor, etiqueta) {
        document.getElementById('tipo-mov-hidden').value = valor;
        document.getElementById('tipo-mov-label').textContent = etiqueta;
        cerrarMovDropdown();
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('tipo-mov-wrapper');
        if (wrapper && !wrapper.contains(e.target)) cerrarMovDropdown();
    });

    function openModalMovimiento(id, nombre) {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        document.getElementById('movimientoInsumoId').value = id;
        document.getElementById('movimientoNombreInsumo').innerText = nombre;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModalMovimiento() {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.querySelector('form').reset();
            // Reset dropdown label
            document.getElementById('tipo-mov-hidden').value = 'entrada';
            document.getElementById('tipo-mov-label').textContent = '🟢 ENTRADA (Suma al stock)';
        }, 300);
    }
</script>