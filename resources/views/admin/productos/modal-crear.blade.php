<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modal-crear-alimento {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #modal-crear-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

<div id="modal-crear-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] hidden opacity-0 transition-all duration-300 flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm -ml-[74px] sm:ml-0" onclick="closeModalCrear()"></div>

    <div class="relative w-full max-w-xl sm:max-w-2xl max-h-[92vh] flex flex-col rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300 overflow-hidden" id="modal-crear-panel"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        <div class="p-5 sm:p-10 pb-4 sm:pb-6 flex justify-between items-start" style="border-bottom: 1px solid var(--border-color);">
            <div>
                <h2 class="text-xl sm:text-3xl font-black tracking-tight" style="color: var(--text-color);" id="modal-title">Nuevo Platillo</h2>
                <p class="text-[10px] sm:text-sm mt-1" style="color: var(--text-muted);" id="modal-subtitle">Configuración estética del menú</p>
            </div>
            <button onclick="closeModalCrear()" class="w-9 h-9 flex items-center justify-center transition rounded-full active:scale-95 shrink-0 hover:text-[#b74309]" style="color: var(--text-muted);">
                <i class="fas fa-times text-lg sm:text-2xl"></i>
            </button>
        </div>

        <form id="formulario-crear-producto" onsubmit="guardarProducto(event)" class="overflow-y-auto overscroll-contain flex-1 p-5 sm:p-10 pt-4 sm:pt-6">
            <div class="grid grid-cols-2 gap-4 sm:gap-6">

                {{-- Nombre --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Nombre del Platillo</label>
                    <input type="text" id="nombre" name="nombre" data-teclado="texto" inputmode="none"
                        class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 outline-none transition text-base focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Ej: Lasagna de la Casa" required>
                </div>

                {{-- Toggle: Se vende por peso --}}
                <div class="col-span-2">
                    <label class="flex items-center justify-between gap-3 bg-orange-500/5 border border-orange-500/20 rounded-2xl p-3.5 sm:p-4 cursor-pointer select-none">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-weight-hanging text-orange-500 text-sm"></i>
                            <span class="text-xs sm:text-sm font-bold" style="color: var(--text-color);">Se vende por peso</span>
                        </span>
                        <span class="relative inline-flex items-center">
                            <input type="checkbox" id="se_vende_por_peso" name="se_vende_por_peso" class="peer sr-only" onchange="toggleModoVentaPeso('crear')">
                            <span class="w-11 h-6 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-[#b74309] transition-colors"></span>
                            <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>

                {{-- Precio fijo --}}
                <div class="col-span-2 sm:col-span-1" id="grupo-precio-fijo-crear">
                    <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Precio</label>
                    <input type="text" id="precio" name="precio" pattern="[0-9]*\.?[0-9]*" data-teclado="numerico" inputmode="none"
                        class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 outline-none transition text-base focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="0.00" required>
                </div>

                {{-- Precio por 100g --}}
                <div class="col-span-2 sm:col-span-1 hidden" id="grupo-precio-peso-crear">
                    <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                    <div class="flex items-center rounded-2xl mt-1.5 focus-within:ring-2 focus-within:ring-orange-500 transition" style="background-color: var(--input-bg); border: 1px solid rgba(249,115,22,0.3);">
                        <span class="pl-4 pr-1.5 font-bold select-none" style="color: var(--text-muted);">$</span>
                        <input type="text" id="precio_por_100g" name="precio_por_100g" pattern="[0-9]*\.?[0-9]*" autocomplete="off" data-teclado="numerico" inputmode="none"
                            class="flex-1 min-w-0 bg-transparent p-3 sm:p-4 pl-0 outline-none transition text-base"
                            style="color: var(--text-color);"
                            placeholder="50.00">
                    </div>
                    <p class="text-[9px] mt-1 ml-1" style="color: var(--text-muted);">Ej: $50 por 100g → 700g = $350</p>
                </div>

                {{-- Categoría --}}
                <div class="col-span-2 sm:col-span-1 relative">
                    <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Categoría</label>
                    <input type="text" id="categoria_nombre" name="categoria_nombre" list="lista-categorias" data-teclado="texto" inputmode="none"
                        class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 outline-none transition text-base focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Escribe o selecciona..." autocomplete="off" required>
                    <input type="hidden" id="categoria_id" name="categoria_id">
                    <datalist id="lista-categorias">
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->nombre }}"></option>
                        @endforeach
                    </datalist>
                </div>

                {{-- Descripción --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" data-teclado="texto" inputmode="none"
                        class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 outline-none transition resize-none text-base focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                        style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                        placeholder="Describe qué lleva este platillo..."></textarea>
                </div>

                {{-- Ingredientes --}}
                <div class="col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Ingredientes del Platillo</label>
                            <p class="text-[9px] text-[#b74309] font-bold mt-1 ml-1 tracking-wide uppercase">
                                <i class="fas fa-info-circle mr-1"></i> Selecciona los ingredientes y la cantidad.
                            </p>
                        </div>
                        <button type="button" onclick="agregarIngrediente('crear')" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white px-4 py-2.5 rounded-xl font-black transition text-[11px] sm:text-xs tracking-wider shadow-sm mt-1 sm:mt-0">
                            <i class="fas fa-plus"></i> AGREGAR INGREDIENTE
                        </button>
                    </div>
                    <div id="ingredientes-container-crear" class="space-y-3 mt-3"></div>
                </div>

            </div>

            {{-- Botones --}}
            <div class="mt-8 pt-4 flex flex-col-reverse sm:flex-row gap-3" style="border-top: 1px solid var(--border-color);">
                <button type="button" onclick="closeModalCrear()" class="w-full sm:flex-1 active:scale-95 font-black py-3 sm:py-4 rounded-xl transition text-xs sm:text-sm tracking-widest" style="background-color: var(--input-bg); color: var(--text-muted);">CANCELAR</button>
                <button type="submit" class="w-full sm:flex-1 bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white font-black py-3 sm:py-4 rounded-xl transition shadow-lg shadow-[#b74309]/20 text-xs sm:text-sm tracking-widest" id="btn-guardar">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.bloquearScrollFondo = function () { document.body.style.overflow = 'hidden'; };
    window.desbloquearScrollFondo = function () { document.body.style.overflow = ''; };

    window.openModalCrear = window.abrirModalCrear = function() {
        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');
        if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
        if (modal && panel) {
            modal.classList.remove('hidden');
            bloquearScrollFondo();
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                panel.classList.remove('opacity-0', 'translate-y-8');
            }, 10);
        }
    };

    window.closeModalCrear = function() {
        if (typeof _cerrarModal === 'function') {
            _cerrarModal('modal-crear-alimento', 'modal-crear-panel');
        } else {
            const modal = document.getElementById('modal-crear-alimento');
            const panel = document.getElementById('modal-crear-panel');
            modal.classList.add('opacity-0');
            panel.classList.add('opacity-0', 'translate-y-8');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
        desbloquearScrollFondo();
        const form = document.getElementById('formulario-crear-producto');
        if (form) form.reset();
    };

    function toggleModoVentaPeso(tipo) {
        const checkbox  = document.getElementById(tipo === 'crear' ? 'se_vende_por_peso' : 'edit-se_vende_por_peso');
        const grupoFijo = document.getElementById(tipo === 'crear' ? 'grupo-precio-fijo-crear' : 'grupo-precio-fijo-editar');
        const grupoPeso = document.getElementById(tipo === 'crear' ? 'grupo-precio-peso-crear' : 'grupo-precio-peso-editar');
        const inputFijo = document.getElementById(tipo === 'crear' ? 'precio' : 'edit-precio');
        const inputPeso = document.getElementById(tipo === 'crear' ? 'precio_por_100g' : 'edit-precio_por_100g');
        const esPorPeso = checkbox.checked;
        grupoFijo.classList.toggle('hidden', esPorPeso);
        grupoPeso.classList.toggle('hidden', !esPorPeso);
        inputFijo.required = !esPorPeso;
        inputPeso.required = esPorPeso;
        if (esPorPeso) { inputFijo.value = 0; }
    }

    function enviarFormularioConImagen(url, formData, btn, textoOriginal, onSuccess) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        })
        .then(async (response) => {
            const json = await response.json().catch(() => ({}));
            if (!response.ok) {
                const mensaje = json.message || 'Ocurrió un error al guardar.';
                if (typeof mostrarNotificacion === 'function') mostrarNotificacion(mensaje, 'error');
                else alert(mensaje);
                btn.textContent = textoOriginal;
                btn.disabled = false;
                return;
            }
            if (typeof mostrarNotificacion === 'function') mostrarNotificacion(json.message || 'Guardado correctamente.', 'success');
            if (typeof onSuccess === 'function') onSuccess();
            if (typeof cargarProductos === 'function')   cargarProductos();
            if (typeof cargarEstadisticas === 'function') cargarEstadisticas();
            btn.textContent = textoOriginal;
            btn.disabled = false;
        })
        .catch((err) => {
            console.error(err);
            if (typeof mostrarNotificacion === 'function') mostrarNotificacion('Error de conexión al guardar.', 'error');
            else alert('Error de conexión al guardar.');
            btn.textContent = textoOriginal;
            btn.disabled = false;
        });
    }

    function guardarProducto(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-guardar');
        if (!btn || btn.disabled) return;
        const original = btn.textContent;
        btn.textContent = 'GUARDANDO...';
        btn.disabled    = true;
        const catNombre = document.getElementById('categoria_nombre').value;
        const catId     = obtenerCategoriaIdPorNombre(catNombre);
        if (!catId) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        document.getElementById('categoria_id').value = catId;
        const formEl   = document.getElementById('formulario-crear-producto');
        const formData = new FormData(formEl);
        formData.set('categoria_id', catId);
        formData.set('se_vende_por_peso', document.getElementById('se_vende_por_peso').checked ? '1' : '0');
        enviarFormularioConImagen(RUTA_STORE, formData, btn, original, closeModalCrear);
    }
</script>