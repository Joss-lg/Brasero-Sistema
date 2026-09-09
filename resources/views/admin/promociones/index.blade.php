@extends('layouts.admin')

@section('title', 'Promociones | El Brasero')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6 sm:space-y-8 relative z-10" style="background-color: var(--bg-color);">
    
    {{-- ENCABEZADO PREMIUM --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 sm:gap-6">
        <div class="space-y-2 sm:space-y-3 max-w-2xl w-full">
            <div class="inline-flex items-center gap-2 rounded-full px-3 sm:px-4 py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.35em] text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/20 shadow-inner">
                <i class="fas fa-tags"></i> Marketing y Ofertas
            </div>
            <h1 class="text-xl sm:text-3xl md:text-4xl font-black tracking-tighter drop-shadow-sm" style="color: var(--text-color);">Promociones y Descuentos</h1>
            <p class="text-xs sm:text-sm font-medium tracking-wide" style="color: var(--text-muted);">Controla las ofertas activas, paquetes especiales y descuentos para tus clientes.</p>
        </div>

        <div class="w-full xl:w-auto mt-2 xl:mt-0">
            @if(auth()->user()->tienePermiso('promociones.crear'))
                <button onclick="openModal('modalCrear')" class="group w-full sm:w-auto relative flex justify-center items-center gap-2 rounded-2xl bg-[#b74309] hover:bg-[#8f3207] px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white transition-all shadow-[0_8px_20px_rgba(183,67,9,0.3)] hover:shadow-[0_8px_25px_rgba(183,67,9,0.5)] hover:-translate-y-0.5 outline-none border-0 active:scale-95 cursor-pointer">
                    <i class="fas fa-plus transition-transform group-hover:rotate-90"></i>
                    Nueva Promo
                </button>
            @endif
        </div>
    </div>

    {{-- BARRA DE BÚSQUEDA Y ESTADÍSTICAS --}}
    <div class="rounded-[24px] p-4 flex flex-col lg:flex-row justify-between items-center gap-4 sm:gap-6 shadow-sm transition-colors" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div class="relative w-full lg:max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none transition-colors" style="color: var(--text-muted);">
                <i class="fas fa-search text-sm group-focus-within:text-[#b74309]"></i>
            </div>
            <input type="text" id="buscadorPromociones" data-teclado="texto" placeholder="Buscar promoción..." 
                class="w-full rounded-xl py-3.5 pl-12 pr-4 text-sm font-medium outline-none transition-all shadow-inner focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
        </div>

        <div class="flex items-center justify-between sm:justify-end w-full lg:w-auto gap-4 sm:gap-6 sm:px-4">
            <div class="text-center sm:text-right flex-1 sm:flex-none">
                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Total Registradas</p>
                <p class="text-xl sm:text-2xl font-black leading-none mt-1" style="color: var(--text-color);">{{ $promociones->count() }}</p>
            </div>
            <div class="w-px h-8 sm:h-10" style="background-color: var(--border-color);"></div>
            <div class="text-center sm:text-left flex-1 sm:flex-none">
                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Activas ahora</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-500 leading-none mt-1">{{ $promociones->where('esta_activa', true)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- TABLERO DE PROMOCIONES --}}
    @if($promociones->isEmpty())
        <div class="rounded-[24px] sm:rounded-[32px] p-8 sm:p-12 md:p-20 text-center shadow-sm flex flex-col items-center justify-center mt-6 sm:mt-8 relative overflow-hidden transition-colors" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <div class="absolute inset-0 bg-gradient-to-b from-[#b74309]/5 to-transparent pointer-events-none"></div>
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-[#b74309]/20 blur-[40px] rounded-full"></div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl flex items-center justify-center shadow-inner relative z-10 group transition-colors" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    <i class="fas fa-ticket-alt text-4xl sm:text-5xl text-[#b74309]/80 group-hover:text-[#b74309] transition-colors"></i>
                </div>
            </div>
            <h2 class="text-xl sm:text-3xl font-black tracking-tight" style="color: var(--text-color);">Sin promociones activas</h2>
            <p class="mt-3 text-xs sm:text-sm font-medium max-w-md" style="color: var(--text-muted);">No tienes ninguna oferta configurada en el sistema. Crea una nueva promo para atraer más clientes.</p>
            
            @if(auth()->user()->tienePermiso('promociones.crear'))
                <button onclick="openModal('modalCrear')" class="mt-6 sm:mt-8 w-full sm:w-auto rounded-2xl px-6 py-3 text-xs font-black uppercase tracking-widest transition-all outline-none active:scale-95 cursor-pointer hover:border-[#b74309]" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                    Comenzar ahora
                </button>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($promociones as $promo)
                <article class="fila-promocion rounded-[24px] p-5 sm:p-6 relative group transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col overflow-hidden" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
                    
                    <div class="absolute inset-x-0 top-0 h-1 {{ $promo->esta_activa ? 'bg-gradient-to-r from-emerald-400 to-teal-500' : '' }}" style="{{ !$promo->esta_activa ? 'background-color: var(--border-color);' : '' }}"></div>

                    {{-- HEADER DE LA TARJETA --}}
                    <div class="flex justify-between items-center mb-5 sm:mb-6 pt-2">
                        <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-2xl {{ $promo->esta_activa ? 'bg-gradient-to-br from-[#b74309] to-[#8f3207] text-white shadow-[0_0_20px_rgba(183,67,9,0.3)]' : '' }} transition-all duration-300" style="{{ !$promo->esta_activa ? 'background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);' : '' }}">
                            <i class="fas fa-ticket-alt text-lg sm:text-xl"></i>
                        </div>
                        
                        @if(auth()->user()->tienePermiso('promociones.editar'))
                            {{-- SWITCH iOS CORREGIDO Y CENTRADO --}}
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input id="togglePromo{{ $promo->id }}" type="checkbox" class="sr-only peer" {{ $promo->esta_activa ? 'checked' : '' }} onchange="togglePromo({{ $promo->id }})">
                                <div class="w-11 h-6 rounded-full px-0.5 flex items-center transition-colors duration-200 peer-checked:bg-emerald-500 shadow-inner bg-slate-300/80 dark:bg-zinc-700/80">
                                    <span class="h-5 w-5 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out" :class="" style="{{ $promo->esta_activa ? 'transform: translateX(20px);' : 'transform: translateX(0px);' }}"></span>
                                </div>
                            </label>
                        @endif
                    </div>

                    {{-- CONTENIDO --}}
                    <div class="mb-5 sm:mb-6 flex-1">
                        <h3 class="nombre-promocion text-lg sm:text-xl font-black tracking-tight leading-tight mb-1" style="color: var(--text-color);">{{ $promo->nombre }}</h3>
                        
                        @if($promo->descripcion)
                            <p class="text-[11px] sm:text-xs font-medium line-clamp-2 mt-1 tracking-wide leading-relaxed" style="color: var(--text-muted);">{{ $promo->descripcion }}</p>
                        @endif
                        
                        <div class="mt-4 flex items-end gap-1.5 sm:gap-2">
                            <span class="bg-clip-text text-transparent bg-gradient-to-r {{ $promo->esta_activa ? 'from-[#b74309] to-[#d95d1e]' : 'from-gray-400 to-gray-500' }} font-black text-3xl sm:text-4xl tracking-tighter">
                                @if($promo->tipo_promocion === 'dos_por_uno')
                                    2x1
                                @elseif($promo->tipo_promocion === 'combo')
                                    Combo
                                @elseif($promo->tipo_promocion === 'porcentaje')
                                    {{ (int)$promo->valor_descuento }}%
                                @else
                                    ${{ number_format($promo->valor_descuento, 2) }}
                                @endif
                            </span>
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1.5 pb-0.5" style="color: var(--text-muted);">Beneficio</span>
                        </div>
                    </div>

                    {{-- DÍAS DE LA SEMANA --}}
                    <div class="mb-5 sm:mb-6">
                        <p class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.3em] mb-2 sm:mb-3" style="color: var(--text-muted);">Días aplicables</p>
                        <div class="flex flex-wrap justify-between gap-1.5 sm:gap-1">
                            @php
                                $dias = $promo->dias_semana;
                                if (is_string($dias)) {
                                    $decode = json_decode($dias, true);
                                    if (is_string($decode)) {
                                        $decode = json_decode($decode, true);
                                    }
                                    $dias = $decode;
                                }
                                $dias = is_array($dias) ? $dias : [];
                            @endphp
                            @foreach(['L','M','M','J','V','S','D'] as $i => $d)
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black transition-colors shrink-0 {{ in_array($i+1, $dias) ? 'bg-[#b74309] text-white shadow-[0_0_10px_rgba(183,67,9,0.4)]' : '' }}" style="{{ !in_array($i+1, $dias) ? 'background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);' : '' }}">
                                    {{ $d }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER DE TARJETA --}}
                    <div class="flex justify-between items-center pt-4 gap-2" style="border-top: 1px solid var(--border-color);">
                        <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[8px] sm:text-[9px] font-black uppercase tracking-widest shrink-0 {{ $promo->esta_activa ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}" style="{{ !$promo->esta_activa ? 'background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $promo->esta_activa ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ $promo->esta_activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            @if(auth()->user()->tienePermiso('promociones.editar'))
                                <button onclick="editPromo({{ $promo->id }})" class="flex h-9 w-9 items-center justify-center rounded-xl transition-all hover:bg-[#b74309]/15 hover:border-[#b74309]/30 hover:text-[#b74309] outline-none active:scale-95 cursor-pointer" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                            @endif

                            @if(auth()->user()->tienePermiso('promociones.eliminar'))
                                <button onclick="openDeleteModal({{ $promo->id }}, '{{ addslashes($promo->nombre) }}')" class="flex h-9 w-9 items-center justify-center rounded-xl transition-all hover:bg-rose-500/15 hover:border-rose-500/30 hover:text-rose-500 outline-none active:scale-95 cursor-pointer" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('admin.promociones.modal-crear')
@include('admin.promociones.modal-editar')
@include('admin.promociones.modal-eliminar')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        ['modalCrear', 'modalEditar', 'modalEliminar'].forEach(id => {
            const el = document.getElementById(id);
            if (el) document.body.appendChild(el);
        });

        const buscador = document.getElementById('buscadorPromociones');
        const filas = document.querySelectorAll('.fila-promocion');
        
        function filtrarPromociones(term) {
            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre-promocion')?.textContent.toLowerCase() ?? '';
                fila.style.display = nombre.includes(term) ? '' : 'none';
            });
        }

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                filtrarPromociones(e.target.value.toLowerCase().trim());
            });
            
            buscador.addEventListener('virtualKeyboardInput', function(e) {
                filtrarPromociones(e.target.value.toLowerCase().trim());
            });
        }
    });

    function openModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        const container = m.querySelector('.modal-container') || m.firstElementChild;
        m.classList.remove('hidden');
        m.classList.add('flex');
        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        const container = m.querySelector('.modal-container') || m.firstElementChild;
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }, 200);
        } else {
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    }

    function togglePromo(id) {
        const checkbox = document.getElementById(`togglePromo${id}`);
        const spanKnob = checkbox?.nextElementSibling?.querySelector('span');
        
        if (spanKnob) {
            spanKnob.style.transform = checkbox.checked ? 'translateX(20px)' : 'translateX(0px)';
        }

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('toggle_status', '1');
        formData.append('esta_activa', checkbox.checked ? '1' : '0');

        fetch(`/promociones/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error);
            checkbox.checked = !checkbox.checked;
            if (spanKnob) {
                spanKnob.style.transform = checkbox.checked ? 'translateX(20px)' : 'translateX(0px)';
            }
            alert('No se pudo cambiar el estado de la promoción.');
        });
    }

   function editPromo(id) {
        fetch(`/promociones/${id}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const promo = data.promocion;
            const form = document.getElementById('formEditarPromocion');
            if (!form) return;
            
            form.action = `/promociones/${id}`;
            const setVal = (name, val) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el) el.value = val;
            };

            setVal('nombre', promo.nombre);
            setVal('descripcion', promo.descripcion || '');
            setVal('tipo_promocion', promo.tipo_promocion);
            setVal('valor_descuento', promo.valor_descuento);
            setVal('fecha_inicio', promo.fecha_inicio);
            setVal('fecha_fin', promo.fecha_fin);
            
            const checkActiva = document.getElementById('edit_esta_activa');
            if (checkActiva) checkActiva.checked = (promo.esta_activa == 1);

            // Sincronizar el dropdown personalizado de Alpine.js
            if (window.actualizarDropdownTipoEditar) {
                window.actualizarDropdownTipoEditar(promo.tipo_promocion);
            }

            // Marcar los días de la semana
            let dias = promo.dias_semana;
            if (typeof dias === 'string') {
                try { dias = JSON.parse(dias); } catch(e) { dias = []; }
            }
            document.querySelectorAll('.edit-dia-checkbox').forEach(cb => {
                cb.checked = Array.isArray(dias) && dias.includes(parseInt(cb.value));
            });

            // Marcar los productos vinculados
            const prodsIds = (promo.productos || []).map(p => p.id);
            document.querySelectorAll('.edit-prod-checkbox').forEach(cb => {
                cb.checked = prodsIds.includes(parseInt(cb.value));
            });
            
            openModal('modalEditar');
        });
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const formEditar = document.getElementById('formEditarPromocion');
        if (formEditar) {
            formEditar.addEventListener('submit', async function (e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const data = await response.json();
                    if (data.success) location.reload();
                    else alert(data.message || 'Error al actualizar');
                } catch (err) { alert('Error al actualizar'); }
            });
        }
    });
</script>

{{-- AQUÍ INCLUIMOS EL COMPONENTE DEL TECLADO VIRTUAL --}}
@include('partials.teclado-virtual')
@endsection