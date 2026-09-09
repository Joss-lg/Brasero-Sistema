@extends('layouts.admin')

@section('title', 'Inventario | Ollintem Pro')

@section('content')
<div class="p-4 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 flex-1 flex flex-col">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 md:gap-6 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: var(--text-color);">Inventario del Restaurante</h1>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-muted);">Control de insumos y materia prima</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-sm" style="color: var(--text-muted);"></i>
                </div>
                <input type="text" id="buscadorInventario" data-teclado="texto" placeholder="Buscar ingrediente..."
                    class="w-full h-12 rounded-2xl pl-11 pr-4 text-xs font-bold outline-none transition-all focus:border-[#b74309] focus:ring-4 focus:ring-[#b74309]/10"
                    style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);">
            </div>

            @if(auth()->user()->tienePermiso('inventario.mostrar'))
                <a href="{{ route('admin.inventario.exportar_pdf_bajo_stock') }}" 
                    class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white px-6 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-rose-600/20 active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf"></i> Reporte Bajo Stock
                </a>
            @endif

            <a href="{{ route('admin.corte.index') }}" 
               class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-emerald-600/20 active:scale-95 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-receipt"></i> Productos Vendidos
            </a>
            
            @if(auth()->user()->tienePermiso('inventario.crear'))
            <button onclick="openModalCrear()" class="w-full sm:w-auto bg-[#b74309] hover:bg-[#8f3207] text-white px-7 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-[#b74309]/20 active:scale-95 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
            @endif
        </div>
    </div>

    <div class="rounded-2xl sm:rounded-[2.5rem] shadow-sm p-4 sm:p-6 lg:p-8 w-full" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        
        <div class="mb-6 flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-[#b74309]/10 flex items-center justify-center text-[#b74309]">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-black uppercase tracking-tight" style="color: var(--text-color);">
                Existencias | <span class="font-bold text-xs sm:text-sm normal-case tracking-normal" style="color: var(--text-muted);">{{ count($insumos ?? []) }} registrados</span>
            </h2>
        </div>

        {{-- VISTA ESCRITORIO --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Código</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Producto / Ingrediente</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Cantidad</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Unidad</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Stock Mínimo</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em]" style="color: var(--text-muted);">Estado</th>
                        <th class="pb-4 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-right" style="color: var(--text-muted);">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaInventario">
                    @forelse($insumos ?? [] as $item)
                    <tr class="fila-articulo transition-colors group hover:bg-[#b74309]/[0.03]" style="border-bottom: 1px solid var(--border-color);">
                        <td class="py-4 px-4 text-xs font-mono font-bold" style="color: var(--text-muted);">
                            {{ $item->codigo ?? 'S/N' }}
                        </td>
                        <td class="py-4 px-4 text-sm font-black nombre-celda" style="color: var(--text-color);">
                            {{ $item->nombre }}
                        </td>
                        <td class="py-4 px-4 text-sm font-black" style="color: var(--text-color);">
                            {{ number_format($item->stock_actual, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);">
                                {{ $item->unidad_medida }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm font-bold" style="color: var(--text-muted);">
                            {{ number_format($item->stock_minimo, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                                $porcentaje = ($item->stock_actual / $minimo) * 100;
                                if($porcentaje >= 150)      { $colorClase = 'bg-emerald-500 text-white'; $textoEstado = 'Óptimo'; }
                                elseif($porcentaje > 100)   { $colorClase = 'bg-[#b74309] text-white'; $textoEstado = 'Bien'; }
                                elseif($porcentaje >= 50)   { $colorClase = 'bg-amber-500 text-white'; $textoEstado = 'Regular'; }
                                else                        { $colorClase = 'bg-rose-500 text-white'; $textoEstado = 'Crítico'; }
                            @endphp
                            <span class="px-3 py-1.5 inline-block {{ $colorClase }} rounded-full text-[10px] font-black shadow-sm uppercase tracking-wider">
                                {{ $textoEstado }} ({{ round($porcentaje) }}%)
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                @if(auth()->user()->tienePermiso('inventario.editar'))
                                <button type="button" title="Ajustar Stock"
                                    onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition-all outline-none">
                                    <i class="fas fa-exchange-alt text-sm"></i>
                                </button>
                                <button type="button" title="Editar Detalles"
                                    onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-[#b74309] bg-[#b74309]/10 hover:bg-[#b74309]/20 transition-all outline-none">
                                    <i class="fas fa-cog text-sm"></i>
                                </button>
                                @endif
                                @if(auth()->user()->tienePermiso('inventario.eliminar'))
                                <button type="button" title="Dar de baja"
                                    onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-rose-600 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 transition-all outline-none">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.inventario.modal-editar', ['item' => $item])
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-sm font-bold" style="color: var(--text-muted);">No hay productos registrados en el inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- VISTA TARJETAS MÓVIL --}}
        <div class="block lg:hidden space-y-4">
            @forelse($insumos ?? [] as $item)
                @php
                    $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                    $porcentaje = ($item->stock_actual / $minimo) * 100;
                    if($porcentaje >= 150)      { $colorClase = 'bg-emerald-500 text-white'; $textoEstado = 'Óptimo'; }
                    elseif($porcentaje > 100)   { $colorClase = 'bg-[#b74309] text-white'; $textoEstado = 'Bien'; }
                    elseif($porcentaje >= 50)   { $colorClase = 'bg-amber-500 text-white'; $textoEstado = 'Regular'; }
                    else                        { $colorClase = 'bg-rose-500 text-white'; $textoEstado = 'Crítico'; }
                @endphp

                <div class="fila-articulo p-4 rounded-2xl space-y-3 transition-all shadow-sm" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="font-mono font-bold" style="color: var(--text-muted);">Cod: {{ $item->codigo ?? 'S/N' }}</span>
                        <span class="px-2.5 py-1 {{ $colorClase }} rounded-full font-black uppercase tracking-wider text-[10px]">
                            {{ $textoEstado }} ({{ round($porcentaje) }}%)
                        </span>
                    </div>

                    <h3 class="text-base font-black nombre-celda leading-tight uppercase tracking-tight" style="color: var(--text-color);">
                        {{ $item->nombre }}
                    </h3>

                    <div class="grid grid-cols-2 gap-2 pt-3 text-xs" style="border-top: 1px solid var(--border-color);">
                        <div>
                            <span class="block text-[9px] uppercase font-black tracking-[0.15em] mb-0.5" style="color: var(--text-muted);">Stock Actual</span>
                            <span class="font-black text-sm" style="color: var(--text-color);">
                                {{ number_format($item->stock_actual, 2) }}
                                <span class="text-[10px] font-black uppercase tracking-wider ml-0.5" style="color: var(--text-muted);">{{ $item->unidad_medida }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-[9px] uppercase font-black tracking-[0.15em] mb-0.5" style="color: var(--text-muted);">Mínimo Req.</span>
                            <span class="font-bold text-sm" style="color: var(--text-muted);">
                                {{ number_format($item->stock_minimo, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-3" style="border-top: 1px solid var(--border-color);">
                        @if(auth()->user()->tienePermiso('inventario.editar'))
                            <button type="button" 
                                onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="flex-1 h-11 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-exchange-alt"></i> Ajustar
                            </button>
                            <button type="button" 
                                onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                class="flex-1 h-11 bg-[#b74309]/10 hover:bg-[#b74309]/20 text-[#b74309] text-[10px] font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-cog"></i> Editar
                            </button>
                        @endif

                        @if(auth()->user()->tienePermiso('inventario.eliminar'))
                            <button type="button" 
                                onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="w-11 h-11 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center transition-colors">
                                <i class="far fa-trash-alt text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>
                
                @if(auth()->user()->tienePermiso('inventario.editar'))
                    @include('admin.inventario.modal-editar', ['item' => $item])
                @endif

            @empty
                <div class="py-8 text-center text-sm font-bold" style="color: var(--text-muted);">No hay productos registrados en el inventario.</div>
            @endforelse
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modales = document.querySelectorAll('#modalCrear, #modalEliminar, #modalMovimiento, [id^="modalEditar-"], [id^="modalMovimiento-"]');
        modales.forEach(modal => { if(modal) document.body.appendChild(modal); });

        const buscador = document.getElementById('buscadorInventario');
        const filas = document.querySelectorAll('.fila-articulo');
        
        function filtrarInventario(term) {
            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre-celda').textContent.toLowerCase();
                fila.style.display = nombre.includes(term) ? '' : 'none';
            });
        }

        if (buscador) {
            buscador.addEventListener('input', function(e) { filtrarInventario(e.target.value.toLowerCase().trim()); });
            buscador.addEventListener('virtualKeyboardInput', function(e) { filtrarInventario(e.target.value.toLowerCase().trim()); });
        }
    });

    function openModalCrear() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (!modal || !container) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCreateModal() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
    }

    function abrirModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            if(container) { container.classList.remove('scale-95', 'opacity-0'); container.classList.add('scale-100', 'opacity-100'); }
        }, 10);
    }

    function cerrarModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if(container) { container.classList.remove('scale-100', 'opacity-100'); container.classList.add('scale-95', 'opacity-0'); }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
    }

    function confirmarEliminacion(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const displayNombre = document.getElementById('delete_nombre_display');
        if (!modal || !container) return;
        if(displayNombre) displayNombre.innerText = nombre;
        if(form) form.action = `/admin/inventario/${id}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => { container.classList.remove('scale-95', 'opacity-0'); container.classList.add('scale-100', 'opacity-100'); }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        if(container) { container.classList.remove('scale-100', 'opacity-100'); container.classList.add('scale-95', 'opacity-0'); }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
    }
</script>

@include('admin.inventario.modal-crear')
@include('admin.inventario.modal-eliminar')
@include('admin.inventario.modal-movimiento')
@include('partials.teclado-virtual')
@endsection