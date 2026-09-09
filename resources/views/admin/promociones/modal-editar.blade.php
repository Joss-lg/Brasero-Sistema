<style>
    /* Solo aplicamos el truco de subir el modal en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalEditar {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }

        body.teclado-virtual-abierto #modalEditarPromocionContent {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

<div id="modalEditar" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto overflow-x-hidden p-3 sm:p-4 transition-all duration-300">
    {{-- Backdrop clickeable --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeModal('modalEditar')"></div>

    <div id="modalEditarPromocionContent" class="modal-container relative w-full max-w-2xl rounded-[1.5rem] sm:rounded-[2.5rem] p-5 sm:p-8 lg:p-10 shadow-2xl my-4 sm:my-8 unique-scrollbar max-h-[92vh] sm:max-h-[90vh] overflow-y-auto overflow-x-hidden scale-95 opacity-0 transition-all duration-200"
         style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Resplandor decorativo de fondo --}}
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-[#b74309]/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Cabecera del Modal --}}
        <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8 relative z-10">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center text-lg sm:text-xl font-black shadow-inner border shrink-0"
                 style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.2); color: #b74309;">
                <i class="fas fa-pen"></i>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight" style="color: var(--text-color);">Modificar Promoción</h2>
                <p class="text-[11px] sm:text-xs font-medium tracking-wide mt-0.5" style="color: var(--text-muted);">Modifica los parámetros y restricciones de la oferta.</p>
            </div>
            <button type="button" onclick="closeModal('modalEditar')" class="ml-auto w-9 h-9 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/5 active:scale-95 transition-colors outline-none shrink-0 cursor-pointer" style="color: var(--text-muted);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- El campo esta_activa sigue enviándose, oculto, para no perder el dato al guardar --}}
        <input type="checkbox" name="esta_activa" value="1" id="edit_esta_activa" class="hidden" form="formEditarPromocion">

        {{-- Formulario de Edición --}}
        <form action="" method="POST" id="formEditarPromocion" class="relative z-10">
            @csrf
            @method('PUT')

            <div class="space-y-5 sm:space-y-6">

                {{-- Nombre de la Promo --}}
                <div class="group">
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Nombre de la Promoción</label>
                    <input type="text" name="nombre" id="edit_nombre" required data-teclado="texto" data-teclado-titulo="Nombre de la Promoción" 
                           class="w-full rounded-xl py-3.5 px-4 text-base font-bold outline-none transition-all shadow-inner focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10" 
                           placeholder="Ej: Jueves de Alitas 2x1"
                           style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                </div>

                {{-- Descripción --}}
                <div class="group">
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Descripción de la Oferta</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="2" data-teclado="texto" data-teclado-titulo="Descripción" 
                              class="w-full rounded-xl py-3.5 px-4 text-base font-bold outline-none transition-all shadow-inner resize-none focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10" 
                              placeholder="Breve nota explicativa para los meseros o clientes..."
                              style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"></textarea>
                </div>

                {{-- Fila: Tipo y Valor --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Dropdown Personalizado: Tipo de Promoción --}}
                    <div class="group" id="dropdownAreaEditarPromocionContainer" x-data="{
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
                            const input = document.getElementById('edit_tipo_promocion');
                            if (input) input.value = opt.value;
                        },
                        init() {
                            this.$watch('$el', () => {});
                            window.actualizarDropdownTipoEditar = (val) => {
                                const match = this.options.find(o => o.value === val);
                                if (match) {
                                    this.selected = match.value;
                                    this.selectedLabel = match.label;
                                }
                            };
                        }
                    }">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Tipo de Promoción</label>
                        
                        <input type="hidden" name="tipo_promocion" id="edit_tipo_promocion" :value="selected" value="porcentaje" required>

                        <div class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full rounded-xl py-3.5 pl-4 pr-10 text-base font-bold outline-none transition-all shadow-inner border flex items-center justify-between focus:border-[#b74309] cursor-pointer"
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
                        <input type="text" name="valor_descuento" id="edit_valor_descuento" required pattern="[0-9]*\.?[0-9]*" data-teclado="numerico" data-teclado-titulo="Valor Descuento / Cantidad" 
                               class="w-full rounded-xl py-3.5 px-4 text-base font-bold outline-none transition-all shadow-inner focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10" 
                               placeholder="Ej: 15.00"
                               style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                    </div>
                </div>

                {{-- Fila: Vigencia de Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Fecha Inicio Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" required 
                                   class="date-input-icon w-full rounded-xl py-3.5 pl-4 pr-10 text-base font-bold outline-none transition-all shadow-inner cursor-pointer focus:border-[#b74309]"
                                   style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <i onclick="abrirCalendario('edit_fecha_inicio')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 text-[#b74309] cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-2" style="color: var(--text-muted);">Fecha Fin Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_fin" id="edit_fecha_fin" required 
                                   class="date-input-icon w-full rounded-xl py-3.5 pl-4 pr-10 text-base font-bold outline-none transition-all shadow-inner cursor-pointer focus:border-[#b74309]"
                                   style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                            <i onclick="abrirCalendario('edit_fecha_fin')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 text-[#b74309] cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                </div>

                {{-- Días de la Semana --}}
                <div>
                    <label class="block uppercase text-[10px] font-black tracking-[0.2em] mb-3" style="color: var(--text-muted);">Días de Aplicación Semanal</label>
                    <div class="grid grid-cols-4 sm:flex gap-2 flex-wrap">
                        @php
                            $mapeoDiasEdit = [
                                ['L' => 1], ['M' => 2], ['M' => 3], ['J' => 4], ['V' => 5], ['S' => 6], ['D' => 7]
                            ];
                        @endphp
                        @foreach($mapeoDiasEdit as $diaData)
                            @php
                                $letra = key($diaData);
                                $num = $diaData[$letra];
                            @endphp
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer hover:border-[#b74309]/50 active:scale-95 transition-all select-none group/day relative"
                                   style="background-color: var(--input-bg); border-color: var(--border-color);">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" id="edit_dia_{{ $num }}" class="edit-dia-checkbox peer sr-only">
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
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" id="edit_prod_{{ $producto->id }}" 
                                       class="edit-prod-checkbox w-5 h-5 rounded-lg cursor-pointer accent-[#b74309] shrink-0"
                                       style="border-color: var(--border-color);">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold group-hover/prod:text-[#b74309] transition-colors leading-snug flex items-center gap-2" style="color: var(--text-color);">
                                        {{ $producto->nombre }}
                                        @if($producto->se_vende_por_peso)
                                            <span class="text-[8px] font-black uppercase tracking-widest text-[#b74309] bg-[#b74309]/10 border border-[#b74309]/20 px-1.5 py-0.5 rounded-md">Por peso</span>
                                        @endif
                                    </p>
                                    @if($producto->se_vende_por_peso)
                                        <p class="text-[11px] font-medium mt-0.5" style="color: var(--text-muted);">${{ number_format($producto->precio_por_100g ?? 0, 2) }} MXN /100g</p>
                                    @else
                                        <p class="text-[11px] font-medium mt-0.5" style="color: var(--text-muted);">${{ number_format($producto->precio, 2) }} MXN</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final --}}
                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t" style="border-top-color: var(--border-color);">
                    <button type="button" onclick="closeModal('modalEditar')" 
                            class="w-full sm:flex-1 py-3.5 sm:py-4 rounded-2xl text-xs font-black uppercase tracking-widest active:scale-95 transition-colors outline-none cursor-pointer border"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                        Cancelar Cambios
                    </button>
                    <button type="submit" 
                            class="w-full sm:flex-1 bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white py-3.5 sm:py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(183,67,9,0.4)] transition-all outline-none cursor-pointer">
                        Actualizar Promoción
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
    .date-input-icon::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 2.5rem;
        height: 100%;
        cursor: pointer;
    }
</style>

<script>
    function abrirCalendario(inputId) {
        const input = document.getElementById(inputId);
        if (input && typeof input.showPicker === 'function') {
            input.showPicker();
        } else if (input) {
            input.focus();
        }
    }
</script>