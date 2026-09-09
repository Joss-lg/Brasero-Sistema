<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearNomina {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #createNominaContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }

    #empleado-nomina-dropdown, #metodo-nomina-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes dropdownOpen {
        from { opacity: 0; transform: scaleY(0.9) translateY(-8px); }
        to   { opacity: 1; transform: scaleY(1) translateY(0); }
    }
</style>

<div id="modalCrearNomina" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    <div id="createNominaContainer" class="w-full max-w-lg rounded-2xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[92dvh]"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        <div class="p-5 sm:p-8 pb-4 sm:pb-5 flex justify-between items-center flex-shrink-0 gap-3"
            style="border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-500 border border-purple-500/20 shadow-sm shrink-0">
                    <i class="fas fa-users text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black tracking-tighter uppercase truncate" style="color: var(--text-color);">Pago de Nómina</h3>
                    <p class="text-[8px] sm:text-[9px] font-bold uppercase tracking-[0.2em]" style="color: var(--text-muted);">Registrar pago a empleado</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateNominaModal()" class="w-9 h-9 rounded-xl flex items-center justify-center hover:text-purple-500 hover:bg-purple-500/10 transition-all outline-none flex-shrink-0" style="background-color: var(--input-bg); color: var(--text-muted);">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.pagos-nomina.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            <div class="p-5 sm:p-8 pt-5 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Dropdown Empleado --}}
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                            <i class="fas fa-user opacity-40"></i> Empleado
                        </label>
                        <input type="hidden" name="user_id" id="empleado-nomina-hidden">
                        <div class="relative" id="empleado-nomina-wrapper">
                            <button type="button" onclick="toggleNominaDropdown('empleado')"
                                class="w-full h-11 rounded-xl px-4 text-sm font-bold flex items-center justify-between gap-2 outline-none transition-all hover:border-purple-500/50"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                <span id="empleado-nomina-label" class="truncate">Selecciona un empleado</span>
                                <i id="empleado-nomina-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                            </button>
                            <div id="empleado-nomina-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"
                                style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                                @foreach($empleados ?? [] as $empleado)
                                <button type="button"
                                    onclick="seleccionarEmpleadoNomina('{{ $empleado->id }}', '{{ $empleado->nombre }}', {{ $empleado->sueldo_base ?? 0 }})"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-purple-500/5">
                                    <div class="w-7 h-7 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-500 text-xs font-black shrink-0">
                                        {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-semibold truncate" style="color: var(--text-color);">{{ $empleado->nombre }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Período --}}
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                            <i class="fas fa-calendar opacity-40"></i> Período
                        </label>
                        <input type="text" name="periodo" required data-teclado="texto"
                            class="w-full h-11 rounded-xl px-5 text-sm font-bold outline-none transition-all focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                            placeholder="Ej: 1-15 Mayo 2026">
                    </div>
                </div>

                {{-- Sueldo / Bonos / Descuentos --}}
                <div class="grid grid-cols-3 gap-2 sm:gap-3 p-2.5 sm:p-3 rounded-xl sm:rounded-2xl"
                    style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black uppercase tracking-wider text-center block" style="color: var(--text-muted);">Sueldo Base</label>
                        <input type="text" name="sueldo_base" required id="sueldoBase" data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 rounded-lg text-sm sm:text-xs font-bold text-center outline-none focus:ring-2 focus:ring-purple-500/20"
                            style="background-color: var(--card-color); color: var(--text-color); border: 1px solid var(--border-color);"
                            placeholder="0.00">
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black text-emerald-500 uppercase tracking-wider text-center block">+ Bonos</label>
                        <input type="text" name="bonos" value="0" data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 rounded-lg text-sm sm:text-xs font-bold text-emerald-500 text-center outline-none focus:ring-2 focus:ring-emerald-500/20"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);"
                            placeholder="0.00" oninput="calcularMonto()">
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black text-rose-500 uppercase tracking-wider text-center block">- Descuentos</label>
                        <input type="text" name="deducciones" value="0" data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 rounded-lg text-sm sm:text-xs font-bold text-rose-500 text-center outline-none focus:ring-2 focus:ring-rose-500/20"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);"
                            placeholder="0.00" oninput="calcularMonto()">
                    </div>
                </div>

                <input type="hidden" name="estado" value="pagado">

                {{-- Dropdown Método de Pago --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ml-1" style="color: var(--text-muted);">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <input type="hidden" name="metodo_pago" id="metodo-nomina-hidden">
                    <div class="relative" id="metodo-nomina-wrapper">
                        <button type="button" onclick="toggleNominaDropdown('metodo')"
                            class="w-full h-11 rounded-xl px-5 text-sm font-bold flex items-center justify-between gap-2 outline-none transition-all hover:border-purple-500/50"
                            style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                            <span id="metodo-nomina-label">Selecciona método</span>
                            <i id="metodo-nomina-chevron" class="fas fa-chevron-down text-[10px] transition-transform duration-200 shrink-0" style="color: var(--text-muted);"></i>
                        </button>
                        <div id="metodo-nomina-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 rounded-xl shadow-2xl z-50 overflow-hidden"
                            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                            @foreach(['Efectivo'=>['icon'=>'fa-money-bill','color'=>'text-emerald-500'],
                                      'Tarjeta'=>['icon'=>'fa-credit-card','color'=>'text-[#b74309]'],
                                      'Transferencia'=>['icon'=>'fa-exchange-alt','color'=>'text-purple-500']] as $val => $op)
                            <button type="button" onclick="seleccionarNominaDropdown('metodo', '{{ $val }}', '{{ $val }}')"
                                class="w-full flex items-center gap-3 px-4 py-2.5 transition-colors text-left hover:bg-purple-500/5">
                                <i class="fas {{ $op['icon'] }} {{ $op['color'] }} text-sm w-5 text-center"></i>
                                <span class="text-sm font-semibold" style="color: var(--text-color);">{{ $val }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Monto Neto --}}
                <div class="rounded-xl sm:rounded-2xl p-3.5 sm:p-4 flex items-center justify-between gap-3"
                    style="background-color: var(--input-bg); border: 1px solid rgba(168,85,247,0.2);">
                    <label class="text-[8px] sm:text-[9px] font-black text-purple-500 uppercase tracking-[0.2em]">Monto Neto a Pagar</label>
                    <div class="text-xl sm:text-3xl font-black text-purple-500 tracking-tight">$ <span id="montoNeto">0.00</span></div>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 flex-shrink-0"
                style="border-top: 1px solid var(--border-color); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateNominaModal()" class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none hover:opacity-70" style="color: var(--text-muted);">Cancelar</button>
                <button type="submit" class="flex-[1.4] h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-purple-600/20 transition-all active:scale-95 outline-none">Guardar Nómina</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') TecladoVirtual.attachAll();
    });

    function toggleNominaDropdown(tipo) {
        ['empleado', 'metodo'].forEach(k => {
            if (k !== tipo) cerrarNominaDropdown(k);
        });
        const dd = document.getElementById(tipo + '-nomina-dropdown');
        const chevron = document.getElementById(tipo + '-nomina-chevron');
        const isHidden = dd.classList.contains('hidden');
        if (isHidden) {
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarNominaDropdown(tipo);
        }
    }

    function cerrarNominaDropdown(tipo) {
        document.getElementById(tipo + '-nomina-dropdown')?.classList.add('hidden');
        const chevron = document.getElementById(tipo + '-nomina-chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    function seleccionarEmpleadoNomina(id, nombre, sueldo) {
        document.getElementById('empleado-nomina-hidden').value = id;
        const label = document.getElementById('empleado-nomina-label');
        label.textContent = nombre;
        label.style.color = 'var(--text-color)';
        cerrarNominaDropdown('empleado');
        // Autocompletar sueldo base
        const inputSueldo = document.getElementById('sueldoBase');
        if (inputSueldo) { inputSueldo.value = parseFloat(sueldo).toFixed(2); }
        calcularMonto();
    }

    function seleccionarNominaDropdown(tipo, valor, etiqueta) {
        document.getElementById(tipo + '-nomina-hidden').value = valor;
        const label = document.getElementById(tipo + '-nomina-label');
        label.textContent = etiqueta;
        label.style.color = 'var(--text-color)';
        cerrarNominaDropdown(tipo);
    }

    document.addEventListener('click', function(e) {
        ['empleado', 'metodo'].forEach(tipo => {
            const wrapper = document.getElementById(tipo + '-nomina-wrapper');
            if (wrapper && !wrapper.contains(e.target)) cerrarNominaDropdown(tipo);
        });
    });

    function closeCreateNominaModal() {
        const modal = document.getElementById('modalCrearNomina');
        const container = document.getElementById('createNominaContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>