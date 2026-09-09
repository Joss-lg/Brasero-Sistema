{{-- resources/views/admin/categorias/modal-eliminar.blade.php --}}
<div id="modalEliminar" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all duration-300">
    {{-- Capa trasera para cerrar si hacen click fuera --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeDeleteModal()"></div>
    
    {{-- Contenedor de Alerta Crítica --}}
    <div id="deleteContainer" class="modal-container relative rounded-2xl w-full max-w-sm mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 overflow-hidden"
         style="background-color: var(--card-color); border: 1px solid var(--border-color);">

        {{-- Icono de Advertencia Destacado y Mensaje --}}
        <div class="p-5 sm:p-6 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 flex items-center justify-center mx-auto ring-1 ring-rose-500/20">
                <i class="fas fa-trash-alt text-rose-500 text-lg"></i>
            </div>
            <div class="space-y-1.5">
                <h2 class="text-base font-black tracking-tight" style="color: var(--text-color);">¿Eliminar categoría?</h2>
                <p class="text-xs font-medium leading-relaxed px-2" style="color: var(--text-muted);">
                    Vas a eliminar permanentemente la categoría <span id="delete_nombre_display" class="font-bold underline decoration-rose-500/40 decoration-2 break-words" style="color: var(--text-color);"></span>. Esta acción no se puede revertir.
                </p>
            </div>
        </div>

        {{-- Formulario con Botones Balanceados --}}
        <form id="formEliminar" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex flex-col-reverse sm:flex-row items-center gap-2.5 sm:gap-3 px-5 sm:px-6 pb-5 sm:pb-6">
                {{-- Botón Cancelar (Neutro) --}}
                <button type="button" onclick="closeDeleteModal()"
                    class="w-full sm:flex-1 h-11 sm:h-10 active:scale-95 font-bold text-xs uppercase tracking-wider rounded-xl transition-all outline-none cursor-pointer border"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                    Cancelar
                </button>
                {{-- Botón de Destrucción (Peligro) --}}
                <button type="submit"
                    class="w-full sm:flex-1 h-11 sm:h-10 bg-rose-600 hover:bg-rose-500 active:scale-[0.98] text-white font-black text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-rose-600/10 outline-none cursor-pointer">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Soporte para ambos nombres de función (closeDeleteModal y cerrarModalEliminar)
    if (typeof window.closeDeleteModal !== 'function') {
        window.closeDeleteModal = function() {
            if (typeof window.cerrarModalEliminar === 'function') {
                window.cerrarModalEliminar();
            } else {
                const modal = document.getElementById('modalEliminar');
                if (!modal) return;
                const container = document.getElementById('deleteContainer') || modal.firstElementChild;
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
            }
        };
    }
</script>