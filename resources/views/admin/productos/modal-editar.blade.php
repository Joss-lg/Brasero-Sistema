<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modal-editar-wrapper {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #modal-editar-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
            overflow-y: auto !important;
        }
    }
</style>

<div id="modal-editar-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[100] overflow-y-auto overscroll-contain hidden opacity-0 transition-all duration-300">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm -ml-[74px] sm:ml-0" onclick="closeModalEditar()"></div>
    <div id="modal-editar-wrapper" class="flex min-h-screen items-center justify-center p-3 sm:p-4">
        <div class="relative w-full max-w-2xl rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-editar-panel"
            style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <div class="p-5 sm:p-10">
                <div class="flex justify-between items-start mb-6 sm:mb-8">
                    <div>
                        <h2 class="text-xl sm:text-3xl font-black tracking-tight" style="color: var(--text-color);">Editar Platillo</h2>
                        <p class="text-xs sm:text-sm mt-1" style="color: var(--text-muted);">Actualiza la información y receta del platillo</p>
                    </div>
                    <button type="button" onclick="closeModalEditar()" class="w-9 h-9 flex items-center justify-center transition rounded-full active:scale-95 shrink-0 hover:text-[#b74309]" style="color: var(--text-muted);">
                        <i class="fas fa-times text-lg sm:text-2xl"></i>
                    </button>
                </div>

                <form id="formulario-editar-alimento" onsubmit="actualizarProducto(event)">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">

                        {{-- Nombre --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Nombre del Platillo</label>
                            <input type="text" id="edit-nombre" name="nombre" data-teclado="texto" inputmode="none"
                                class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base outline-none transition focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);" required>
                        </div>

                        {{-- Toggle por peso --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="flex items-center justify-between gap-3 bg-orange-500/5 border border-orange-500/20 rounded-2xl p-3.5 sm:p-4 cursor-pointer select-none">
                                <span class="flex items-center gap-2.5">
                                    <i class="fas fa-weight-hanging text-orange-500 text-sm"></i>
                                    <span class="text-xs sm:text-sm font-bold" style="color: var(--text-color);">Se vende por peso</span>
                                </span>
                                <span class="relative inline-flex items-center">
                                    <input type="checkbox" id="edit-se_vende_por_peso" name="se_vende_por_peso" class="peer sr-only" onchange="toggleModoVentaPeso('editar')">
                                    <span class="w-11 h-6 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-[#b74309] transition-colors"></span>
                                    <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>

                        {{-- Precio fijo --}}
                        <div class="col-span-1" id="grupo-precio-fijo-editar">
                            <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Precio</label>
                            <input type="text" id="edit-precio" name="precio" pattern="[0-9]*\.?[0-9]*" data-teclado="numerico" inputmode="none"
                                class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base outline-none transition focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);" required>
                        </div>

                        {{-- Precio por 100g --}}
                        <div class="col-span-1 hidden" id="grupo-precio-peso-editar">
                            <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                            <div class="flex items-center rounded-2xl mt-1.5 sm:mt-2 focus-within:ring-2 focus-within:ring-orange-500 transition" style="background-color: var(--input-bg); border: 1px solid rgba(249,115,22,0.3);">
                                <span class="pl-4 pr-1.5 text-base font-bold select-none" style="color: var(--text-muted);">$</span>
                                <input type="text" id="edit-precio_por_100g" name="precio_por_100g" pattern="[0-9]*\.?[0-9]*" autocomplete="off" data-teclado="numerico" inputmode="none"
                                    class="flex-1 min-w-0 bg-transparent p-3 sm:p-4 pl-0 text-base outline-none transition"
                                    style="color: var(--text-color);" placeholder="50.00">
                            </div>
                            <p class="text-[9px] mt-1 ml-1" style="color: var(--text-muted);">Ej: $50 por 100g → 700g = $350</p>
                        </div>

                        {{-- Categoría --}}
                        <div class="col-span-1 relative">
                            <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Categoría</label>
                            <input type="text" id="edit-categoria_nombre" name="categoria_nombre" list="lista-categorias-editar" data-teclado="texto" inputmode="none"
                                class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base outline-none transition focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"
                                autocomplete="off" required>
                            <input type="hidden" id="edit-categoria_id" name="categoria_id">
                            <datalist id="lista-categorias-editar">
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->nombre }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Descripción</label>
                            <textarea id="edit-descripcion" name="descripcion" rows="3" data-teclado="texto" inputmode="none"
                                class="w-full rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base outline-none transition resize-none focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);"></textarea>
                        </div>

                        {{-- Ingredientes --}}
                        <div class="col-span-1 sm:col-span-2">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                <div>
                                    <label class="text-[11px] sm:text-xs font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Ingredientes de la Receta</label>
                                </div>
                                <button type="button" onclick="agregarIngrediente('editar')" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto self-start bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white px-4 py-2.5 sm:py-2 rounded-2xl font-black transition text-[11px] sm:text-sm shadow-sm">
                                    <i class="fas fa-plus"></i> Agregar ingrediente
                                </button>
                            </div>
                            <div id="ingredientes-container-editar" class="space-y-3 sm:space-y-4 mt-3 sm:mt-4"></div>
                        </div>
                    </div>

                    <div class="mt-8 sm:mt-10 flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
                        <button type="button" onclick="closeModalEditar()" class="flex-1 active:scale-95 font-black py-3 sm:py-4 rounded-2xl transition text-sm" style="background-color: var(--input-bg); color: var(--text-muted);">CANCELAR</button>
                        <button type="submit" class="flex-1 bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white font-black py-3 sm:py-4 rounded-2xl transition shadow-lg shadow-[#b74309]/20 text-sm" id="btn-actualizar">ACTUALIZAR PLATILLO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.bloquearScrollFondo = function () { document.body.style.overflow = 'hidden'; };
    window.desbloquearScrollFondo = function () { document.body.style.overflow = ''; };

    function closeModalEditar() {
        _cerrarModal('modal-editar-alimento', 'modal-editar-panel');
        desbloquearScrollFondo();
        const form = document.getElementById('formulario-editar-alimento');
        if (form) form.reset();
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

    function editarProducto(id) {
        if (!tienePermisoEditar) { mostrarNotificacion('Sin permisos para editar', 'error'); return; }
        const producto = estadoGlobal.productosMap[id];
        if (!producto) return;
        estadoGlobal.editandoId = id;
        document.getElementById('edit-nombre').value              = producto.nombre             ?? '';
        document.getElementById('edit-precio').value              = producto.precio             ?? '';
        document.getElementById('edit-precio_por_100g').value     = producto.precio_por_100g    ?? '';
        document.getElementById('edit-se_vende_por_peso').checked = !!producto.se_vende_por_peso;
        document.getElementById('edit-descripcion').value         = producto.descripcion        ?? '';
        document.getElementById('edit-categoria_nombre').value    = producto.categoria?.nombre  ?? '';
        document.getElementById('edit-categoria_id').value        = producto.categoria?.id      ?? '';
        toggleModoVentaPeso('editar');
        llenarIngredientesEdicion(producto);
        bloquearScrollFondo();
        const modalEditar = document.getElementById('modal-editar-alimento');
        if (modalEditar && modalEditar.parentElement !== document.body) document.body.appendChild(modalEditar);
        _abrirModal('modal-editar-alimento', 'modal-editar-panel');
    }
 
    function actualizarProducto(event) {
        event.preventDefault();
        if (!tienePermisoEditar) { mostrarNotificacion('Sin autorización para editar', 'error'); return; }
        const btn = document.getElementById('btn-actualizar');
        if (!btn || btn.disabled) return;
        const original = btn.textContent;
        btn.textContent = 'ACTUALIZANDO...';
        btn.disabled    = true;
        const catNombre = document.getElementById('edit-categoria_nombre').value;
        const catId     = obtenerCategoriaIdPorNombre(catNombre);
        if (!catId) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        document.getElementById('edit-categoria_id').value = catId;
        const formEl   = document.getElementById('formulario-editar-alimento');
        const formData = new FormData(formEl);
        formData.set('categoria_id', catId);
        formData.set('se_vende_por_peso', document.getElementById('edit-se_vende_por_peso').checked ? '1' : '0');
        formData.set('_method', 'PUT');
        enviarFormularioConImagen(RUTA_API_BASE + estadoGlobal.editandoId, formData, btn, original, closeModalEditar);
    }
</script>