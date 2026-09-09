@extends('layouts.admin')
@section('title', 'Productos | Ollintem Pro')
@section('header-title', 'Gestión de Productos')
@section('header-subtitle', 'Administra el menú y las recetas de los productos')
@section('content')

<div class="p-3 sm:p-6 lg:p-8 xl:p-10 max-w-[1400px] mx-auto w-full space-y-5 sm:space-y-8" style="background-color: var(--bg-color); color: var(--text-color);">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-end gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-3xl font-black tracking-tight" style="color: var(--text-color);">Menú de Productos</h1>
            <p class="text-xs sm:text-sm font-medium mt-1" style="color: var(--text-muted);">Gestiona los productos del restaurante</p>
        </div>
        <button type="button" onclick="abrirModalCrear()" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#b74309] hover:bg-[#8f3207] text-white px-5 py-3 sm:py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm active:scale-[0.98]">
            <i class="fas fa-plus text-[12px]"></i>
            <span>Agregar Producto</span>
        </button>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 min-[420px]:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-5">
        <div class="rounded-[18px] sm:rounded-[20px] p-4 sm:p-6 shadow-sm border flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md" style="background-color: var(--card-color); border-color: rgba(183,67,9,0.4); box-shadow: 0 1px 3px rgba(183,67,9,0.2);">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Total Productos</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#b74309]/10 flex items-center justify-center text-[#b74309] shrink-0">
                    <i class="fas fa-utensils text-xs sm:text-sm"></i>
                </div>
            </div>
            <span class="text-2xl sm:text-4xl font-black tracking-tight" style="color: var(--text-color);" id="stat-total">0</span>
        </div>

        <div class="rounded-[18px] sm:rounded-[20px] p-4 sm:p-6 shadow-sm border flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md" style="background-color: var(--card-color); border-color: rgba(34,197,94,0.4); box-shadow: 0 1px 3px rgba(34,197,94,0.2);">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Disponibles</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500 shrink-0">
                    <i class="fas fa-check text-xs sm:text-sm"></i>
                </div>
            </div>
            <span class="text-2xl sm:text-4xl font-black tracking-tight" style="color: var(--text-color);" id="stat-disponibles">0</span>
        </div>

        <div class="rounded-[18px] sm:rounded-[20px] p-4 sm:p-6 shadow-sm border flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md col-span-1 min-[420px]:col-span-2 md:col-span-1" style="background-color: var(--card-color); border-color: rgba(168,85,247,0.4); box-shadow: 0 1px 3px rgba(168,85,247,0.2);">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest" style="color: var(--text-muted);">Categorías</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 shrink-0">
                    <i class="fas fa-tags text-xs sm:text-sm"></i>
                </div>
            </div>
            <span class="text-2xl sm:text-4xl font-black tracking-tight" style="color: var(--text-color);" id="stat-categorias">0</span>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="relative mb-4">
        <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-sm pointer-events-none" style="color: var(--text-muted);"></i>
        <input type="text" id="buscadorProductosAdmin"
               placeholder="Buscar producto por nombre..."
               autocomplete="off"
               class="w-full pl-11 pr-11 py-3 rounded-[16px] text-sm font-semibold outline-none transition-colors shadow-sm focus:border-[#b74309]"
               style="border: 1px solid var(--border-color); background-color: var(--card-color); color: var(--text-color);">
        <button type="button" id="limpiarBusquedaAdmin"
                class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full transition-colors"
                style="color: var(--text-muted);"
                title="Limpiar búsqueda">
            <i class="fas fa-xmark text-sm"></i>
        </button>
    </div>

    {{-- Contenedor de productos --}}
    <div class="rounded-[18px] sm:rounded-[24px] p-2.5 sm:p-6 shadow-sm min-h-[420px]" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        <div id="categorias-container" class="space-y-5 sm:space-y-6"
             data-permiso-editar="{{ auth()->user()->tienePermiso('Productos', 'editar') ? 'true' : 'false' }}"
             data-permiso-eliminar="{{ auth()->user()->tienePermiso('Productos', 'eliminar') ? 'true' : 'false' }}"
             data-permiso-gestionar="{{ auth()->user()->tienePermiso('Productos', 'mostrar') ? 'true' : 'false' }}">
        </div>
    </div>

</div>

@include('admin.productos.modal-crear')
@include('admin.productos.modal-editar')
@include('admin.productos.modal-eliminar')

