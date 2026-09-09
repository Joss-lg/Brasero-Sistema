(function () {
    const config = window.ComandaConfig || {};

    window.ajustarPersonas = function () { 
        const input = document.getElementById('personasInput');
        const modal = document.getElementById('modalPersonas');
        if (input) input.value = numeroPersonas; 
        if (modal) modal.classList.remove('hidden'); 
    };

    window.guardarPersonas = function () {
        const input = document.getElementById('personasInput');
        if (!input) return;

        const v = parseInt(input.value, 10);
        if (isNaN(v) || v <= 0) { mostrarError('Inválido'); return; }

        numeroPersonas = v;
        const txtPers = document.getElementById('txtPersonas');
        if (txtPers) txtPers.innerText = v;
        cerrarModal('modalPersonas');

        fetch(config.rutas.comandaPersonas, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({ personas: v })
        })
        .then(res => res.json())
        .then(data => { if (!data.success) mostrarError('No se pudo guardar el número de personas.'); })
        .catch(() => mostrarError('Error de red al guardar personas.'));
    };

    window.agregarNota = function () { 
        if (!itemActivo) { mostrarError('Selecciona platillo'); return; } 
        const textarea = document.getElementById('notaTextarea');
        const modal = document.getElementById('modalNota');
        const tituloProducto = document.getElementById('notaModalProducto');

        const nombreProducto = itemActivo.querySelector('.nombre-platillo')?.innerText || 'Producto';
        if (tituloProducto) tituloProducto.innerText = nombreProducto;

        if (textarea) textarea.value = ''; 
        if (modal) modal.classList.remove('hidden'); 
    };
    
    window.aplicarDescuento = function () { 
        const input = document.getElementById('descuentoInput');
        const modal = document.getElementById('modalDescuento');
        if (input) input.value = descuentoPorcentaje; 
        if (modal) modal.classList.remove('hidden'); 
    };
    
    window.guardarDescuento = function () { 
        const input = document.getElementById('descuentoInput');
        if (!input) return;
        const v = parseFloat(input.value); 
        if (isNaN(v) || v < 0 || v > 100) { mostrarError('Inválido'); return; } 
        descuentoPorcentaje = v; 
        if (typeof window.actualizarTotales === 'function') window.actualizarTotales(); 
        cerrarModal('modalDescuento'); 
    };
    
    window.insertarNotaCaracter = function (c) { const t = document.getElementById('notaTextarea'); if (t) { t.value += c; t.focus(); } };
    window.insertarNotaEspacio = function () { const t = document.getElementById('notaTextarea'); if (t) { t.value += ' '; t.focus(); } };
    window.borrarNotaCaracter = function () { const t = document.getElementById('notaTextarea'); if (t) { t.value = t.value.slice(0, -1); t.focus(); } };
    window.limpiarNota = function () { const t = document.getElementById('notaTextarea'); if (t) { t.value = ''; t.focus(); } };

    // ---------------------------------------------------------------
    // GESTIÓN DE PROPINA (MODAL Y CÁLCULOS CORREGIDOS)
    // ---------------------------------------------------------------
    
    window.abrirModalPropina = function () {
        const input = document.getElementById('propinaInput');
        const modal = document.getElementById('modalPropina');
        
        if (input) {
            input.value = window.propinaGlobal > 0 ? window.propinaGlobal.toFixed(2) : '';
        }
        
        if (modal) {
            modal.classList.remove('hidden');
        }
        
        if (input) {
            setTimeout(() => input.focus(), 150);
        }
    };

    window.calcularPropinaPorcentaje = function (porcentaje) {
        let total = parseFloat(window.totalComandaSinPropina) || 0; 
        const input = document.getElementById('propinaInput');
        
        if (total > 0) {
            if (input) {
                let calculado = (total * (porcentaje / 100)).toFixed(2);
                input.value = calculado;
            }
        } else {
            alert("Primero debes agregar productos para calcular un porcentaje.");
        }
    };

    window.guardarPropina = function () {
        const input = document.getElementById('propinaInput');
        const montoPropina = input ? (parseFloat(input.value) || 0) : 0;
        
        if (montoPropina < 0) {
            alert("La propina no puede ser un valor negativo.");
            return;
        }

        window.propinaGlobal = montoPropina;
        
        if (typeof window.actualizarTotales === 'function') {
            window.actualizarTotales();
        }
        
        cerrarModal('modalPropina');
    };

    window.imprimirPrecuenta = function () {
        const url = config.rutas && config.rutas.comandaPrecuenta;
        if (!url) { mostrarError('No se encontró la ruta de la pre-cuenta. Revisa ComandaConfig.rutas.comandaPrecuenta.'); return; }
        window.open(url, '_blank');
    };

    // ---------------------------------------------------------------
    // ENVÍO A COCINA
    // ---------------------------------------------------------------
    window.enviarACocina = function () {
        const itemsHTML = document.querySelectorAll('.ticket-item');
        if (itemsHTML.length === 0) { mostrarError("¡Agrega platillos!"); return; }

        const platillosData = [];
        itemsHTML.forEach(item => {
            const nomEl = item.querySelector('.nombre-platillo');
            const nombre = nomEl ? nomEl.innerText : 'Producto';
            const modsElementos = item.querySelectorAll('.nota-texto-real');
            const mods = []; 
            modsElementos.forEach(m => mods.push(m.innerText.replace('•', '').trim()));

            // Obtener el ID de la variante si existe
            const varIdRaw = item.dataset.varianteId;
            const varianteId = (varIdRaw && varIdRaw !== 'sin-variante') ? parseInt(varIdRaw, 10) : null;

            platillosData.push({
                id: parseInt(item.dataset.productoId, 10),
                variante_id: varianteId, // <-- Parámetro mapeado al backend
                nombre: nombre,
                cantidad: parseInt(item.dataset.cantidad, 10),
                precio: parseFloat(item.dataset.precio),
                modificadores: mods,
                gramaje: item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje,
                tiempo: item.dataset.tiempo
            });
        });

        const btn = document.getElementById('btn-enviar'); 
        if (btn) { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true; }
        
        const txtTotalEl = document.getElementById('txtTotal') || document.getElementById('txtTotalComanda');
        const totalParseado = txtTotalEl ? parseFloat(txtTotalEl.innerText.replace('$', '').replace(',', '')) : 0;

        fetch(config.rutas.comandaEnviar, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest', 
                'X-CSRF-TOKEN': csrfToken() 
            },
            body: JSON.stringify({
                mesa_id: (typeof mesaDestinoSeleccionada !== 'undefined' && mesaDestinoSeleccionada) || (config.mesa && config.mesa.id) || 1,
                platillos: platillosData,
                total: totalParseado,
                personas: numeroPersonas,
                descuento_porcentaje: descuentoPorcentaje,
                nota_general: typeof notaGeneral !== 'undefined' ? notaGeneral : '',
                propina: window.propinaGlobal || 0
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarExito("¡Enviado a cocina!");

                platillosData.forEach(p => {
                    if (typeof platillosEnviadosDB !== 'undefined') {
                        platillosEnviadosDB.push({
                            nombre: p.nombre,
                            cantidad: p.cantidad,
                            precio: p.precio,
                            estado: 'enviado'
                        });
                    }
                });

                if (typeof window.limpiarTicket === 'function') window.limpiarTicket();

                setTimeout(() => window.location.href = (config.rutas && config.rutas.dashboard) || '/', 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => { 
            mostrarError(error.message); 
            if (btn) { 
                btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i><span>Enviar Orden</span>'; 
                btn.disabled = false; 
            }
        });
    };
})();