<div id="modalEliminar" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div class="relative w-full max-w-sm rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[95dvh]" id="deleteContainer"
        style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        
        <div class="p-6 sm:p-10 text-center space-y-5 sm:space-y-6 overflow-y-auto hide-scroll" style="-webkit-overflow-scrolling: touch;">
            
            <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 border border-rose-500/20 shrink-0">
                <i class="far fa-trash-alt text-2xl sm:text-3xl"></i>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl sm:text-2xl font-black tracking-tighter" style="color: var(--text-color);">¿Eliminar Insumo?</h3>
                <p class="text-[10px] sm:text-xs font-bold leading-relaxed px-2 sm:px-4 break-words" style="color: var(--text-muted);">
                    Estás a punto de borrar <span id="delete_nombre_display" class="font-black" style="color: var(--text-color);"></span>. Esta acción no se puede deshacer.
                </p>
            </div>

            <form id="formEliminar" action="" method="POST" class="flex flex-col gap-2 sm:gap-3 pt-2 sm:pt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full h-11 sm:h-14 bg-rose-500 hover:bg-rose-600 text-white rounded-xl sm:rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:-translate-y-1 transition-all active:scale-95 outline-none">
                    Confirmar Eliminación
                </button>
            </form>

            <button type="button" onclick="closeDeleteModal()" class="w-full h-11 sm:h-14 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none hover:opacity-70" style="color: var(--text-muted); margin-bottom: max(0.25rem, env(safe-area-inset-bottom));">
                No, mantenerlo
            </button>
        </div>
    </div>
</div>