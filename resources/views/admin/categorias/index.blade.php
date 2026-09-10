@extends('layouts.admin')

@section('title', 'Categorías | El Brasero')
@section('header-title', 'Gestión de Categorías')
@section('header-subtitle', 'Organiza el menú de tu restaurante mediante bloques estructurales. Control rápido, preciso y con información en tiempo real.')

@section('content')
<div class="min-h-screen p-4 sm:p-6 lg:p-10" style="background-color: var(--bg-color);">
    <div class="max-w-[1400px] mx-auto space-y-6">

        {{-- CABECERA --}}
        <div class="rounded-3xl p-6 sm:p-8 lg:p-10" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
            <div class="flex flex-col lg:flex-row lg:items-start gap-6 lg:gap-10">

                {{-- Info izquierda --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] font-black text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/20 px-3 py-1 rounded-full">● CATÁLOGO</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight mb-3" style="color: var(--text-color);">
                        Gestión de Categorías
                    </h1>
                    <p class="text-sm sm:text-base font-medium leading-relaxed max-w-xl" style="color: var(--text-muted);">
                        Organiza el menú de tu restaurante mediante bloques estructurales. Control rápido, preciso y con información en tiempo real.
                    </p>

                    {{-- Buscador + botón --}}
                    <div class="flex flex-col sm:flex-row gap-3 mt-6 sm:mt-8">
                        <div class="relative flex-1 max-w-sm">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color: var(--text-muted);"></i>
                            <input type="text" id="buscadorCategorias"
                                placeholder="Buscar categoría por nombre..."
                                class="w-full pl-11 pr-4 py-3 rounded-2xl text-sm font-medium outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                                style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
                        </div>

                        @if(auth()->user()->tienePermiso('categorias.crear'))
                            <button type="button" onclick="openCreateModal()"
                                class="inline-flex items-center gap-2 bg-[#b74309] hover:bg-[#8f3207] text-white text-sm font-bold px-5 py-2.5 rounded-2xl transition-all shadow-lg shadow-[#b74309]/20 active:scale-95 whitespace-nowrap">
                                <i class="fas fa-plus text-xs"></i>
                                CREAR CATEGORÍA
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Stats derecha --}}
                <div class="flex flex-row lg:flex-col gap-3 lg:gap-4 lg:w-56 shrink-0">
                    <div class="flex-1 lg:flex-none rounded-2xl p-4 lg:p-5" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                        <p class="text-3xl lg:text-4xl font-black" style="color: var(--text-color);">{{ $categorias->count() }}</p>
                        <p class="text-xs font-bold mt-1" style="color: var(--text-muted);">Bloques registrados en el menú</p>
                    </div>
                    <div class="flex-1 lg:flex-none rounded-2xl p-4 lg:p-5" style="border: 1px solid rgba(34,197,94,0.3); background-color: rgba(34,197,94,0.05);">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Platillos Activos</p>
                            <i class="fas fa-utensils text-emerald-500 text-sm"></i>
                        </div>
                        <p class="text-3xl lg:text-4xl font-black text-emerald-600 dark:text-emerald-400">{{ $totalProductos ?? 0 }}</p>
                        <p class="text-xs font-bold mt-1 text-emerald-600/70 dark:text-emerald-400/70">Asignados a través del sistema</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="rounded-3xl overflow-hidden" style="background-color: var(--card-color); border: 1px solid var(--border-color);">

            <div class="flex items-center justify-between px-6 sm:px-8 py-4 sm:py-5" style="border-bottom: 1px solid var(--border-color);">
                <h2 class="text-base sm:text-lg font-black" style="color: var(--text-color);">Listado de Categorías</h2>
                <span class="text-xs font-bold px-3 py-1 rounded-full" style="color: var(--text-muted); background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    {{ $categorias->count() }} Registros
                </span>
            </div>

            {{-- VISTA MÓVIL --}}
            <div class="sm:hidden divide-y" style="border-color: var(--border-color);" id="listaMobileCategorias">
                @forelse($categorias as $categoria)
                <div class="fila-categoria-movil flex items-center gap-3 px-4 py-3.5">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-black text-white shrink-0"
                        style="background-color: {{ $categoria->color ?? '#b74309' }};">
                        {{ strtoupper(substr($categoria->nombre, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm truncate nombre-categoria" style="color: var(--text-color);">{{ $categoria->nombre }}</p>
                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Añadido el {{ $categoria->created_at->format('d M, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(auth()->user()->tienePermiso('categorias.editar'))
                            <button type="button" onclick="openEditModal(this)"
                                data-id="{{ $categoria->id }}"
                                data-nombre="{{ $categoria->nombre }}"
                                data-area="{{ $categoria->area_impresion ?? '' }}"
                                class="w-8 h-8 flex items-center justify-center rounded-xl bg-[#b74309]/10 text-[#b74309] dark:text-[#e8946a] hover:bg-[#b74309]/20 transition-all active:scale-95">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                        @endif
                        @if(auth()->user()->tienePermiso('categorias.eliminar'))
                            <button type="button" onclick="abrirModalEliminar(this)"
                                data-id="{{ $categoria->id }}"
                                data-nombre="{{ $categoria->nombre }}"
                                class="w-8 h-8 flex items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500/20 transition-all active:scale-95">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-12 text-center" style="color: var(--text-muted);">
                    <i class="fas fa-layer-group text-3xl mb-3 opacity-30"></i>
                    <p class="text-sm font-bold">No hay categorías registradas.</p>
                </div>
                @endforelse
            </div>

            {{-- VISTA ESCRITORIO --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest" style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th class="px-6 sm:px-8 py-4">Categoría</th>
                            <th class="px-6 sm:px-8 py-4">Área de Impresión</th>
                            <th class="px-6 sm:px-8 py-4">Contenido</th>
                            <th class="px-6 sm:px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategorias">
                        @forelse($categorias as $categoria)
                        <tr class="fila-categoria group transition-colors hover:bg-[#b74309]/[0.03]" style="border-bottom: 1px solid var(--border-color);">

                            {{-- Nombre --}}
                            <td class="px-6 sm:px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-black text-white shrink-0"
                                        style="background-color: {{ $categoria->color ?? '#b74309' }};">
                                        {{ strtoupper(substr($categoria->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm nombre-categoria" style="color: var(--text-color);">{{ $categoria->nombre }}</p>
                                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Añadido el {{ $categoria->created_at->format('d M, Y') }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Área de impresión --}}
                            <td class="px-6 sm:px-8 py-4">
                                @if($categoria->area_impresion)
                                    @php
                                        $areaColor = match(strtolower($categoria->area_impresion)) {
                                            'cocina'    => 'text-orange-600 dark:text-orange-400 bg-orange-500/10 border-orange-500/20',
                                            'barra'     => 'text-purple-600 dark:text-purple-400 bg-purple-500/10 border-purple-500/20',
                                            'parrilla'  => 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                                            default     => 'text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border-[#b74309]/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border {{ $areaColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ ucfirst($categoria->area_impresion) }}
                                    </span>
                                @else
                                    <span class="text-xs font-bold" style="color: var(--text-muted);">—</span>
                                @endif
                            </td>

                            {{-- Contenido --}}
                            <td class="px-6 sm:px-8 py-4">
                                <span class="text-xs font-bold text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/20 px-3 py-1 rounded-xl whitespace-nowrap">
                                    {{ $categoria->productos_count }} Platillo{{ $categoria->productos_count == 1 ? '' : 's' }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 sm:px-8 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->tienePermiso('categorias.editar'))
                                        <button type="button" onclick="openEditModal(this)"
                                            data-id="{{ $categoria->id }}"
                                            data-nombre="{{ $categoria->nombre }}"
                                            data-area="{{ $categoria->area_impresion ?? '' }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-[#b74309]/10 text-[#b74309] dark:text-[#e8946a] hover:bg-[#b74309]/20 transition-all active:scale-95">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->tienePermiso('categorias.eliminar'))
                                        <button type="button" onclick="abrirModalEliminar(this)"
                                            data-id="{{ $categoria->id }}"
                                            data-nombre="{{ $categoria->nombre }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500/20 transition-all active:scale-95">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL EDITAR ESPECÍFICO DE ESTA CATEGORÍA --}}
                        @if(auth()->user()->tienePermiso('categorias.editar'))
                            @include('admin.categorias.modal-editar', ['categoria' => $categoria])
                        @endif

                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center">
                                <i class="fas fa-layer-group text-4xl mb-4 opacity-20" style="color: var(--text-muted);"></i>
                                <p class="text-sm font-bold" style="color: var(--text-muted);">No hay categorías registradas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->tienePermiso('categorias.crear')) @include('admin.categorias.modal-crear') @endif
@if(auth()->user()->tienePermiso('categorias.eliminar')) @include('admin.categorias.modal-eliminar') @endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mover modales fijos al final del body para evitar problemas de stacking context
        ['modalCrearCategoria', 'modalCrear', 'modalEliminar'].forEach(id => {
            const el = document.getElementById(id);
            if (el) document.body.appendChild(el);
        });

        // Mover todos los modales de edición al body
        document.querySelectorAll('[id^="modalEditar-"]').forEach(el => {
            document.body.appendChild(el);
        });

        const buscador = document.getElementById('buscadorCategorias');
        const filasDesktop = document.querySelectorAll('.fila-categoria');
        const filasMovil   = document.querySelectorAll('.fila-categoria-movil');

        function filtrar(term) {
            filasDesktop.forEach(f => {
                const nombre = f.querySelector('.nombre-categoria')?.textContent.toLowerCase() ?? '';
                f.style.display = nombre.includes(term) ? '' : 'none';
            });
            filasMovil.forEach(f => {
                const nombre = f.querySelector('.nombre-categoria')?.textContent.toLowerCase() ?? '';
                f.style.display = nombre.includes(term) ? '' : 'none';
            });
        }

        if (buscador) buscador.addEventListener('input', e => filtrar(e.target.value.toLowerCase().trim()));
    });

    // Abrir Modal Crear
    window.openCreateModal = function() {
        const modal = document.getElementById('modalCrearCategoria') || document.getElementById('modalCrear');
        if (!modal) return;
        const container = modal.querySelector('.modal-container') || document.getElementById('createContainer') || modal.firstElementChild;
        modal.classList.remove('hidden');
        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    };

    // Cerrar Modal Crear
    window.closeCreateModal = function() {
        const modal = document.getElementById('modalCrearCategoria') || document.getElementById('modalCrear');
        if (!modal) return;
        const container = modal.querySelector('.modal-container') || document.getElementById('createContainer') || modal.firstElementChild;
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        } else {
            modal.classList.add('hidden');
        }
    };

    // Abrir Modal Editar
    window.openEditModal = function(btn) {
        const id = btn.getAttribute('data-id');
        const modal = document.getElementById(`modalEditar-${id}`) || document.getElementById('modalEditarCategoria');
        if (!modal) return;

        const container = modal.querySelector('.modal-container') || modal.querySelector('[id^="modalContainer-"]') || modal.firstElementChild;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    };

    // Cerrar Modal Editar
    window.closeEditModal = function(id) {
        const modal = id ? document.getElementById(`modalEditar-${id}`) : document.getElementById('modalEditarCategoria');
        if (!modal) return;
        const container = modal.querySelector('.modal-container') || modal.querySelector('[id^="modalContainer-"]') || modal.firstElementChild;
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
    };

    // Abrir Modal Eliminar
    window.abrirModalEliminar = function(btn) {
        const id     = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre');
        const modal     = document.getElementById('modalEliminar');
        if (!modal) return;

        const container = document.getElementById('deleteContainer') || modal.querySelector('.modal-container') || modal.firstElementChild;
        const form      = document.getElementById('formEliminar') || modal.querySelector('form');
        const display   = document.getElementById('delete_nombre_display');

        if (display) display.innerText = nombre;
        if (form)    form.action = `/admin/categorias/${id}`;

        modal.classList.remove('hidden');
        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    };

    // Cerrar Modal Eliminar
    window.cerrarModalEliminar = function() {
        const modal = document.getElementById('modalEliminar');
        if (!modal) return;
        const container = document.getElementById('deleteContainer') || modal.querySelector('.modal-container') || modal.firstElementChild;
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        } else {
            modal.classList.add('hidden');
        }
    };
</script>
@endpush