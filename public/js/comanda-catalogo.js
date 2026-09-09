/**
 * comanda-catalogo.js
 * Renderiza el catálogo de productos (categorías + tarjetas) del panel
 * derecho. Depende de categoriasDB / productosDB (declaradas en
 * comanda-core.js) y expone la interacción con variantes y tickets.
 */
(function () {
    function renderizarMenu() {
        const menuCat = document.getElementById('menuCategorias');
        const gridProd = document.getElementById('gridProductos');

        if (!menuCat || !gridProd) {
            return; 
        }

        menuCat.innerHTML = `<button type="button" onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[#b74309] text-white text-[11px] font-bold tracking-wide shadow-sm hover:bg-[#8f3207] transition-all outline-none border border-transparent">Todos</button>`;

        if (typeof categoriasDB !== 'undefined' && categoriasDB.length > 0) {
            categoriasDB.forEach(cat => {
                menuCat.innerHTML += `<button type="button" onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[#b74309] hover:border-[#b74309]/50 text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none">${cat.nombre}</button>`;
            });
        }

        gridProd.innerHTML = '';

        const listaProductos = (typeof productosDB !== 'undefined' && productosDB.length > 0)
            ? productosDB
            : ((window.ComandaConfig && window.ComandaConfig.productos) || []);

        const productosVisibles = listaProductos.filter(prod => prod.esta_disponible == 1 || prod.esta_disponible === true);

        if (productosVisibles.length > 0) {
            productosVisibles.forEach(prod => {
                const catNombre = prod.categoria ? prod.categoria.nombre : '';
                const tieneVariantes = Boolean(prod.tiene_variantes || (prod.variantes && prod.variantes.length > 0));
                const sePorPeso = Boolean(prod.se_vende_por_peso);
                const precioPor100g = parseFloat(prod.precio_por_100g) || 0;

                let precioMostrar = parseFloat(prod.precio) || 0;
                let etiquetaPrecio = `$${precioMostrar.toFixed(2)}`;

                if (sePorPeso) {
                    etiquetaPrecio = `$${precioPor100g.toFixed(2)} <span class="text-[11px] font-semibold text-slate-500">/100g</span>`;
                } else if (tieneVariantes && prod.variantes && prod.variantes.length > 0) {
                    const precios = prod.variantes.map(v => parseFloat(v.precio) || 0);
                    const precioMin = Math.min(...precios);
                    etiquetaPrecio = `<span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Desde</span>$${precioMin.toFixed(2)}`;
                }

                const badgeVariante = tieneVariantes
                    ? `<span class="inline-flex items-center gap-1 text-[9px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md uppercase tracking-wider mb-2">
                           <i class="fas fa-layer-group text-[8px]"></i> Elige proteína
                       </span>`
                    : '';

                const iconoAccion = tieneVariantes ? 'fa-list-ul' : 'fa-plus';

                gridProd.innerHTML += `
                    <button type="button"
                         data-categoria-item="${catNombre}" 
                         data-nombre-item="${(prod.nombre || '').toLowerCase()}" 
                         onclick="window.seleccionarItemCatalogo(${prod.id}); event.stopPropagation();"
                         class="producto-card group relative flex flex-col justify-between text-left rounded-[20px] border border-orange-200/70 bg-white p-4 shadow-sm min-h-[130px] hover:border-[#b74309] hover:shadow-md hover:-translate-y-0.5 active:scale-[0.97] active:translate-y-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#b74309]/50 transition-all duration-150">

                        <div>
                            <h3 class="text-[14px] sm:text-[15px] font-black text-slate-900 leading-tight uppercase mb-1 pr-2">
                                ${prod.nombre}
                            </h3>
                            ${badgeVariante}
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 w-full">
                            <p class="text-[16px] sm:text-[18px] font-black text-slate-900 leading-none tracking-tight">
                                ${etiquetaPrecio}
                            </p>

                            <span class="flex-shrink-0 w-9 h-9 rounded-full bg-[#b74309] text-white flex items-center justify-center text-sm font-bold shadow-sm group-hover:bg-[#8f3207] group-active:scale-90 transition-all duration-150">
                                <i class="fas ${iconoAccion}"></i>
                            </span>
                        </div>
                    </button>
                `;
            });
        } else {
            gridProd.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center text-[var(--text-muted)] mt-20">
                    <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                    <p class="text-xs font-medium">Catálogo vacío</p>
                </div>`;
        }
    }

    // Manejador centralizado para agregar o abrir variantes sin colisión de comillas
    window.seleccionarItemCatalogo = function (productoId) {
        const listaProductos = (typeof productosDB !== 'undefined' && productosDB.length > 0)
            ? productosDB
            : ((window.ComandaConfig && window.ComandaConfig.productos) || []);

        const prod = listaProductos.find(p => p.id === productoId);
        if (!prod) return;

        const tieneVariantes = Boolean(prod.tiene_variantes || (prod.variantes && prod.variantes.length > 0));

        if (tieneVariantes && prod.variantes && prod.variantes.length > 0) {
            if (typeof window.abrirModalVariante === 'function') {
                window.abrirModalVariante(prod.id, prod.nombre, prod.variantes);
            }
        } else {
            const catNombre = prod.categoria ? prod.categoria.nombre : '';
            const precioNum = parseFloat(prod.precio) || 0;
            const sePorPeso = Boolean(prod.se_vende_por_peso);
            const precioPor100g = parseFloat(prod.precio_por_100g) || 0;
            const mods = prod.modificadores || [];

            if (typeof window.agregarAlTicket === 'function') {
                window.agregarAlTicket(prod.id, prod.nombre, precioNum, catNombre, mods, sePorPeso, precioPor100g);
            }
        }
    };

    document.addEventListener('DOMContentLoaded', renderizarMenu);

    let categoriaActiva = 'Todos';
    let textoBuscado = '';

    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function aplicarFiltrosCatalogo() {
        const buscado = normalizar(textoBuscado).trim();
        let visibles = 0;

        document.querySelectorAll('.producto-card').forEach(card => {
            const coincideCategoria = categoriaActiva === 'Todos'
                || card.getAttribute('data-categoria-item') === categoriaActiva;

            const nombre = normalizar(card.getAttribute('data-nombre-item'));
            const coincideTexto = buscado === '' || nombre.includes(buscado);

            const mostrar = coincideCategoria && coincideTexto;
            card.style.display = mostrar ? 'flex' : 'none';
            if (mostrar) visibles++;
        });

        const aviso = document.getElementById('catalogoSinResultados');
        if (aviso) aviso.classList.toggle('hidden', visibles > 0);
    }

    window.filtrarCategoria = function (nombreCat, btn) {
        if (!btn) return;

        // Reset a estilo inactivo con hover naranja
        document.querySelectorAll('.cat-btn').forEach(el => {
            el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[#b74309] hover:border-[#b74309]/50 text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none";
        });

        // Estilo activo para el botón presionado
        btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[#b74309] text-white text-[11px] font-bold tracking-wide shadow-sm hover:bg-[#8f3207] transition-all outline-none border border-transparent";

        categoriaActiva = nombreCat;
        aplicarFiltrosCatalogo();
    };
    
    // --- BUSCADOR ---
    document.addEventListener('DOMContentLoaded', () => {
        const buscador = document.getElementById('buscadorProductos');
        const btnLimpiar = document.getElementById('limpiarBusquedaProductos');
        if (!buscador) return;

        const buscar = () => {
            textoBuscado = buscador.value || '';
            if (btnLimpiar) btnLimpiar.classList.toggle('hidden', textoBuscado === '');
            aplicarFiltrosCatalogo();
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
})();