<div id="modal-eliminar-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[100] hidden opacity-0 transition-all duration-300 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 -ml-[74px] sm:ml-0" onclick="cerrarModalEliminar()"></div>
    <div class="relative w-full max-w-md rounded-[1.5rem] sm:rounded-[2rem] shadow-2xl transform opacity-0 scale-95 transition-all duration-300" id="modal-eliminar-panel"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="p-6 sm:p-10 text-center">
            <div class="flex justify-center mb-5 sm:mb-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-3xl sm:text-4xl text-red-500"></i>
                </div>
            </div>

            <h3 class="text-xl sm:text-2xl font-black mb-2 tracking-tight" style="color: var(--text-color);">¿Eliminar Platillo?</h3>
            <p class="text-sm sm:text-base mb-6 sm:mb-8" style="color: var(--text-muted);">
                Estás a punto de eliminar a
                <span class="font-bold block mt-2 break-words" style="color: var(--text-color);" id="nombre-platillo-eliminar">Platillo</span>
            </p>

            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
                <button type="button" onclick="cerrarModalEliminar()" class="flex-1 px-6 py-3 active:scale-95 font-black rounded-xl transition text-sm sm:text-base" style="background-color: var(--input-bg); color: var(--text-muted);">
                    CANCELAR
                </button>
                <button type="button" onclick="confirmarEliminacion()" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-500 active:scale-95 text-white font-black rounded-xl transition shadow-lg shadow-red-900/20 text-sm sm:text-base">
                    <i class="fas fa-trash-alt mr-2"></i> SÍ, ELIMINAR
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let idProductoAEliminar = null;

    window.bloquearScrollFondo = window.bloquearScrollFondo || function () { document.body.style.overflow = 'hidden'; };
    window.desbloquearScrollFondo = window.desbloquearScrollFondo || function () { document.body.style.overflow = ''; };
 
    function abrirModalEliminar(id, nombreProducto) {
        idProductoAEliminar = id;
        document.getElementById('nombre-platillo-eliminar').textContent = nombreProducto;
        const modal = document.getElementById('modal-eliminar-alimento');
        const panel = document.getElementById('modal-eliminar-panel');
        if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
        modal.classList.remove('hidden');
        modal.classList.add('opacity-100');
        bloquearScrollFondo();
        setTimeout(() => { panel.classList.add('opacity-100', 'scale-100'); }, 10);
    }
 
    function cerrarModalEliminar() {
        const modal = document.getElementById('modal-eliminar-alimento');
        const panel = document.getElementById('modal-eliminar-panel');
        panel.classList.remove('opacity-100', 'scale-100');
        desbloquearScrollFondo();
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('opacity-100');
            idProductoAEliminar = null;
        }, 300);
    }
 
    function confirmarEliminacion() {
        if (!idProductoAEliminar) return;
        fetch(RUTA_API_BASE + idProductoAEliminar, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la solicitud');
            return response.json();
        })
        .then(resultado => {
            cerrarModalEliminar();
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message, 'success');
        })
        .catch(error => {
            mostrarNotificacion('Error al eliminar el platillo', 'error');
            console.error(error);
        });
    }
</script>