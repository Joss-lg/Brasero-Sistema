var ComandaConfig_ = window.ComandaConfig || {};

var categoriasDB = ComandaConfig_.categorias || [];
var productosDB = ComandaConfig_.productos || [];
var platillosEnviadosDB = ComandaConfig_.platillosEnviados || [];

var ticketSubtotal = 0;
var itemActivo = null;
var contadorItems = 0;
var tiempoGlobal = 'sin-tiempo';
var gramajePendiente = null;
var productoPorPesoPendiente = null;
var numeroPersonas = (ComandaConfig_.mesa && ComandaConfig_.mesa.personas) || 4;
var descuentoPorcentaje = 0;
var notaGeneral = '';
var promocion2x1Activa = false;
var promocion2x1Nombre = '';
var comboActivo = false;
var comboNombre = '';
var comboProductoIds = [];
var comboMonto = 0;
var capitanAutorizado = false;
var mesaDestinoSeleccionada = null;
var mesaDestinoSeleccionadaNumero = null;

// =====================================================================
// UTILIDADES COMPARTIDAS
// =====================================================================
(function () {
    const config = window.ComandaConfig || {};

    window.csrfToken = function () {
        return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
    };

    // ---------------------------------------------------------------
    // Compatibilidad del catálogo: detecta variantes antes de agregar
    // ---------------------------------------------------------------
    window.agregarProducto = function (productoId) {
        const producto = Array.isArray(productosDB)
            ? productosDB.find(p => Number(p.id) === Number(productoId))
            : null;

        if (!producto) {
            mostrarError('No se encontró el producto seleccionado.');
            return;
        }

        // Si tiene variantes y existen opciones disponibles, abre el modal de variantes
        const tieneVariantes = Boolean(producto.tiene_variantes && producto.variantes && producto.variantes.length > 0);
        if (tieneVariantes && typeof abrirModalVariante === 'function') {
            abrirModalVariante(producto.id, producto.nombre, producto.variantes);
            return;
        }

        const categoriaNombre = producto.categoria?.nombre ?? '';
        const precioNum = parseFloat(producto.precio) || 0;
        const precioPor100g = parseFloat(producto.precio_por_100g) || 0;
        const modificadores = Array.isArray(producto.modificadores) ? producto.modificadores : [];

        window.agregarAlTicket(
            producto.id,
            producto.nombre,
            precioNum,
            categoriaNombre,
            modificadores,
            Boolean(producto.se_vende_por_peso),
            precioPor100g
        );
    };

    // ---------------------------------------------------------------
    // ENVÍO DE ORDEN A COCINA (Soporte para variante_id)
    // ---------------------------------------------------------------
    window.enviarACocina = async function () {
        const items = document.querySelectorAll('#listaTicket .ticket-item');
        if (items.length === 0) {
            mostrarError('No hay platillos en la orden para enviar.');
            return;
        }

        const btnEnviar = document.getElementById('btn-enviar');
        if (btnEnviar) {
            btnEnviar.disabled = true;
            btnEnviar.classList.add('opacity-50', 'pointer-events-none');
        }

        const platillosPayload = Array.from(items).map(item => {
            const modsString = item.getAttribute('data-modificadores');
            let mods = [];
            try {
                mods = JSON.parse(modsString || '[]');
            } catch (e) {
                mods = [];
            }

            const varId = item.getAttribute('data-variante-id');

            return {
                id: parseInt(item.dataset.productoId, 10),
                variante_id: varId && varId !== '' && varId !== 'null' ? parseInt(varId, 10) : null,
                cantidad: parseInt(item.dataset.cantidad, 10) || 1,
                precio: parseFloat(item.dataset.precio) || 0,
                modificadores: mods,
                notas: item.dataset.nota || null,
                gramaje: item.dataset.gramaje !== 'sin-gramaje' ? item.dataset.gramaje : null,
                tiempo: item.dataset.tiempo || null
            };
        });

        const mesaId = config.mesa?.id || document.getElementById('mesa_id')?.value;
        const rutaEnviar = config.rutas?.enviar || '/mesero/comanda/enviar';

        try {
            const resp = await fetch(rutaEnviar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mesa_id: mesaId,
                    platillos: platillosPayload,
                    total: window.totalComandaSinPropina || ticketSubtotal,
                    personas: numeroPersonas,
                    descuento_porcentaje: descuentoPorcentaje
                })
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                mostrarExito(data.message || 'Orden enviada a cocina.');
                window.location.reload();
            } else {
                mostrarError(data.message || 'Error al enviar comanda.');
                if (btnEnviar) {
                    btnEnviar.disabled = false;
                    btnEnviar.classList.remove('opacity-50', 'pointer-events-none');
                }
            }
        } catch (err) {
            mostrarError('Error de comunicación con el servidor.');
            if (btnEnviar) {
                btnEnviar.disabled = false;
                btnEnviar.classList.remove('opacity-50', 'pointer-events-none');
            }
        }
    };

    // ---------------------------------------------------------------
    // TEMA (oscuro / crema)
    // ---------------------------------------------------------------
    window.toggleTheme = function () {
        const body = document.body;
        body.classList.toggle('modo-crema');
        localStorage.setItem('tema-ollintem', body.classList.contains('modo-crema') ? 'crema' : 'negro');
        actualizarIconoTema(body.classList.contains('modo-crema'));
    };

    function actualizarIconoTema(esCrema) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = esCrema
                ? 'fas fa-moon text-[11px] group-hover:rotate-45 transition-transform duration-500'
                : 'fas fa-sun text-[11px] group-hover:rotate-45 transition-transform duration-500';
        }
    }

    // ---------------------------------------------------------------
    // TABS (Orden / Enviado / Total)
    // ---------------------------------------------------------------
    window.cambiarTab = function (pestana) {
        const slider = document.getElementById('tab-slider');
        const btns = [document.getElementById('btn-tab-nueva-orden'), document.getElementById('btn-tab-enviados'), document.getElementById('btn-tab-comanda')];
        const txtTotalElement = document.getElementById('txtTotal');

        btns.forEach(el => { if (el) { el.classList.remove('text-[var(--bg-base)]'); el.classList.add('text-[var(--text-muted)]'); } });

        ['vista-nueva-orden', 'vista-enviados', 'vista-comanda'].forEach(id => {
            const vista = document.getElementById(id);
            if (vista) {
                vista.classList.add('hidden');
                vista.classList.remove('flex');
            }
        });

        if (pestana === 'nueva-orden') {
            if (slider) slider.style.transform = 'translateX(0%)';
            if (btns[0]) { btns[0].classList.add('text-[var(--bg-base)]'); btns[0].classList.remove('text-[var(--text-muted)]'); }
            const vNueva = document.getElementById('vista-nueva-orden');
            if (vNueva) { vNueva.classList.remove('hidden'); vNueva.classList.add('flex'); }
            
            if (txtTotalElement) txtTotalElement.innerText = '$0.00';

        } else if (pestana === 'enviados') {
            if (slider) slider.style.transform = 'translateX(100%)';
            if (btns[1]) { btns[1].classList.add('text-[var(--bg-base)]'); btns[1].classList.remove('text-[var(--text-muted)]'); }
            const vEnviados = document.getElementById('vista-enviados');
            if (vEnviados) { vEnviados.classList.remove('hidden'); vEnviados.classList.add('flex'); }
            
            if (txtTotalElement) txtTotalElement.innerText = '$0.00';

        } else if (pestana === 'comanda') {
            if (slider) slider.style.transform = 'translateX(100%)';
            if (btns[2]) { btns[2].classList.add('text-[var(--bg-base)]'); btns[2].classList.remove('text-[var(--text-muted)]'); }
            const vComanda = document.getElementById('vista-comanda');
            if (vComanda) { vComanda.classList.remove('hidden'); vComanda.classList.add('flex'); }
            
            actualizarVistaTotal();
        }
    };

    // ---------------------------------------------------------------
    // Recalcula y pinta el "Total a Pagar"
    // ---------------------------------------------------------------
    window.actualizarVistaTotal = function () {
        const contenedorNuevos = document.getElementById('lista-comanda-total');
        const mensajeVacio = document.getElementById('estadoVacioComanda');
        const itemsEnTicket = document.querySelectorAll('#listaTicket .ticket-item');
        const contenedorDB = document.getElementById('items-db-total');
        const hayPlatillosEnDB = contenedorDB && contenedorDB.children.length > 0;
        const txtTotalElement = document.getElementById('txtTotal');

        if (contenedorNuevos) contenedorNuevos.innerHTML = '';

        if (itemsEnTicket.length === 0 && !hayPlatillosEnDB) {
            if (mensajeVacio) { mensajeVacio.classList.remove('hidden'); mensajeVacio.classList.add('flex'); }
        } else {
            if (mensajeVacio) { mensajeVacio.classList.add('hidden'); mensajeVacio.classList.remove('flex'); }
            if (itemsEnTicket.length > 0 && contenedorNuevos) {
                contenedorNuevos.innerHTML += `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mt-4 mb-2 px-1">Por Enviar</div>`;
                itemsEnTicket.forEach(item => {
                    const cantEl = item.querySelector('.cantidad-platillo');
                    const nomEl = item.querySelector('.nombre-platillo');
                    const preEl = item.querySelector('.precio-platillo');

                    const cant = cantEl ? cantEl.innerText : '1';
                    const nombre = nomEl ? nomEl.innerText : 'Producto';
                    const precio = preEl ? preEl.innerText : '$0.00';

                    contenedorNuevos.innerHTML += `
                        <div class="flex justify-between items-center p-2.5 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm mb-2">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold flex items-center justify-center">${cant}</span>
                                <span class="text-[11px] font-bold text-[var(--text-main)]">${nombre}</span>
                            </div>
                            <span class="text-[11px] font-bold text-[var(--text-main)]">${precio}</span>
                        </div>
                    `;
                });
            }
        }

        const totalHistorial = platillosEnviadosDB.reduce((acc, i) => acc + ((i.precio || 0) * (i.cantidad || 1)), 0);
        
        const descuento2x1Monto = typeof window.calcularDescuento2x1Monto === 'function' ? window.calcularDescuento2x1Monto() : 0;
        const descuentoComboMonto = typeof window.calcularDescuentoComboMonto === 'function' ? window.calcularDescuentoComboMonto() : 0;
        
        const subtotalTicketTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalTicketConDescuento = Math.max(0, subtotalTicketTras2x1 - (subtotalTicketTras2x1 * (descuentoPorcentaje / 100)));
        const subtotalGeneral = subtotalTicketConDescuento + totalHistorial;

        const ivaConfig = (window.ComandaConfig && window.ComandaConfig.iva) || { habilitado: false, porcentaje: 0 };
        const ivaGeneral = ivaConfig.habilitado ? subtotalGeneral * (ivaConfig.porcentaje / 100) : 0;

        let comisionGeneral = 0;
        const cfgDelivery = (window.ComandaConfig && window.ComandaConfig.delivery) || null;
        if (cfgDelivery && cfgDelivery.esDelivery) {
            const baseComision = subtotalGeneral + ivaGeneral;
            const comision = baseComision * ((parseFloat(cfgDelivery.comisionPorcentaje) || 0) / 100);
            const comisionIva = comision * ((parseFloat(cfgDelivery.comisionIvaPorcentaje) || 0) / 100);
            comisionGeneral = comision + comisionIva;
        }

        if (txtTotalElement) {
            txtTotalElement.innerText = '$' + (subtotalGeneral + ivaGeneral + comisionGeneral).toFixed(2);
        }
    };

    // ---------------------------------------------------------------
    // NOTIFICACIONES TOAST
    // ---------------------------------------------------------------
    window.mostrarToast = function (msg, type = 'info') {
        const c = document.getElementById('toastContainer'); if (!c) return;
        const t = document.createElement('div');
        t.className = `toast-panel ${type}`;
        t.innerHTML = `<div class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i></div><div><strong>${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Aviso'}</strong><span>${msg}</span></div>`;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); t.addEventListener('transitionend', () => t.remove(), { once: true }); }, 3000);
    };
    window.mostrarError = function (m) { mostrarToast(m, 'error'); };
    window.mostrarExito = function (m) { mostrarToast(m, 'success'); };

    // ---------------------------------------------------------------
    // Cierra modal genérico
    // ---------------------------------------------------------------
    window.cerrarModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    };

    // ── Teclado numérico virtual ────────────────
    window.escribirNumVirtual = function (inputId, digit) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.value.length >= 6) return;
        input.value += digit;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    window.borrarNumVirtual = function (inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.value = input.value.slice(0, -1);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    window.esPantallaTactil = function () {
        return window.innerWidth > 768;
    };

    let _inputVirtualActivo = null;

    window.setInputVirtualActivo = function (inputId) {
        _inputVirtualActivo = inputId;
    };
    window.clearInputVirtualActivo = function () {
        _inputVirtualActivo = null;
    };

    document.addEventListener('keydown', function (e) {
        if (!_inputVirtualActivo) return;
        const input = document.getElementById(_inputVirtualActivo);
        if (!input) return;

        if (e.key === 'Backspace') {
            e.preventDefault();
            input.value = input.value.slice(0, -1);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const btnConfirm = document.querySelector('[data-confirm-virtual]:not([disabled])');
            if (btnConfirm) btnConfirm.click();
        } else if (e.key.length === 1) {
            e.preventDefault();
            const soloNumeros = input.type === 'password' || input.dataset.soloNumeros === 'true';
            if (soloNumeros && !/[0-9]/.test(e.key)) return;
            if (input.maxLength > 0 && input.value.length >= input.maxLength) return;
            input.value += e.key;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    // ── Panel ticket mobile ────────────────────────────────
    window.toggleOrdenMobile = function () {
        const panel    = document.getElementById('col-ticket');
        const backdrop = document.getElementById('backdropOrdenMobile');
        if (!panel) return;

        const estaAbierto = !panel.classList.contains('translate-y-full');

        if (estaAbierto) {
            panel.classList.add('translate-y-full');
            panel.classList.remove('translate-y-0');
            if (backdrop) { backdrop.classList.add('hidden'); backdrop.classList.remove('flex'); }
            document.body.style.overflow = '';
        } else {
            panel.classList.remove('translate-y-full');
            panel.classList.add('translate-y-0');
            if (backdrop) { backdrop.classList.remove('hidden'); backdrop.classList.add('flex'); }
            document.body.style.overflow = 'hidden';
        }
    };
})();