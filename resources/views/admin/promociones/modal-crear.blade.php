<div id="modalCrear" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto overflow-x-hidden p-4 transition-all duration-300">
    {{-- Backdrop clickeable --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeModal('modalCrear')"></div>

    <div class="modal-container relative w-full max-w-2xl rounded-[2.5rem] p-8 lg:p-10 shadow-2xl my-8 unique-scrollbar max-h-[90vh] overflow-y-auto overflow-x-hidden scale-95 opacity-0 transition-all duration-200" 
         id="createContainer"
         style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Resplandor decorativo de fondo --}}
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-[#b74309]/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Cabecera del Modal --}}
        <div class="flex items-center gap-4 mb-8 relative z-10">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl font-black shadow-inner border"
                 style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.2); color: #b74309;">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight" style="color: var(--text-color);">Crear Nueva Promoción</h2>
                <p class="text-xs font-medium tracking-wide mt-0.5" style="color: var(--text-muted);">Configura una oferta especial para el menú.</p>
            </div>
            <button type="button" onclick="closeModal('modalCrear')" class="ml-auto w-9 h-9 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/5 active:scale-95 transition-colors outline-none shrink-0 cursor-pointer" style="color: var(--text-muted);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- La promoción siempre se crea activa --}}
        <input type="checkbox" name="esta_activa" value="1" checked class="hidden" form="formCrearPromocion">

        <form id="formCrearPromocion" onsubmit="guardarPromocion(event)" class="relative z-10">
            @csrf
            <div class="space-y-6">

                {{-- Nombre de Promoción --}}
                <div class="group">
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Nombre de la Promoción</label>
                    <input type="text" name="nombre" required data-teclado="texto" 
                           placeholder="Ej: Promo Parrillada Familiar..."
                           class="w-full rounded-xl py-3.5 px-4 text-sm font-bold outline-none transition-all shadow-inner focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                           style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                </div>

                {{-- Fila: Tipo y Valor --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Dropdown Personalizado: Tipo de Promoción --}}
                    <div class="group" x-data="{
                        open: false,
                        selected: 'porcentaje',
                        selectedLabel: 'Porcentaje (%)',
                        options: [
                            { value: 'porcentaje', label: 'Porcentaje (%)' },
                            { value: 'descuento_fijo', label: 'Descuento fijo ($)' },
                            { value: 'dos_por_uno', label: '2 x 1' },
                            { value: 'combo', label: 'Combo' }
                        ],
                        select(opt) {
                            this.selected = opt.value;
                            this.selectedLabel = opt.label;
                            this.open = false;
                            const input = document.getElementById('crear_tipo_promocion');
                            if (input) input.value = opt.value;
                        }
                    }">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Tipo de Promoción</label>
                        
                        <input type="hidden" name="tipo_promocion" id="crear_tipo_promocion" :value="selected" value="porcentaje" required>

                        <div class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold outline-none transition-all shadow-inner border flex items-center justify-between focus:border-[#b74309] cursor-pointer"
                                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                                <span x-text="selectedLabel">Porcentaje (%)</span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }" style="color: var(--text-muted);"></i>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute z-50 w-full mt-2 rounded-xl shadow-2xl py-2 border overflow-hidden"
                                style="background-color: var(--card-color); border-color: var(--border-color); display: none;">
                                <template x-for="opt in options" :key="opt.value">
                                    <div @click="select(opt)"
                                        class="px-4 py-2.5 text-sm font-bold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                                        :class="{ 'bg-[#b74309]/15 text-[#b74309]': selected === opt.value }"
                                        style="color: var(--text-color);">
                                        <span x-text="opt.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Valor Descuento --}}
                    <div class="group">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Valor Descuento / Cantidad</label>
                        <input type="text" name="valor_descuento" required data-teclado="numerico" data-teclado-decimales="true" 
                               class="w-full rounded-xl py-3.5 px-4 text-sm font-bold outline-none transition-all shadow-inner focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10" 
                               placeholder="Ej: 15.00"
                               style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                    </div>
                </div>

                {{-- Fila: Vigencia de Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Fecha Inicio Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" id="crear_fecha_inicio" required value="{{ date('Y-m-d') }}" 
                                   class="date-input-icon w-full rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold outline-none transition-all shadow-inner cursor-pointer focus:border-[#b74309]"
                                   style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <i onclick="abrirCalendario('crear_fecha_inicio')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 text-[#b74309] cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Fecha Fin Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_fin" id="crear_fecha_fin" required value="{{ date('Y-m-d', strtotime('+1 month')) }}" 
                                   class="date-input-icon w-full rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold outline-none transition-all shadow-inner cursor-pointer focus:border-[#b74309]"
                                   style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <i onclick="abrirCalendario('crear_fecha_fin')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 text-[#b74309] cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                </div>

                {{-- Días de la Semana --}}
                <div>
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-3" style="color: var(--text-muted);">Días de Aplicación Semanal</label>
                    <div class="grid grid-cols-4 sm:flex gap-2 flex-wrap">
                        @php
                            $mapeoDias = [['L' => 1], ['M' => 2], ['M' => 3], ['J' => 4], ['V' => 5], ['S' => 6], ['D' => 7]];
                        @endphp
                        @foreach($mapeoDias as $diaData)
                            @php $letra = key($diaData); $num = $diaData[$letra]; @endphp
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer hover:border-[#b74309]/50 transition-colors select-none group/day relative"
                                   style="background-color: var(--input-bg); border-color: var(--border-color);">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" checked class="peer sr-only">
                                <div class="w-5 h-5 rounded-lg border-2 peer-checked:bg-[#b74309] peer-checked:border-[#b74309] flex items-center justify-center transition-all mb-1"
                                     style="border-color: var(--border-color);">
                                    <i class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                                <span class="font-black text-[11px] uppercase tracking-wider group-hover/day:text-[#b74309] transition-colors" style="color: var(--text-color);">{{ $letra }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Selección de Productos --}}
                <div>
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Productos Vinculados</label>
                    <div class="max-h-52 overflow-y-auto border rounded-2xl p-2 shadow-inner unique-scrollbar divide-y transition-colors"
                         style="background-color: var(--input-bg); border-color: var(--border-color); divide-color: var(--border-color);">
                        @foreach($productos as $producto)
                            <label class="flex items-center gap-4 p-3 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition-colors select-none group/prod">
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" 
                                       class="w-5 h-5 rounded-lg cursor-pointer accent-[#b74309]"
                                       style="border-color: var(--border-color);">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold group-hover/prod:text-[#b74309] transition-colors leading-snug flex items-center gap-2" style="color: var(--text-color);">
                                        {{ $producto->nombre }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t" style="border-top-color: var(--border-color);">
                    <button type="button" onclick="closeModal('modalCrear')" 
                            class="w-full sm:flex-1 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-colors outline-none cursor-pointer border"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-guardar-promocion" 
                            class="w-full sm:flex-1 bg-[#b74309] hover:bg-[#8f3207] text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(183,67,9,0.4)] active:scale-95 transition-all outline-none cursor-pointer">
                        Guardar Promoción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrear { align-items: flex-start !important; padding-top: 10px !important; }
        body.teclado-virtual-abierto #createContainer { max-height: 45dvh !important; }
    }
    .unique-scrollbar::-webkit-scrollbar { width: 6px; }
    .unique-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    @media (prefers-color-scheme: dark) { .unique-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); } }
    .date-input-icon::-webkit-calendar-picker-indicator { opacity: 0; position: absolute; right: 0; width: 2.5rem; height: 100%; cursor: pointer; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });

    function abrirCalendario(inputId) {
        const input = document.getElementById(inputId);
        if (input && typeof input.showPicker === 'function') { 
            input.showPicker(); 
        } else if (input) { 
            input.focus(); 
        }
    }
</script>