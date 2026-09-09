{{-- resources/views/admin/categorias/modal-editar.blade.php --}}
{{-- Se incluye una vez por cada $categoria dentro del @forelse de index.blade.php --}}

{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto [id^="modalEditar-"],
        body.teclado-virtual-abierto #modalEditarCategoria {
            align-items: flex-start !important;
            padding-top: 10px !important;
        }

        body.teclado-virtual-abierto [id^="modalContainer-"],
        body.teclado-virtual-abierto #editContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

<div id="modalEditar-{{ $categoria->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4 transition-all duration-300">
    {{-- Fondo clickeable para cerrar --}}
    <div class="fixed inset-0 bg-transparent" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"></div>

    <div id="modalContainer-{{ $categoria->id }}"
         class="modal-container relative rounded-2xl p-6 sm:p-8 w-full max-w-md shadow-2xl transform scale-95 opacity-0 transition-all duration-200"
         style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Botón cerrar --}}
        <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
            class="absolute top-5 right-5 hover:opacity-75 transition-opacity outline-none cursor-pointer"
            style="color: var(--text-muted);">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="mb-6">
            <h2 class="text-xl font-bold" style="color: var(--text-color);">Editar Categoría</h2>
            <p class="text-xs mt-1" style="color: var(--text-muted);">Actualiza los datos de "{{ $categoria->nombre }}"</p>
        </div>

        {{-- Formulario --}}
        <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST" class="space-y-5 overflow-y-auto">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div class="space-y-2">
                <label for="edit_nombre_{{ $categoria->id }}" class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider" style="color: var(--text-muted);">
                    <i class="fas fa-tag text-[#b74309] text-[10px]"></i> Nombre de la Categoría
                </label>
                <input type="text" id="edit_nombre_{{ $categoria->id }}" name="nombre" required 
                    data-teclado="texto" data-teclado-titulo="Nombre de Categoría" autocomplete="off"
                    value="{{ $categoria->nombre }}"
                    class="w-full h-12 rounded-2xl px-4 text-base font-semibold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                    style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
            </div>

            {{-- Dropdown Personalizado: Área de Impresión --}}
            @php
                $initialArea = $categoria->area_impresion ?? '';
                $initialAreaLabel = match($initialArea) {
                    'Cocina' => 'Cocina / Calientes',
                    'Barra' => 'Barra / Bebidas',
                    'Parrilla' => 'Parrilla',
                    default => 'Sin asignar',
                };
            @endphp
            <div class="space-y-2" id="dropdownAreaEditarContainer_{{ $categoria->id }}" x-data="{
                open: false,
                selected: @js($initialArea),
                selectedLabel: @js($initialAreaLabel),
                options: [
                    { value: '', label: 'Sin asignar' },
                    { value: 'Cocina', label: 'Cocina / Calientes' },
                    { value: 'Barra', label: 'Barra / Bebidas' },
                    { value: 'Parrilla', label: 'Parrilla' }
                ],
                select(opt) {
                    this.selected = opt.value;
                    this.selectedLabel = opt.label;
                    this.open = false;
                    const input = document.getElementById('edit_area_{{ $categoria->id }}');
                    if (input) input.value = opt.value;
                    const lbl = document.getElementById('label_edit_area_{{ $categoria->id }}');
                    if (lbl) lbl.textContent = opt.label;
                }
            }">
                <label class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider" style="color: var(--text-muted);">
                    <i class="fas fa-print text-[#b74309] text-[10px]"></i> Área de Impresión
                </label>

                <input type="hidden" id="edit_area_{{ $categoria->id }}" name="area_impresion" :value="selected" value="{{ $categoria->area_impresion ?? '' }}">

                <div class="relative">
                    <button type="button" 
                        @click="open = !open"
                        onclick="toggleDropdownEditarArea('{{ $categoria->id }}', event)"
                        id="btn_edit_area_{{ $categoria->id }}"
                        class="w-full h-12 rounded-2xl px-4 flex items-center justify-between text-base font-semibold outline-none transition-all border focus:border-[#b74309]"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        <span id="label_edit_area_{{ $categoria->id }}" x-text="selectedLabel">{{ $initialAreaLabel }}</span>
                        <i id="icon_edit_area_chevron_{{ $categoria->id }}" class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }" style="color: var(--text-muted);"></i>
                    </button>

                    <div id="menu_edit_area_{{ $categoria->id }}" 
                        x-show="open" 
                        @click.outside="open = false" 
                        x-transition
                        class="hidden absolute z-50 w-full mt-2 rounded-2xl shadow-2xl py-2 border overflow-hidden"
                        style="background-color: var(--card-color); border-color: var(--border-color);">
                        
                        <div onclick="seleccionarEditarArea('{{ $categoria->id }}', '', 'Sin asignar')"
                            @click="select({ value: '', label: 'Sin asignar' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Sin asignar
                        </div>

                        <div onclick="seleccionarEditarArea('{{ $categoria->id }}', 'Cocina', 'Cocina / Calientes')"
                            @click="select({ value: 'Cocina', label: 'Cocina / Calientes' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Cocina / Calientes
                        </div>

                        <div onclick="seleccionarEditarArea('{{ $categoria->id }}', 'Barra', 'Barra / Bebidas')"
                            @click="select({ value: 'Barra', label: 'Barra / Bebidas' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Barra / Bebidas
                        </div>

                        <div onclick="seleccionarEditarArea('{{ $categoria->id }}', 'Parrilla', 'Parrilla')"
                            @click="select({ value: 'Parrilla', label: 'Parrilla' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Parrilla
                        </div>
                    </div>
                </div>
            </div>

            {{-- Color --}}
            <div class="space-y-2">
                <label for="edit_color_{{ $categoria->id }}" class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider" style="color: var(--text-muted);">
                    <i class="fas fa-palette text-[#b74309] text-[10px]"></i> Color
                </label>
                <div class="flex items-center gap-3 w-full h-12 rounded-2xl px-3" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    <input type="color" id="edit_color_{{ $categoria->id }}" name="color"
                        value="{{ $categoria->color ?? '#b74309' }}"
                        class="w-8 h-8 rounded-xl cursor-pointer border-0 bg-transparent outline-none">
                    <span class="text-[13px] font-bold" style="color: var(--text-muted);">Elige un color</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-2 gap-4">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
                    class="w-full sm:w-auto text-center text-[11px] font-black uppercase tracking-widest outline-none py-3 cursor-pointer transition-colors"
                    style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 h-12 bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-[11px] uppercase tracking-widest rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#b74309]/20 active:scale-95">
                    <i class="fas fa-save text-sm"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });

    if (typeof window.cerrarModalEspecifico !== 'function') {
        window.cerrarModalEspecifico = function(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const container = modal.querySelector('.modal-container') || modal.querySelector('[id^="modalContainer-"]') || modal.firstElementChild;
            if (container) {
                container.classList.remove('scale-100', 'opacity-100');
                container.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 200);
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };
    }

    if (typeof window.toggleDropdownEditarArea !== 'function') {
        window.toggleDropdownEditarArea = function(id, e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('menu_edit_area_' + id);
            const icon = document.getElementById('icon_edit_area_chevron_' + id);
            if (!menu) return;

            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                if (icon) icon.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-180');
            }
        };
    }

    if (typeof window.seleccionarEditarArea !== 'function') {
        window.seleccionarEditarArea = function(id, value, label) {
            const input = document.getElementById('edit_area_' + id);
            const display = document.getElementById('label_edit_area_' + id);
            const menu = document.getElementById('menu_edit_area_' + id);
            const icon = document.getElementById('icon_edit_area_chevron_' + id);

            if (input) input.value = value;
            if (display) display.textContent = label;
            if (menu) menu.classList.add('hidden');
            if (icon) icon.classList.remove('rotate-180');
        };
    }

    document.addEventListener('click', function(e) {
        document.querySelectorAll('[id^="menu_edit_area_"]').forEach(menu => {
            const id = menu.id.replace('menu_edit_area_', '');
            const container = document.getElementById('dropdownAreaEditarContainer_' + id);
            const icon = document.getElementById('icon_edit_area_chevron_' + id);
            if (container && !container.contains(e.target)) {
                menu.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-180');
            }
        });
    });
</script>