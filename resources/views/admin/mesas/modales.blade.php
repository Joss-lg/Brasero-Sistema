{{-- Modal: Crear Nueva Mesa --}}
<div id="modalNuevaMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300">
        <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
            <div>
                <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Nueva Mesa</h2>
                <p class="text-[10px] uppercase tracking-widest text-emerald-500 font-bold mt-1">Crear Registro</p>
            </div>
            <button type="button" onclick="cerrarModalNuevaMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none cursor-pointer"><i class="fas fa-times"></i></button>
        </div>
        <div class="grid gap-4">
            <label class="block">
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Nombre / Número</span>
                <input id="nuevaMesaNumero" type="text"
                    data-teclado="texto"
                    data-teclado-titulo="Número de Mesa"
                    data-teclado-max="10"
                    class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors">
            </label>
            <label class="block">
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                <input id="nuevaMesaCapacidad" type="text" inputmode="numeric"
                    data-teclado="numerico"
                    data-teclado-titulo="Capacidad"
                    data-teclado-max="3"
                    class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors">
            </label>
        </div>
        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
            <button type="button" onclick="cerrarModalNuevaMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none cursor-pointer">Cancelar</button>
            <button type="button" onclick="crearNuevaMesa()" class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition outline-none shadow-sm cursor-pointer active:scale-95">Crear Mesa</button>
        </div>
    </div>
</div>

{{-- Modal: Editar Mesa --}}
<div id="modalEditarMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300">
        <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
            <div>
                <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Editar Mesa</h2>
                <p class="text-[10px] uppercase tracking-widest text-[#b74309] dark:text-[#e8946a] font-bold mt-1">Ajustes Generales</p>
            </div>
            <button type="button" onclick="cerrarModalEditarMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none cursor-pointer"><i class="fas fa-times"></i></button>
        </div>
        <input type="hidden" id="editarMesaId">
        <div class="grid gap-4">
            <label class="block">
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Nombre / Número</span>
                <input id="editarMesaNumero" type="text"
                    data-teclado="texto"
                    data-teclado-titulo="Número de Mesa"
                    data-teclado-max="10"
                    class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#b74309] transition-colors">
            </label>
            <label class="block">
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                <input id="editarMesaCapacidad" type="text" inputmode="numeric"
                    data-teclado="numerico"
                    data-teclado-titulo="Capacidad"
                    data-teclado-max="3"
                    class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#b74309] transition-colors">
            </label>
        </div>
        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
            <button type="button" onclick="cerrarModalEditarMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none cursor-pointer">Cancelar</button>
            <button type="button" onclick="guardarMesaEditada()" class="px-5 py-2.5 rounded-xl bg-[#b74309] text-white text-xs font-black uppercase tracking-widest hover:bg-[#8f3207] transition outline-none shadow-sm cursor-pointer active:scale-95">Guardar</button>
        </div>
    </div>
</div>

{{-- Modal: Eliminar Mesa --}}
<div id="modalEliminarMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300">
        <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
            <div>
                <h2 class="text-xl font-black text-rose-500 tracking-tight">Eliminar Mesa</h2>
                <p class="text-[10px] uppercase tracking-widest text-[var(--text-muted)] font-bold mt-1">Acción irreversible</p>
            </div>
            <button type="button" onclick="cerrarModalEliminarMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none cursor-pointer"><i class="fas fa-times"></i></button>
        </div>
        <input type="hidden" id="eliminarMesaId">
        <div class="mb-2 p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl">
            <p class="text-sm font-bold text-[var(--text-color)]">¿Confirmas la eliminación?</p>
            <p class="text-xs text-[var(--text-muted)] mt-1.5">La mesa <span id="eliminarMesaNumero" class="font-black text-rose-400"></span> será borrada permanentemente.</p>
        </div>
        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
            <button type="button" onclick="cerrarModalEliminarMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none cursor-pointer">Cancelar</button>
            <button type="button" onclick="confirmarEliminarMesa()" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-500 transition outline-none shadow-sm cursor-pointer active:scale-95">Eliminar</button>
        </div>
    </div>
</div>

{{-- Teclado virtual: se incluye aquí para que quede junto a los modales que lo usan --}}
@include('partials.teclado-virtual')
<script src="{{ asset('js/teclado-virtual.js') }}"></script>