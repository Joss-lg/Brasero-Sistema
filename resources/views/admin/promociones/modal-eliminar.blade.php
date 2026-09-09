<div id="modalEliminar" class="hidden fixed inset-0 z-[9999] items-center justify-center bg-black/80 backdrop-blur-sm transition-all duration-300 p-4">
    {{-- Capa trasera para cerrar si hacen clic fuera --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeDeleteModal()"></div>

    <div id="deleteContainer" class="modal-container relative rounded-[1.5rem] sm:rounded-[2rem] w-full max-w-md shadow-2xl scale-95 opacity-0 transition-all duration-300 overflow-hidden"
         style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Contenido del Aviso --}}
        <div class="p-6 sm:p-8 text-center space-y-3 sm:space-y-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center mx-auto shadow-inner">
                <i class="fas fa-trash-alt text-rose-500 text-xl sm:text-2xl"></i>
            </div>
            <div class="space-y-2">
                <h2 class="text-lg sm:text-xl font-black tracking-tight" style="color: var(--text-color);">¿Eliminar promoción?</h2>
                <p class="text-xs font-medium leading-relaxed max-w-xs mx-auto tracking-wide" style="color: var(--text-muted);">
                    Vas a eliminar permanentemente la oferta <span id="delete_nombre_display" class="text-rose-500 dark:text-rose-400 font-bold"></span> del sistema. Esta acción no se puede revertir.
                </p>
            </div>
        </div>

        {{-- Formulario de Acción --}}
        <form id="formEliminar" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div class="flex flex-col-reverse sm:flex-row items-center gap-3 px-6 sm:px-8 pb-6 sm:pb-8">
                <button type="button" onclick="closeDeleteModal()"
                    class="w-full sm:flex-1 py-3.5 sm:py-4 active:scale-95 font-black text-xs uppercase tracking-widest rounded-2xl transition-all border outline-none cursor-pointer"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 py-3.5 sm:py-4 bg-rose-600 hover:bg-rose-500 active:scale-95 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_8px_20px_rgba(244,63,94,0.2)] hover:shadow-[0_8px_25px_rgba(244,63,94,0.4)] outline-none cursor-pointer">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Abre el modal de eliminación inyectando el ID y el Nombre de la promoción de forma dinámica
     */
    function openDeleteModal(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const txtNombre = document.getElementById('delete_nombre_display');

        // Configurar los datos dinámicos de la promoción
        form.action = `/promociones/${id}`;
        txtNombre.textContent = `"${nombre}"`;

        // Mostrar el modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 20);
    }

    /**
     * Cierra el modal aplicando la animación inversa de salida
     */
    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');

        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>