{{-- resources/views/admin/roles/modal-crear.blade.php --}}

<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearRol {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto .dynamic-modal-content {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

<div id="modalCrearRol" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
    
    <div class="absolute inset-0" onclick="cerrarModalCrear()"></div>

    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] sm:rounded-[2rem] w-full max-w-lg p-6 sm:p-8 shadow-2xl relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300 dynamic-modal-content z-10 max-h-[92vh] overflow-y-auto">
        
        <div class="absolute top-[-10%] left-[-10%] w-32 h-32 rounded-full bg-[#b74309]/10 blur-[60px] pointer-events-none"></div>

        <div class="flex justify-between items-center mb-5 sm:mb-6 relative">
            <h2 class="text-lg sm:text-xl font-black text-[var(--text-color)] tracking-tight">Crear Puesto Nuevo</h2>
            <button type="button" onclick="cerrarModalCrear()" class="w-9 h-9 flex items-center justify-center shrink-0 text-[var(--text-muted)] hover:text-[var(--text-color)] active:scale-95 transition-colors hover:bg-black/5 dark:hover:bg-white/5 rounded-full outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-5 sm:space-y-6 relative">
            @csrf
            
            <div>
                <label for="nombre" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Nombre del Puesto</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Guardia Nocturno" required 
                        data-teclado="texto"
                       class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-base text-[var(--text-color)] outline-none transition-all focus:border-[#b74309] focus:ring-1 focus:ring-[#b74309]">
                @error('nombre') <span class="text-xs text-rose-500 mt-2 block font-bold">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-[var(--border-color)] flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="cerrarModalCrear()" 
                        class="w-full sm:w-auto px-5 py-3 rounded-xl text-xs font-bold uppercase text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5 active:scale-95 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold bg-[#b74309] hover:bg-[#8f3207] text-white transition-all shadow-lg shadow-[#b74309]/20 active:scale-[0.98] outline-none">
                    <i class="fas fa-save mr-2"></i> Guardar Puesto
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

    window.abrirModalCrear = function() {
        const modal = document.getElementById('modalCrearRol');
        const content = modal.querySelector('.dynamic-modal-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    window.cerrarModalCrear = function() {
        const modal = document.getElementById('modalCrearRol');
        const content = modal.querySelector('.dynamic-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>