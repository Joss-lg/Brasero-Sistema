{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrear,
        body.teclado-virtual-abierto #modalCrearCategoria {
            align-items: flex-start !important;
            padding-top: 10px !important;
        }

        body.teclado-virtual-abierto #createContainer {
            max-height: 45dvh !important;
            transform: translateY(0) scale(0.95) !important;
        }
    }
</style>

<div id="modalCrearCategoria"
    class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="fixed inset-0 bg-transparent" onclick="closeCreateModal()"></div>

    <div id="createContainer"
        class="modal-container relative rounded-[24px] w-full max-w-md mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 flex flex-col overflow-hidden max-h-[92vh]"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Encabezado del Modal --}}
        <div class="flex items-center gap-4 p-5 sm:p-6 pb-5 shrink-0" style="background-color: var(--card-color);">
            <div
                class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-[#b74309]/10 border border-[#b74309]/20 flex items-center justify-center shrink-0">
                <i class="fas fa-plus text-[#b74309] text-lg"></i>
            </div>
            <div>
                <h2 class="text-[17px] sm:text-[19px] font-black tracking-tight leading-tight"
                    style="color: var(--text-color);">
                    Nueva Categoría
                </h2>
                <p class="text-[10px] font-bold uppercase tracking-widest mt-1" style="color: var(--text-muted);">
                    Agregar al menú
                </p>
            </div>
            <button type="button" onclick="closeCreateModal()"
                class="ml-auto w-9 h-9 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/5 active:scale-95 transition-colors outline-none shrink-0"
                style="color: var(--text-muted);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mx-6 shrink-0" style="border-top: 1px solid var(--border-color);"></div>

        {{-- Formulario --}}
        <form action="{{ route('admin.categorias.store') }}" method="POST"
            class="px-5 sm:px-6 py-6 space-y-6 overflow-y-auto">
            @csrf

            {{-- Input: Nombre con teclado virtual --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider"
                    style="color: var(--text-muted);">
                    <i class="fas fa-tag text-[#b74309] text-[10px]"></i> Nombre de Categoría
                </label>
                <input type="text" name="nombre" placeholder="Ej: Platos Fuertes..." required data-teclado="texto"
                    class="w-full h-12 rounded-2xl px-4 text-base font-semibold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                    style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
            </div>

            {{-- Dropdown Personalizado: Área de Impresión --}}
            <div class="space-y-2" id="dropdownAreaCrearContainer" x-data="{
                open: false,
                selected: 'Cocina',
                selectedLabel: 'Cocina / Calientes',
                options: [
                    { value: 'Cocina', label: 'Cocina / Calientes' },
                    { value: 'Barra', label: 'Barra / Bebidas' }
                ],
                select(opt) {
                    this.selected = opt.value;
                    this.selectedLabel = opt.label;
                    this.open = false;
                    const input = document.getElementById('input_crear_area_impresion');
                    if (input) input.value = opt.value;
                    const lbl = document.getElementById('label_crear_area_impresion');
                    if (lbl) lbl.textContent = opt.label;
                }
            }">
                <label class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider"
                    style="color: var(--text-muted);">
                    <i class="fas fa-print text-[#b74309] text-[10px]"></i> Área de Impresión
                </label>

                <input type="hidden" name="area_impresion" id="input_crear_area_impresion" value="Cocina" required>

                <div class="relative">
                    <button type="button" @click="open = !open" onclick="toggleDropdownCrearArea(event)"
                        id="btn_crear_area_impresion"
                        class="w-full h-12 rounded-2xl px-4 flex items-center justify-between text-base font-semibold outline-none transition-all border focus:border-[#b74309]"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        <span id="label_crear_area_impresion" x-text="selectedLabel">Cocina / Calientes</span>
                        <i id="icon_crear_area_chevron"
                            class="fas fa-chevron-down text-xs transition-transform duration-200"
                            :class="{ 'rotate-180': open }" style="color: var(--text-muted);"></i>
                    </button>

                    <div id="menu_crear_area_impresion" x-show="open" @click.outside="open = false" x-transition
                        class="hidden absolute z-50 w-full mt-2 rounded-2xl shadow-2xl py-2 border overflow-hidden"
                        style="background-color: var(--card-color); border-color: var(--border-color);">

                        <div onclick="seleccionarCrearArea('Cocina', 'Cocina / Calientes')"
                            @click="select({ value: 'Cocina', label: 'Cocina / Calientes' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Cocina / Calientes
                        </div>

                        <div onclick="seleccionarCrearArea('Barra', 'Barra / Bebidas')"
                            @click="select({ value: 'Barra', label: 'Barra / Bebidas' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Barra / Bebidas
                        </div>

                        <div onclick="seleccionarCrearArea('Parrilla', 'Parrilla')"
                            @click="select({ value: 'Parrilla', label: 'Parrilla' })"
                            class="px-4 py-2.5 text-sm font-semibold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                            style="color: var(--text-color);">
                            Parrilla
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selector de Color --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider"
                    style="color: var(--text-muted);">
                    <i class="fas fa-palette text-[#b74309] text-[10px]"></i> Color
                </label>
                <div class="flex items-center gap-3 w-full h-12 rounded-2xl px-3"
                    style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    <input type="color" name="color" value="#b74309"
                        class="w-8 h-8 rounded-xl cursor-pointer border-0 bg-transparent outline-none">
                    <span class="text-[13px] font-bold" style="color: var(--text-muted);">Elige un color</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-2 gap-4">
                <button type="button" onclick="closeCreateModal()"
                    class="w-full sm:w-auto text-center text-[11px] font-black uppercase tracking-widest outline-none py-3 cursor-pointer transition-colors"
                    style="color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 h-12 bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-[11px] uppercase tracking-widest rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#b74309]/20 active:scale-95">
                    <i class="fas fa-save text-sm"></i> Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });

    // Soporte para toggle nativo / fallback del dropdown
    function toggleDropdownCrearArea(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('menu_crear_area_impresion');
        const icon = document.getElementById('icon_crear_area_chevron');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (icon) icon.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (icon) icon.classList.remove('rotate-180');
        }
    }

    function seleccionarCrearArea(value, label) {
        const input = document.getElementById('input_crear_area_impresion');
        const display = document.getElementById('label_crear_area_impresion');
        const menu = document.getElementById('menu_crear_area_impresion');
        const icon = document.getElementById('icon_crear_area_chevron');

        if (input) input.value = value;
        if (display) display.textContent = label;
        if (menu) menu.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
    }

    document.addEventListener('click', function (e) {
        const container = document.getElementById('dropdownAreaCrearContainer');
        const menu = document.getElementById('menu_crear_area_impresion');
        const icon = document.getElementById('icon_crear_area_chevron');

        if (menu && container && !container.contains(e.target)) {
            menu.classList.add('hidden');
            if (icon) icon.classList.remove('rotate-180');
        }
    });

    // Funciones globales de apertura y cierre
    window.openCreateModal = function () {
        const modal = document.getElementById('modalCrearCategoria') || document.getElementById('modalCrear');
        if (!modal) return;

        // Reinicializar Alpine si está disponible en el entorno
        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(modal);
        }

        const container = modal.querySelector('.modal-container') || document.getElementById('createContainer') || modal.firstElementChild;
        modal.classList.remove('hidden');

        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    };

    window.closeCreateModal = function () {
        const modal = document.getElementById('modalCrearCategoria') || document.getElementById('modalCrear');
        if (!modal) return;

        const container = modal.querySelector('.modal-container') || document.getElementById('createContainer') || modal.firstElementChild;

        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        } else {
            modal.classList.add('hidden');
        }
    };
</script>