<script>
    const RUTA_PRODUCTOS    = "{{ route('admin.productos.api.productos') }}";
    const RUTA_ESTADISTICAS = "{{ route('admin.productos.api.estadisticas') }}";
    const RUTA_STORE        = "{{ route('admin.productos.api.store') }}";
    const RUTA_API_BASE     = "{{ url('/productos/api/') }}/";
 
    let estadoGlobal = { productos: {}, productosMap: {}, editandoId: null };
    let tienePermisoEditar = false, tienePermisoEliminar = false, tienePermisoGestionar = false;
    let categoriasDisponibles = [], insumosDisponibles = [];
 
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('categorias-container');
        if (container) {
            tienePermisoEditar    = container.dataset.permisoEditar    === 'true';
            tienePermisoEliminar  = container.dataset.permisoEliminar  === 'true';
            tienePermisoGestionar = container.dataset.permisoGestionar === 'true';
        }
        categoriasDisponibles = {!! Illuminate\Support\Js::from($categorias->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre])) !!};
        insumosDisponibles    = @json($insumos);
 
        cargarProductos();
        cargarEstadisticas();
        setInterval(cargarEstadisticas, 10000);
    });
 
    function cargarProductos() {
        const scrollY = window.scrollY;
        fetch(RUTA_PRODUCTOS)
            .then(r => r.json())
            .then(data => {
                estadoGlobal.productos    = data;
                estadoGlobal.productosMap = {};
                Object.keys(data).forEach(cat => {
                    data[cat].forEach(p => { estadoGlobal.productosMap[p.id] = p; });
                });
                renderizarProductos();
                requestAnimationFrame(() => window.scrollTo(0, scrollY));
            })
            .catch(e => console.error('Error cargando productos:', e));
    }
 
    function cargarEstadisticas() {
        fetch(RUTA_ESTADISTICAS)
            .then(r => r.json())
            .then(data => {
                const t = document.getElementById('stat-total');
                const d = document.getElementById('stat-disponibles');
                const c = document.getElementById('stat-categorias');
                if (t) t.textContent = data.total;
                if (d) d.textContent = data.disponibles;
                if (c) c.textContent = data.categorias;
            })
            .catch(e => console.error('Error cargando estadísticas:', e));
    }
 
    let filtroProductos = '';

    function normalizarTexto(texto) {
        return (texto || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function renderizarProductos() {
        const container = document.getElementById('categorias-container');
        container.innerHTML = '';
        if (Object.keys(estadoGlobal.productos).length === 0) {
            container.innerHTML = '<p class="text-center py-10 sm:py-12 font-bold text-sm px-4" style="color: var(--text-muted);">No hay productos registrados aún.</p>';
            return;
        }

        const buscado = normalizarTexto(filtroProductos).trim();
        let encontrados = 0;

        Object.keys(estadoGlobal.productos).forEach(catNombre => {
            const coincideCategoria = buscado !== '' && normalizarTexto(catNombre).includes(buscado);
            const productos = buscado === '' || coincideCategoria
                ? estadoGlobal.productos[catNombre]
                : estadoGlobal.productos[catNombre].filter(p => normalizarTexto(p.nombre).includes(buscado));

            if (productos.length === 0) return;
            encontrados += productos.length;
            const gridId = 'grid-' + catNombre.toLowerCase().replace(/[^a-z0-9]/g, '-');    
            const seccion = document.createElement('div');
            seccion.className = 'mb-6 sm:mb-8 rounded-[16px] sm:rounded-[20px] p-2.5 sm:p-4';
            seccion.style.cssText = 'background-color: var(--bg-color); border: 1px solid var(--border-color);';
            seccion.innerHTML = `
                <div class="flex items-center gap-3 sm:gap-4 mb-4 sm:mb-5 pb-3 sm:pb-4 px-1.5 sm:px-2" style="border-bottom: 1px solid var(--border-color);">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-xl flex items-center justify-center shadow-sm" style="background-color: var(--card-color); border: 1px solid var(--border-color); color: var(--text-color);">
                        <i class="${obtenerIconoCategoria(catNombre)} text-xs sm:text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-lg font-black tracking-tight uppercase truncate" style="color: var(--text-color);">${catNombre}</h2>
                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest mt-0.5" style="color: var(--text-muted);">${productos.length} Producto${productos.length !== 1 ? 's' : ''}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 min-[480px]:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4" id="${gridId}"></div>
            `;
            container.appendChild(seccion);
            const grid = seccion.querySelector(`#${gridId}`);
            productos.forEach(p => grid.appendChild(crearCardProducto(p)));
        });

        if (encontrados === 0) {
            container.innerHTML = `
                <div class="text-center py-12 px-4">
                    <i class="fas fa-magnifying-glass text-4xl opacity-30 mb-3" style="color: var(--text-muted);"></i>
                    <p class="font-bold" style="color: var(--text-color);">Sin resultados para "${filtroProductos}"</p>
                    <p class="text-sm mt-1" style="color: var(--text-muted);">Prueba con otras letras.</p>
                </div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const buscador = document.getElementById('buscadorProductosAdmin');
        const btnLimpiar = document.getElementById('limpiarBusquedaAdmin');
        if (!buscador) return;

        const buscar = () => {
            filtroProductos = buscador.value || '';
            if (btnLimpiar) btnLimpiar.classList.toggle('hidden', filtroProductos === '');
            renderizarProductos();
        };

        buscador.addEventListener('input', buscar);

        let vigilante = null;
        buscador.addEventListener('focus', () => {
            let previo = buscador.value;
            vigilante = setInterval(() => {
                if (buscador.value !== previo) { previo = buscador.value; buscar(); }
            }, 250);
        });
        buscador.addEventListener('blur', () => clearInterval(vigilante));

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => {
                buscador.value = '';
                buscar();
                buscador.focus();
            });
        }
    });
 
    function crearCardProducto(producto) {
        const card = document.createElement('div');
        card.className = 'rounded-[14px] sm:rounded-[16px] p-3.5 sm:p-5 shadow-sm group flex flex-col relative';
        card.style.cssText = 'background-color: var(--card-color); border: 1px solid var(--border-color);';
 
        const mods = producto.modificadores?.length
            ? `<p class="text-[10px] mt-1.5 truncate" style="color: var(--text-muted);"><i class="fas fa-list-ul mr-1 opacity-70"></i> ${producto.modificadores.map(m => m.nombre).join(', ')}</p>`
            : '';

        const esPorPeso = Boolean(producto.se_vende_por_peso);
        const tieneVariantes = Boolean(producto.tiene_variantes);

        const badgePorPeso = esPorPeso
            ? `<span class="text-[8px] font-black text-orange-500 bg-orange-500/10 border border-orange-500/20 px-1.5 py-0.5 rounded-md uppercase tracking-widest inline-flex items-center gap-1 mt-1.5"><i class="fas fa-weight-hanging"></i> Por peso</span>`
            : '';

        const badgeVariantes = tieneVariantes
            ? `<span class="text-[8px] font-black text-blue-500 bg-blue-500/10 border border-blue-500/20 px-1.5 py-0.5 rounded-md uppercase tracking-widest inline-flex items-center gap-1 mt-1.5"><i class="fas fa-layer-group"></i> Variantes</span>`
            : '';

        let precioMostrado = `$${parseFloat(producto.precio || 0).toFixed(2)}`;
        if (esPorPeso) {
            precioMostrado = `$${parseFloat(producto.precio_por_100g ?? 0).toFixed(2)} <span class="text-[10px] sm:text-[11px] font-bold" style="color: var(--text-muted);">/100g</span>`;
        } else if (tieneVariantes && producto.variantes && producto.variantes.length > 0) {
            const precios = producto.variantes.map(v => parseFloat(v.precio));
            const min = Math.min(...precios);
            const max = Math.max(...precios);
            precioMostrado = min === max ? `$${min.toFixed(2)}` : `$${min.toFixed(2)} - $${max.toFixed(2)}`;
        }
 
        const botonesHTML = [
            tienePermisoEditar   ? `<button class="w-9 h-9 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition active:scale-95 hover:text-[#b74309] hover:border-[#b74309]" style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-muted);" onclick="editarProducto(${producto.id})" title="Editar"><i class="fas fa-pen text-[11px]"></i></button>` : '',
            tienePermisoEliminar ? `<button class="w-9 h-9 sm:w-8 sm:h-8 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition active:scale-95" onclick="eliminarProducto(${producto.id})" title="Eliminar"><i class="fas fa-trash text-[11px]"></i></button>` : '',
        ].join('');
 
        const toggleHTML = tienePermisoEditar
            ? `<button class="w-9 h-5 rounded-full transition-colors duration-200 relative shrink-0 ${producto.esta_disponible ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'}" onclick="toggleDisponibilidad(this, ${producto.id})" title="Cambiar disponibilidad"><div class="w-4 h-4 bg-white rounded-full shadow-sm absolute top-0.5 transition-transform duration-200 ${producto.esta_disponible ? 'translate-x-[18px]' : 'translate-x-0.5'}"></div></button>`
            : `<div class="w-9 h-5 rounded-full relative shrink-0 ${producto.esta_disponible ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'} opacity-50 cursor-not-allowed" title="Sin permisos"><div class="w-4 h-4 bg-white rounded-full shadow-sm absolute top-0.5 ${producto.esta_disponible ? 'translate-x-[18px]' : 'translate-x-0.5'}"></div></div>`;
 
        card.innerHTML = `
            <div class="flex justify-between items-start mb-3 sm:mb-4 gap-2">
                <div class="flex items-start gap-2.5 sm:gap-3 overflow-hidden min-w-0">
                    <div class="overflow-hidden min-w-0">
                        <h3 class="text-sm sm:text-[15px] font-bold tracking-tight truncate" style="color: var(--text-color);">${producto.nombre}</h3>
                        <p class="text-[11px] sm:text-[12px] mt-1 line-clamp-2" style="color: var(--text-muted);">${producto.descripcion ?? 'Sin descripción'}</p>
                        ${mods}
                        <div class="flex flex-wrap gap-1">
                            ${badgePorPeso}
                            ${badgeVariantes}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity shrink-0">${botonesHTML}</div>
            </div>
            <div class="flex flex-wrap justify-between items-center gap-2 mt-auto pt-3 sm:pt-4" style="border-top: 1px solid var(--border-color);">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    ${toggleHTML}
                    <span class="text-[9px] font-bold uppercase tracking-widest texto-estado" style="color: var(--text-muted);">${producto.esta_disponible ? 'Disponible' : 'Agotado'}</span>
                </div>
                <span class="text-sm sm:text-[16px] font-black tracking-tight" style="color: var(--text-color);">${precioMostrado}</span>
            </div>
        `;
        return card;
    }
 
    function obtenerIconoCategoria(nombre) {
        const n = nombre.toLowerCase();
        if (n.includes('pizza'))                               return 'fas fa-pizza-slice';
        if (n.includes('pasta'))                               return 'fas fa-utensils';
        if (n.includes('bebida') || n.includes('cocteleria'))  return 'fas fa-glass-water';
        if (n.includes('postre'))                              return 'fas fa-cake-slice';
        if (n.includes('ensalada') || n.includes('verdura'))   return 'fas fa-leaf';
        if (n.includes('carne') || n.includes('parrillada'))   return 'fas fa-drumstick-bite';
        if (n.includes('marisco') || n.includes('pescado'))    return 'fas fa-fish';
        if (n.includes('sopa'))                                return 'fas fa-bowl-food';
        if (n.includes('abarrote'))                            return 'fas fa-box-open';
        return 'fas fa-concierge-bell';
    }
 
    function eliminarProducto(id) {
        if (!tienePermisoEliminar) { mostrarNotificacion('Sin permisos para eliminar', 'error'); return; }
        const producto = estadoGlobal.productosMap[id];
        if (producto) abrirModalEliminar(id, producto.nombre);
    }
 
    function toggleDisponibilidad(btn, id) {
        if (!tienePermisoEditar) { mostrarNotificacion('Sin autorización', 'error'); return; }
        const circulo     = btn.querySelector('div');
        const estaActivo = btn.classList.contains('bg-green-500');
        const textoEstado = btn.nextElementSibling;
        _setToggleEstado(btn, circulo, textoEstado, !estaActivo);
        fetch(RUTA_API_BASE + id + '/toggle-disponibilidad', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        })
        .then(() => cargarEstadisticas())
        .catch(() => {
            _setToggleEstado(btn, circulo, textoEstado, estaActivo);
            mostrarNotificacion('Error al cambiar disponibilidad', 'error');
        });
    }
 
    function limpiarIngredientesContainer(tipo) {
        document.getElementById(`ingredientes-container-${tipo}`).innerHTML = '';
    }
 
    function agregarIngrediente(tipo = 'crear', ingrediente = {}) {
        const container = document.getElementById(`ingredientes-container-${tipo}`);
        if (container) container.appendChild(crearFilaIngrediente(ingrediente));
    }
 
    function eliminarIngredienteRow(button) { button.closest('.ingrediente-row').remove(); }
 
    function crearFilaIngrediente(ingrediente = {}) {
        const row = document.createElement('div');
        row.className = 'flex flex-col md:grid md:grid-cols-12 gap-3 items-stretch md:items-end p-4 md:p-0 rounded-2xl md:border-0 ingrediente-row relative mb-3 md:mb-0';
        row.style.cssText = 'background-color: var(--bg-color); border: 1px solid var(--border-color);';
        
        const insumoValue   = ingrediente.insumo_id    ?? ingrediente.id ?? '';
        const cantidadValue = ingrediente.cantidad     ?? ingrediente.pivot?.cantidad_usada ?? '';
        const unidadValue   = ingrediente.unidad_medida ?? '';

        const options = insumosDisponibles.map(ins => {
            const sel = ins.id == insumoValue ? 'selected' : '';
            const stockSeguro = (ins.stock_actual !== undefined && ins.stock_actual !== null) ? ins.stock_actual : 0;
            return `<option value="${ins.id}" data-unidad="${ins.unidad_medida ?? ''}" data-stock="${stockSeguro}" ${sel}>${ins.nombre}</option>`;
        }).join('');

        row.innerHTML = `
            <div class="md:col-span-6">
                <label class="text-[10px] font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Ingrediente</label>
                <select name="insumos[]" class="w-full rounded-2xl p-4 mt-1 outline-none transition focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);" onchange="sincronizarInsumo(this)" required>
                    <option value="">Seleccionar...</option>${options}
                </select>
            </div>
            <div class="grid grid-cols-12 gap-2 items-end md:col-span-6">
                <div class="col-span-6">
                    <label class="text-[10px] font-black uppercase tracking-widest ml-1" style="color: var(--text-muted);">Cantidad</label>
                    <input type="number" name="cantidades[]" step="0.001" min="0.001" value="${cantidadValue}" class="w-full rounded-2xl p-4 mt-1 outline-none transition focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);" placeholder="0.000" required>
                </div>
                <div class="col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-widest ml-1 text-center block" style="color: var(--text-muted);">Uni</label>
                    <input type="text" value="${unidadValue}" class="w-full rounded-2xl p-4 mt-1 text-center font-medium" style="background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color);" disabled>
                </div>
                <div class="col-span-3 flex flex-col items-center justify-end h-full">
                    <button type="button" class="w-full md:w-10 h-12 rounded-xl bg-red-900/10 border border-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center active:scale-95" onclick="eliminarIngredienteRow(this)">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="text-[10px] font-bold mt-1 px-1 item-stock-label" style="color: var(--text-muted);"></div>
        `;

        const select = row.querySelector('select[name="insumos[]"]');
        if (insumoValue) { select.value = insumoValue; sincronizarInsumo(select); }
        return row;
    }
 
    function sincronizarInsumo(select) {
        const opt = select.options[select.selectedIndex];
        const row = select.closest('.ingrediente-row');
        if (!row) return;

        const unidadInput = row.querySelector('input[disabled]');
        const stockLabel  = row.querySelector('.item-stock-label');

        if (opt && opt.value) {
            if (unidadInput) unidadInput.value = opt.dataset.unidad ?? '';
            const stockValor = opt.dataset.stock;
            if (stockValor !== undefined && stockValor !== 'undefined' && stockValor !== '') {
                stockLabel.innerHTML = `<span class="bg-[#b74309]/10 text-[#b74309] px-2 py-0.5 rounded-md inline-block">Stock: ${stockValor}</span>`;
            } else {
                stockLabel.innerHTML = '';
            }
        } else {
            if (unidadInput) unidadInput.value = '';
            if (stockLabel) stockLabel.innerHTML = '';
        }
    }
 
    function llenarIngredientesEdicion(producto) {
        limpiarIngredientesContainer('editar');
        if (producto.insumos?.length) {
            producto.insumos.forEach(ins => agregarIngrediente('editar', {
                insumo_id:     ins.id,
                cantidad:      ins.pivot?.cantidad_usada ?? '',
                unidad_medida: ins.unidad_medida         ?? '',
                stock_actual:  ins.stock_actual          ?? ''
            }));
        } else {
            agregarIngrediente('editar');
        }
    }
 
    function obtenerCategoriaIdPorNombre(nombre) {
        if (!nombre) return null;
        return categoriasDisponibles.find(c => c.nombre.toLowerCase() === nombre.toLowerCase())?.id ?? null;
    }
 
    function ejecutarPeticion(url, data, boton, textoBoton, cerrarModalFn) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept':       'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(r => { if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Error'); }); return r.json(); })
        .then(res => { cerrarModalFn(); cargarProductos(); cargarEstadisticas(); mostrarNotificacion(res.message || 'Operación exitosa', 'success'); })
        .catch(e  => mostrarNotificacion(e.message || 'Error en el servidor', 'error'))
        .finally(() => { boton.textContent = textoBoton; boton.disabled = false; });
    }
 
    function _abrirModal(modalId, panelId) {
        const modal = document.getElementById(modalId);
        const panel = document.getElementById(panelId);
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.add('opacity-100'); panel.classList.add('opacity-100', 'translate-y-0'); }, 10);
    }
 
    function _cerrarModal(modalId, panelId) {
        const modal = document.getElementById(modalId);
        const panel = document.getElementById(panelId);
        modal.classList.remove('opacity-100');
        panel.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
 
    function _setToggleEstado(btn, circulo, texto, activo) {
        btn.classList.toggle('bg-green-500', activo);
        btn.classList.toggle('bg-gray-300', !activo);
        btn.classList.toggle('dark:bg-zinc-700', !activo);
        circulo.classList.toggle('translate-x-[18px]', activo);
        circulo.classList.toggle('translate-x-0.5', !activo);
        if (texto) texto.textContent = activo ? 'DISPONIBLE' : 'AGOTADO';
    }
 
    function _serializarFormulario(formId) {
        const data = {};
        new FormData(document.getElementById(formId)).forEach((value, key) => {
            if (key.endsWith('[]')) {
                const name = key.slice(0, -2);
                if (!data[name]) data[name] = [];
                data[name].push(value);
            } else { data[key] = value; }
        });
        return data;
    }

    let contadorToast = 0;

    function mostrarNotificacion(mensaje, tipo) {
        contadorToast++;
        const id = `toast-ajax-${contadorToast}`;
        const esExito = tipo === 'success';

        let contenedor = document.getElementById('toast-ajax-container');
        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'toast-ajax-container';
            contenedor.className = 'fixed top-4 left-4 right-4 sm:left-auto sm:top-6 sm:right-6 flex flex-col gap-3 sm:gap-4 items-stretch sm:items-end pointer-events-none';
            contenedor.style.zIndex = '999999';
            document.body.appendChild(contenedor);
        }

        const colorBarra   = esExito ? 'from-emerald-400 to-cyan-400' : 'from-rose-400 to-red-500';
        const colorIcono   = esExito ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)]' : 'border-rose-500/30 bg-rose-500/10 text-rose-500 dark:text-rose-400 shadow-[0_0_15px_rgba(244,63,94,0.15)]';
        const colorTitulo  = esExito ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
        const icono        = esExito ? 'fa-check' : 'fa-exclamation';
        const titulo       = esExito ? 'Operación Exitosa' : 'Atención';

        const toast = document.createElement('div');
        toast.id = id;
        toast.className = 'pointer-events-auto relative overflow-hidden bg-white dark:bg-[#0f1015] border border-gray-100 dark:border-white/5 rounded-2xl shadow-2xl p-4 flex gap-3.5 items-start w-full sm:w-[320px] transition-all duration-300 transform translate-x-0 opacity-100';
        toast.innerHTML = `
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${colorBarra}"></div>
            <div class="flex items-center justify-center w-8 h-8 rounded-full border ${colorIcono} flex-shrink-0 mt-1">
                <i class="fas ${icono} text-[11px]"></i>
            </div>
            <div class="flex-1 pr-3 min-w-0">
                <p class="text-[9px] font-black uppercase tracking-[0.2em] ${colorTitulo} mb-1">${titulo}</p>
                <p class="text-[13px] font-bold text-gray-900 dark:text-white leading-tight break-words">${mensaje}</p>
            </div>
            <button onclick="cerrarToastAjax('${id}')" class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors outline-none">
                <i class="fas fa-times text-[10px]"></i>
            </button>
            <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r ${colorBarra} animate-shrink"></div>
        `;

        contenedor.appendChild(toast);
        setTimeout(() => cerrarToastAjax(id), 3000);
    }

    function cerrarToastAjax(id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }
    }
</script>
@endsection