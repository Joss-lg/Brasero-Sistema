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
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Zona / Sección</span>
                <select id="nuevaMesaSeccion"
                        class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors cursor-pointer">
                    <option value="Entrada">Entrada</option>
                    <option value="Salón">Salón</option>
                    <option value="2do Piso">2do Piso</option>
                </select>
            </label>

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
                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Zona / Sección</span>
                <select id="editarMesaSeccion"
                        class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#b74309] transition-colors cursor-pointer">
                    <option value="Entrada">Entrada</option>
                    <option value="Salón">Salón</option>
                    <option value="2do Piso">2do Piso</option>
                </select>
            </label>

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

<script>
    // Conectar la sección activa al abrir el modal de crear mesa
    window.abrirModalNuevaMesa = function() {
        const modal = document.getElementById('modalNuevaMesa');
        if (!modal) return;

        const selectSeccion = document.getElementById('nuevaMesaSeccion');
        if (selectSeccion && window.seccionFiltroActiva && window.seccionFiltroActiva !== 'todas') {
            selectSeccion.value = window.seccionFiltroActiva;
        }

        document.getElementById('nuevaMesaNumero').value = '';
        document.getElementById('nuevaMesaCapacidad').value = '';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform')?.classList.remove('scale-95');
        }, 10);
    };

    window.cerrarModalNuevaMesa = function() {
        const modal = document.getElementById('modalNuevaMesa');
        if (!modal) return;
        modal.classList.add('opacity-0');
        modal.querySelector('.transform')?.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    window.crearNuevaMesa = function() {
        const seccion = document.getElementById('nuevaMesaSeccion')?.value || 'Entrada';
        const numero = document.getElementById('nuevaMesaNumero')?.value?.trim();
        const capacidad = parseInt(document.getElementById('nuevaMesaCapacidad')?.value, 10);

        if (!numero) {
            alert('Ingresa el número o nombre de la mesa.');
            return;
        }
        if (!capacidad || capacidad <= 0) {
            alert('Ingresa una capacidad válida.');
            return;
        }

        fetch("/plano-espacial/api/crear", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                numero: numero,
                capacidad: capacidad,
                seccion: seccion,
                estado: 'disponible'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success || data.mesa) {
                window.location.reload();
            } else {
                alert(data.message || 'Ocurrió un error al crear la mesa.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al procesar la solicitud.');
        });
    };

    window.abrirModalEditarMesa = function(id, numero, capacidad, seccion = 'Entrada') {
        const modal = document.getElementById('modalEditarMesa');
        if (!modal) return;

        document.getElementById('editarMesaId').value = id;
        document.getElementById('editarMesaNumero').value = numero;
        document.getElementById('editarMesaCapacidad').value = capacidad;
        
        const selectSeccion = document.getElementById('editarMesaSeccion');
        if (selectSeccion) {
            selectSeccion.value = seccion || 'Entrada';
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform')?.classList.remove('scale-95');
        }, 10);
    };

    window.cerrarModalEditarMesa = function() {
        const modal = document.getElementById('modalEditarMesa');
        if (!modal) return;
        modal.classList.add('opacity-0');
        modal.querySelector('.transform')?.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    window.guardarMesaEditada = function() {
        const id = document.getElementById('editarMesaId')?.value;
        const seccion = document.getElementById('editarMesaSeccion')?.value || 'Entrada';
        const numero = document.getElementById('editarMesaNumero')?.value?.trim();
        const capacidad = parseInt(document.getElementById('editarMesaCapacidad')?.value, 10);

        if (!id || !numero) {
            alert('Ingresa los datos requeridos.');
            return;
        }

        fetch(`/plano-espacial/api/actualizar/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                numero: numero,
                capacidad: capacidad,
                seccion: seccion
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error al actualizar la mesa.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al guardar la mesa.');
        });
    };

    window.abrirModalEliminarMesa = function(id, numero) {
        const modal = document.getElementById('modalEliminarMesa');
        if (!modal) return;

        document.getElementById('eliminarMesaId').value = id;
        document.getElementById('eliminarMesaNumero').textContent = numero;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform')?.classList.remove('scale-95');
        }, 10);
    };

    window.cerrarModalEliminarMesa = function() {
        const modal = document.getElementById('modalEliminarMesa');
        if (!modal) return;
        modal.classList.add('opacity-0');
        modal.querySelector('.transform')?.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    window.confirmarEliminarMesa = function() {
        const id = document.getElementById('eliminarMesaId')?.value;
        if (!id) return;

        fetch(`/plano-espacial/api/eliminar/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error al eliminar.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al eliminar la mesa.');
        });
    };
</script>

{{-- Teclado virtual: se incluye aquí para que quede junto a los modales que lo usan --}}
@include('partials.teclado-virtual')
<script src="{{ asset('js/teclado-virtual.js') }}"></script>