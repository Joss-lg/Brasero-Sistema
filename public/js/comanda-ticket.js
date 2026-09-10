/**
 * comanda-ticket.js
 * Manejo del ticket: agregar/quitar/editar líneas, cantidades y totales.
 * Usa el estado compartido de comanda-core.js (ticketSubtotal, itemActivo,
 * contadorItems, tiempoGlobal, gramajePendiente, descuentoPorcentaje,
 * promocion2x1Activa, comboActivo, etc.) y expone funciones que otros
 * módulos también necesitan:
 *   - window.calcularDescuento2x1Monto / window.calcularDescuentoComboMonto
 *     (usadas también por comanda-core.js y comanda-promociones.js)
 *   - window._insertarItemEnTicket (usada por comanda-gramaje.js)
 *   - window.actualizarTotales (usada por comanda-gramaje.js,
 *     comanda-promociones.js y comanda-capitan-traspaso.js)
 */
(function () {
    const listaTicket = document.getElementById('listaTicket');
    const estadoVacio = document.getElementById('estadoVacio');
    const barraModificadores = document.getElementById('barraModificadores');
    const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');

    window.calcularDescuento2x1Monto = function () {
        if (!promocion2x1Activa) return 0;
        let monto = 0;
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            const cantidad = parseInt(item.dataset.cantidad, 10) || 0;
            const precio = parseFloat(item.dataset.precio) || 0;
            const pares = Math.floor(cantidad / 2);
            monto += pares * precio;
        });
        return monto;
    };

    window.calcularDescuentoComboMonto = function () {
        if (!comboActivo || comboProductoIds.length === 0) return 0;
        const idsEnTicket = new Set();
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            idsEnTicket.add(parseInt(item.dataset.productoId, 10));
        });
        const completo = comboProductoIds.every(id => idsEnTicket.has(id));
        return completo ? comboMonto : 0;
    };

    // Punto de entrada regular (sin variantes)
    window.agregarAlTicket = function (id, nombre, precio, categoria, arrayModificadores = [], sePorPeso = false, precioPor100g = 0) {
        cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));

        if (sePorPeso) {
            abrirModalGramajeNuevo(id, nombre, categoria, arrayModificadores, precioPor100g);
            return;
        }

        _insertarItemEnTicket({
            id: parseInt(id, 10),
            varianteId: null,
            nombre,
            precioUnitario: parseFloat(precio),
            categoria,
            arrayModificadores,
            sePorPeso: false,
            precioPor100g: 0,
            gramaje: gramajePendiente
        });

        if (gramajePendiente) { 
            gramajePendiente = null; 
            const indGramaje = document.getElementById('indicador-gramaje-pendiente');
            if (indGramaje) indGramaje.classList.add('hidden'); 
        }
    };

    // Punto de entrada para productos con variantes (proteínas/tamaños/sabores)
    window.agregarProductoConVariante = function (productoId, variante) {
        cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));

        // 1. Obtener el nombre real del producto desde la base de datos cargada en JS
        let nombreBase = '';
        const listaProductos = (typeof productosDB !== 'undefined' && productosDB.length > 0)
            ? productosDB
            : ((window.ComandaConfig && window.ComandaConfig.productos) || []);

        const prodEncontrado = listaProductos.find(p => p.id === parseInt(productoId, 10));
        if (prodEncontrado && prodEncontrado.nombre) {
            nombreBase = prodEncontrado.nombre;
        } else {
            const btnElement = document.querySelector(`[data-producto-id="${productoId}"]`);
            nombreBase = btnElement ? btnElement.getAttribute('data-producto-nombre') : 'PRODUCTO';
        }

        // 2. Formato limpio con barra: AGUA DE FRUTA DE TEMPORADA / JARRA
        const nombreCompleto = `${nombreBase.trim()} / ${variante.nombre.trim()}`;

        _insertarItemEnTicket({
            id: parseInt(productoId, 10),
            varianteId: parseInt(variante.id, 10),
            nombre: nombreCompleto,
            precioUnitario: parseFloat(variante.precio),
            categoria: '',
            arrayModificadores: [],
            sePorPeso: false,
            precioPor100g: 0,
            gramaje: null
        });
    };

    // Inserta o agrupa una línea en el ticket
    window._insertarItemEnTicket = function ({ id, varianteId = null, nombre, precioUnitario, categoria, arrayModificadores = [], sePorPeso = false, precioPor100g = 0, gramaje = null }) {
        estadoVacio.classList.add('hidden');

        const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
        const gramajeKey = gramaje ? gramaje.toString() : 'sin-gramaje';
        const varKey = varianteId ? String(varianteId) : 'sin-variante';

        // Búsqueda de coincidencia incluyendo la variante
        const existingItem = Array.from(listaTicket.querySelectorAll('.ticket-item')).find(item => {
            return parseInt(item.dataset.productoId, 10) === id
                && (item.dataset.varianteId || 'sin-variante') === varKey
                && item.dataset.modificadores === modsString
                && item.dataset.gramaje === gramajeKey
                && item.dataset.tiempo === tiempoGlobal;
        });

        if (existingItem) {
            const cantidadSpan = existingItem.querySelector('.cantidad-platillo');
            const cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
            cantidadSpan.innerText = cantidad;
            existingItem.dataset.cantidad = cantidad;

            const precioSpan = existingItem.querySelector('.precio-platillo');
            precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            existingItem.dataset.precio = precioUnitario;

            ticketSubtotal += precioUnitario;
            actualizarTotales();
            seleccionarItem(existingItem.id);
            return existingItem.id;
        }

        contadorItems++;
        const itemId = 'ticket-item-' + contadorItems;

        const etiquetaGramaje = gramaje ? `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${gramaje}g</span>` : '';

        let etiquetaTiempo = '';
        if (tiempoGlobal !== 'sin-tiempo') {
            const t = tiempoGlobal === 'primer-tiempo' ? '1T' : (tiempoGlobal === 'segundo-tiempo' ? '2T' : '3T');
            etiquetaTiempo = `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${t}</span>`;
        }

        const etiquetaPorPeso = sePorPeso
            ? `<span class="text-[7px] text-orange-500 font-bold bg-orange-500/10 border border-orange-500/20 px-1.5 py-0.5 rounded-md"><i class="fas fa-weight-hanging"></i> Por peso</span>`
            : '';

        const itemHTML = `
            <div id="${itemId}" 
                 data-producto-id="${id}" 
                 data-variante-id="${varianteId ? varianteId : ''}" 
                 data-cantidad="1" 
                 data-precio="${precioUnitario}" 
                 data-modificadores="${modsString}" 
                 data-gramaje="${gramajeKey}" 
                 data-tiempo="${tiempoGlobal}" 
                 data-se-por-peso="${sePorPeso ? '1' : '0'}" 
                 data-precio100g="${precioPor100g}" 
                 class="ticket-item animate-item relative w-full rounded-[18px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm p-4 flex flex-col gap-3 cursor-pointer transition-all duration-300 outline-none" 
                 onclick="seleccionarItem('${itemId}')">

                <div class="flex justify-between items-start gap-2">
                    <div class="flex-1">
                        <h3 class="text-[12px] font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</h3>
                        <div class="flex flex-wrap gap-1.5 mt-1.5 empty:hidden">
                            <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                            ${etiquetaTiempo}
                            ${etiquetaPorPeso}
                        </div>
                    </div>
                    <span class="text-[13px] font-black text-[var(--text-main)] tracking-tight precio-platillo">$${precioUnitario.toFixed(2)}</span>
                </div>

                <div class="modificadores-lista w-full text-[10px] text-orange-500 font-medium leading-relaxed empty:hidden"></div>

                <div class="flex items-center justify-between pt-3 mt-1 border-t border-[var(--border-color)]">
                    <div class="flex items-center bg-[var(--input-bg)] rounded-[10px] p-0.5 border border-[var(--border-color)] shadow-inner" onclick="event.stopPropagation();">
                        <button type="button" onclick="decrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                            <i class="fas fa-minus text-[10px]"></i>
                        </button>
                        <span class="cantidad-platillo w-8 text-center text-[12px] font-bold text-[var(--text-main)]">1</span>
                        <button type="button" onclick="incrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                            <i class="fas fa-plus text-[10px]"></i>
                        </button>
                    </div>

                    <button type="button" onclick="eliminarItemFila(this); event.stopPropagation();" class="hidden btn-control-eliminar w-8 h-8 rounded-[8px] text-red-500 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center outline-none">
                        <i class="fas fa-trash-alt text-[10px]"></i>
                    </button>
                </div>
            </div>
        `;

        listaTicket.insertAdjacentHTML('beforeend', itemHTML);
        ticketSubtotal += precioUnitario;
        actualizarTotales();
        seleccionarItem(itemId);
        listaTicket.parentElement.scrollTop = listaTicket.parentElement.scrollHeight;
        return itemId;
    };

    window.seleccionarItem = function (id) {
        deseleccionarTicket();
        itemActivo = document.getElementById(id);
        if (itemActivo) {
            itemActivo.classList.add('bg-[#b74309]/5', 'border-[#b74309]/40');
            itemActivo.classList.remove('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
            itemActivo.querySelector('.btn-control-eliminar').classList.remove('hidden');
            itemActivo.querySelector('.btn-control-eliminar').classList.add('flex');

            const modsString = itemActivo.getAttribute('data-modificadores');
            const modificadoresParaPintar = JSON.parse(modsString || '[]');
            contenedorBotonesModificadores.innerHTML = '';

            const hint = document.getElementById('barraModificadores-hint');
            if (modificadoresParaPintar.length > 0) {
                modificadoresParaPintar.forEach(mod => {
                    const nombreMod = mod.nombre || mod.descripcion || mod;
                    contenedorBotonesModificadores.insertAdjacentHTML('beforeend', `<button type="button" onclick="agregarModificadorFijo('${nombreMod}')" class="px-5 py-2 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold hover:border-[#b74309] transition-all shadow-sm">${nombreMod}</button>`);
                });
                if (hint) hint.textContent = 'Selección activa';
            } else {
                if (hint) hint.textContent = 'Sin modificadores';
            }
        }
    };

    window.deseleccionarTicket = function () {
        document.querySelectorAll('.ticket-item').forEach(el => {
            el.classList.remove('bg-[#b74309]/5', 'border-[#b74309]/40');
            el.classList.add('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
            if (el.querySelector('.btn-control-eliminar')) {
                el.querySelector('.btn-control-eliminar').classList.add('hidden');
                el.querySelector('.btn-control-eliminar').classList.remove('flex');
            }
        });
        itemActivo = null;
        contenedorBotonesModificadores.innerHTML = '';
        const hint = document.getElementById('barraModificadores-hint');
        if (hint) hint.textContent = 'Toca un producto para modificar';
    };

    window.agregarModificadorFijo = function (texto) {
        if (itemActivo) {
            const list = itemActivo.querySelector('.modificadores-lista');
            const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
            list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${texto}</span></span>`);
        }
    };

    window.guardarNota = function () {
        const nota = document.getElementById('notaTextarea').value.trim();
        if (!itemActivo) { cerrarModal('modalNota'); return; }
        if (nota.length === 0) { mostrarError('Nota vacía.'); return; }

        const list = itemActivo.querySelector('.modificadores-lista');
        const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
        list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${nota}</span></span>`);

        itemActivo.dataset.nota = nota; 
        notaGeneral = nota; 
        cerrarModal('modalNota');
    };

    window.incrementarCantidad = function (id) {
        const item = document.getElementById(id); if (!item) return;
        const cantidadSpan = item.querySelector('.cantidad-platillo');
        const precioUnitario = parseFloat(item.dataset.precio) || 0;
        let cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
        cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
        item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
        ticketSubtotal += precioUnitario; actualizarTotales();
    };

    window.decrementarCantidad = function (id) {
        const item = document.getElementById(id); if (!item) return;
        const cantidadSpan = item.querySelector('.cantidad-platillo');
        const precioUnitario = parseFloat(item.dataset.precio) || 0;
        let cantidad = parseInt(cantidadSpan.innerText, 10) - 1;
        if (cantidad <= 0) { eliminarItemFila(item.querySelector('.btn-control-eliminar')); return; }
        cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
        item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
        ticketSubtotal += precioUnitario; actualizarTotales();
    };

    window.eliminarItemFila = function (btn) {
        const fila = btn.closest('.ticket-item');
        ticketSubtotal -= (parseInt(fila.dataset.cantidad, 10) * parseFloat(fila.dataset.precio));
        fila.remove(); actualizarTotales(); deseleccionarTicket();
        if (document.getElementById('listaTicket').children.length === 0) document.getElementById('estadoVacio').classList.remove('hidden');
    };

    if (typeof window.propinaGlobal === 'undefined') {
        window.propinaGlobal = 0;
    }

    window.actualizarTotales = function () {
        const descuento2x1Monto = calcularDescuento2x1Monto();
        const descuentoComboMonto = calcularDescuentoComboMonto();
        const subtotalTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalConDescuento = Math.max(0, subtotalTras2x1 - (subtotalTras2x1 * (descuentoPorcentaje / 100)));

        const iva = 0; // IVA desactivado
        window.totalComandaSinPropina = subtotalConDescuento;
        const txtSubtotalEl = document.getElementById('txtSubtotal');
        if (txtSubtotalEl) txtSubtotalEl.innerText = '$' + subtotalConDescuento.toFixed(2);
        const txtIvaEl = document.getElementById('txtIva'); if (txtIvaEl) txtIvaEl.innerText = '$0.00';

        const txtPropina = document.getElementById('txtPropina');
        if (txtPropina) {
            txtPropina.innerText = '$' + window.propinaGlobal.toFixed(2);
        }

        const totalComision = window.calcularComisionDelivery(subtotalConDescuento);
        const totalFinal = subtotalConDescuento + window.propinaGlobal + totalComision;
        const txtTotalComanda = document.getElementById('txtTotalComanda');
        if (txtTotalComanda) {
            txtTotalComanda.innerText = '$' + totalFinal.toFixed(2);
        }

        // Sincronizar barra fija inferior (móvil)
        const barraTotal = document.getElementById('barra-mobile-total');
        const barraCount = document.getElementById('barra-mobile-count');
        if (barraTotal) barraTotal.innerText = '$' + totalFinal.toFixed(2);
        if (barraCount) {
            const totalItems = document.querySelectorAll('#listaTicket .ticket-item').length;
            barraCount.innerText = totalItems;
        }

        if (typeof actualizarVistaTotal === 'function') {
            actualizarVistaTotal();
        }
    };

    window.calcularComisionDelivery = function (base) {
        const bloque = document.getElementById('bloqueComisionDelivery');
        const cfg = (window.ComandaConfig && window.ComandaConfig.delivery) || null;

        if (!cfg || !cfg.esDelivery) {
            if (bloque) bloque.classList.add('hidden');
            return 0;
        }

        const pctComision = parseFloat(cfg.comisionPorcentaje) || 0;
        const pctIva      = parseFloat(cfg.comisionIvaPorcentaje) || 0;

        const comision    = base * (pctComision / 100);
        const comisionIva = comision * (pctIva / 100);

        if (bloque) {
            bloque.classList.remove('hidden');

            const setTxt = (id, valor) => {
                const el = document.getElementById(id);
                if (el) el.innerText = valor;
            };

            setTxt('txtPlataformaNombre', cfg.plataforma || 'Delivery');
            setTxt('txtComisionPorcentaje', pctComision.toFixed(0));
            setTxt('txtComisionIvaPorcentaje', pctIva.toFixed(0));
            setTxt('txtComision', '$' + comision.toFixed(2));
            setTxt('txtComisionIva', '$' + comisionIva.toFixed(2));
        }

        return comision + comisionIva;
    };

    window.limpiarTicket = function () {
        document.getElementById('listaTicket').innerHTML = ''; 
        document.getElementById('estadoVacio').classList.remove('hidden');
        ticketSubtotal = 0; 
        descuentoPorcentaje = 0; 
        notaGeneral = '';
        promocion2x1Activa = false; 
        promocion2x1Nombre = '';
        comboActivo = false; 
        comboNombre = ''; 
        comboProductoIds = []; 
        comboMonto = 0;
        window.propinaGlobal = 0;
        
        const txtPropina = document.getElementById('txtPropina');
        if (txtPropina) txtPropina.innerText = '$0.00';

        actualizarTotales(); 
        deseleccionarTicket();
    };

    window.cambiarTiempoGlobal = function (tiempo) {
        tiempoGlobal = tiempo;
        const mapas = ['sin-tiempo', 'primer-tiempo', 'segundo-tiempo', 'tercer-tiempo'];
        const ids = ['tiempo-global-sin', 'tiempo-global-1', 'tiempo-global-2', 'tiempo-global-3'];
        ids.forEach((id, i) => {
            const btn = document.getElementById(id);
            if (mapas[i] === tiempo) btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] transition-all";
            else btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all";
        });
    };
})();