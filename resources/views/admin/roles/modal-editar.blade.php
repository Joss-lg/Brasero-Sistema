{{-- resources/views/admin/roles/modal-editar.blade.php --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalEditarRol {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #modalEditarRol .dynamic-modal-content {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

<div id="modalEditarRol" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
    
    <div class="absolute inset-0" onclick="cerrarModalEditar()"></div>

    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 dynamic-modal-content z-10">
        
        <form id="formEditarRol" method="POST" class="p-5 sm:p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- Header compacto dentro del form --}}
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">Editar Puesto</h2>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Actualiza el nombre del puesto</p>
                </div>
                <button type="button" onclick="cerrarModalEditar()" 
                        class="w-8 h-8 flex items-center justify-center shrink-0 rounded-full bg-gray-100 dark:bg-zinc-800 text-gray-500 hover:text-gray-900 dark:hover:text-white active:scale-95 transition-colors outline-none">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <div>
                <label for="editNombre" class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1.5 block">Nombre del Puesto</label>
                <input type="text" id="editNombre" name="nombre" required data-teclado="texto"
                       class="w-full h-11 bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl px-4 text-sm font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-[#b74309] focus:ring-4 focus:ring-[#b74309]/10">
            </div>

            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="cerrarModalEditar()" 
                        class="px-4 py-2.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#b74309] hover:bg-[#8f3207] text-white rounded-full text-xs font-bold transition-all shadow-lg shadow-[#b74309]/20 flex items-center gap-2 active:scale-[0.98]">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.abrirModalEditar = function(btn) {
        const modal = document.getElementById('modalEditarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        const form = document.getElementById('formEditarRol');
        form.action = `{{ url('roles') }}/${btn.getAttribute('data-id')}`;
        document.getElementById('editNombre').value = btn.getAttribute('data-nombre');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    window.cerrarModalEditar = function() {
        const modal = document.getElementById('modalEditarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>