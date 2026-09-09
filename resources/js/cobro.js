/**
 * Muestra una notificación tipo "toast" arriba a la derecha, replicando el
 * mismo diseño visual que ya usa el resto del sistema: tarjeta oscura,
 * degradado en la barra superior e inferior, ícono con anillo, título en
 * mayúsculas y barra de progreso que indica cuánto falta para cerrarse.
 * @param {string} mensaje
 * @param {'error'|'exito'|'info'} tipo
 * @param {string} [titulo] - opcional, si no se manda usa el título por defecto de cada tipo
 */
function mostrarAlerta(mensaje, tipo = 'info', titulo = null) {
    // Inyecta la animación una sola vez
    if (!document.getElementById('toast-anim-style')) {
        const style = document.createElement('style');
        style.id = 'toast-anim-style';
        style.textContent = `
            @keyframes toast-in { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: translateX(0); } }
            @keyframes toast-out { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(24px); } }
        `;
        document.head.appendChild(style);
    }

    const estilos = {
        error: { gradiente: 'linear-gradient(90deg, #ef4444, #f97316)', iconBg: 'bg-red-500/15', ring: 'ring-red-500/40', iconColor: 'text-red-400', icon: 'fa-circle-exclamation', tituloColor: 'text-red-400', tituloDefault: 'ERROR', duracion: 7000 },
        exito: { gradiente: 'linear-gradient(90deg, #34d399, #22d3ee)', iconBg: 'bg-emerald-500/15', ring: 'ring-emerald-400/40', iconColor: 'text-emerald-400', icon: 'fa-check', tituloColor: 'text-emerald-400', tituloDefault: 'OPERACIÓN EXITOSA', duracion: 5000 },
        info:  { gradiente: 'linear-gradient(90deg, #60a5fa, #22d3ee)', iconBg: 'bg-blue-500/15', ring: 'ring-blue-400/40', iconColor: 'text-blue-400', icon: 'fa-info', tituloColor: 'text-blue-400', tituloDefault: 'AVISO', duracion: 5000 },
    };
    const s = estilos[tipo] || estilos.info;
    const tituloFinal = titulo || s.tituloDefault;

    const anterior = document.getElementById('toast-alerta');
    if (anterior) anterior.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-alerta';
    toast.className = 'fixed top-6 right-6 z-[9999] w-full max-w-sm rounded-2xl bg-zinc-900 shadow-2xl overflow-hidden';
    toast.style.animation = 'toast-in 0.3s ease-out';

    toast.innerHTML = `
        <div class="h-[3px] w-full" style="background:${s.gradiente};"></div>
        <div class="flex items-start gap-3 p-4">
            <div class="w-10 h-10 rounded-full ${s.iconBg} ring-2 ${s.ring} flex items-center justify-center shrink-0">
                <i class="fa-solid ${s.icon} ${s.iconColor}"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <p class="text-xs font-black uppercase tracking-wider ${s.tituloColor}">${tituloFinal}</p>
                <p class="text-sm font-semibold text-white/90 mt-0.5">${mensaje}</p>
            </div>
            <button type="button" class="text-white/30 hover:text-white/70 shrink-0 leading-none cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="h-[3px] w-full bg-white/5">
            <div class="toast-progress h-full" style="background:${s.gradiente}; width:100%;"></div>
        </div>
    `;

    document.body.appendChild(toast);

    function cerrarToast() {
        clearTimeout(timeoutId);
        toast.style.animation = 'toast-out 0.25s ease-in forwards';
        setTimeout(() => toast.remove(), 250);
    }

    const progressBar = toast.querySelector('.toast-progress');
    requestAnimationFrame(() => {
        progressBar.style.transition = `width ${s.duracion}ms linear`;
        progressBar.style.width = '0%';
    });

    const timeoutId = setTimeout(cerrarToast, s.duracion);
    toast.querySelector('button').addEventListener('click', cerrarToast);
}

/**
 * Carga el ticket en un iframe invisible y dispara de forma directa la
 * ventana de impresión nativa del navegador en la misma página.
 * @param {Function} [alCerrar] - se ejecuta después de mandar la orden de impresión
 */
function mostrarModalTicket(alCerrar) {
    const urlTicket = window.COBRO_CONFIG && window.COBRO_CONFIG.urlTicket;
    if (!urlTicket) {
        console.warn('No se encontró urlTicket en COBRO_CONFIG.');
        if (typeof alCerrar === 'function') alCerrar();
        return;
    }

    // Oculta el modal en caso de que esté presente en el DOM
    const modalVisual = document.getElementById('modal-ticket-preview');
    if (modalVisual) modalVisual.classList.add('hidden');

    let iframe = document.getElementById('ticket-impresion-silenciosa');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'ticket-impresion-silenciosa';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }

    iframe.src = urlTicket;

    iframe.onload = () => {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            console.error('No se pudo imprimir el ticket:', e);
            mostrarAlerta('No se pudo invocar la impresión del ticket.', 'error');
        }

        if (typeof alCerrar === 'function') {
            // Breve espera para permitir que el proceso de impresión tome el control
            setTimeout(() => {
                alCerrar();
            }, 600);
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    // ==========================================================================
    // SELECCIÓN DE ELEMENTOS DE LA INTERFAZ
    // ==========================================================================
    const displayMonto = document.getElementById('monto-input');
    const displayCambio = document.getElementById('display-cambio');
    const btnPagar = document.getElementById('btn-procesar-pago'); // Botón "FINALIZAR"
    const teclas = document.querySelectorAll('.btn-tecla');
    const btnTicket = document.getElementById('btn-ticket');
    
    const btnAbrirModal = document.getElementById('btn-abrir-modal-metodo');
    
    const modalMetodo = document.getElementById('modal-metodo');
    const btnConfirmarCombinado = document.getElementById('btn-confirmar-combinado');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal-metodo');
    const btnActivarCombinado = document.getElementById('btn-activar-combinado');
    
    const seccionLista = document.getElementById('seccion-metodos-lista');
    const seccionCombinado = document.getElementById('seccion-metodos-combinado');
    const tituloModal = document.getElementById('modal-metodo-titulo');

    const cInputEfectivo = document.getElementById('comb-input-efectivo');
    const cInputTarjeta = document.getElementById('comb-input-tarjeta');
    const cInputTransferencia = document.getElementById('comb-input-transferencia');
    const cDisplayTotal = document.getElementById('comb-total-requerido');
    const cDisplayStatus = document.getElementById('comb-monto-status');
    const cLabelStatus = document.getElementById('comb-label-status');
    
    const cInputRefTarjeta = document.getElementById('comb-ref-tarjeta');
    const cInputRefTransferencia = document.getElementById('comb-ref-transferencia');
    
    const inputMetodoOculto = document.getElementById('metodo-pago');
    const labelMetodoVisible = document.getElementById('metodo-pago-label');

    const nonCashSection = document.getElementById('non-cash-section');
    const inputReferenciaUnica = document.getElementById('referencia');

    let modoCombinadoActivo = false;

    // ==========================================================================
    // 1. VALIDACIÓN DE SEGURIDAD PARA EL TOTAL DE LA ORDEN
    // ==========================================================================
    const totalElement = document.getElementById('total-pagar-derecha');
    if (!totalElement) {
        console.warn("Elemento 'total-pagar-derecha' no encontrado. Script de cobro detenido.");
        return; 
    }

    let totalPagar = parseFloat(totalElement.innerText.replace(/[^0-9.]/g, '')) || 0;
    let montoActual = "0.00";

    // ==========================================================================
    // 0. DIVISIÓN DE CUENTA
    // ==========================================================================
    const config = window.COBRO_CONFIG || {};
    let division = config.division || null;
    let cuentaSeleccionadaId = null;

    const inputCuentaDivisionId = document.getElementById('cuenta-division-id');
    const btnAbrirDivision = document.getElementById('btn-abrir-division');
    const panelIniciarDivision = document.getElementById('panel-iniciar-division');
    const btnConfirmarDivision = document.getElementById('btn-confirmar-division');
    const btnCancelarDivision = document.getElementById('btn-cancelar-division');
    const tipoDivisionBtns = document.querySelectorAll('.tipo-division-btn');
    const inputNumeroPersonas = document.getElementById('input-numero-personas');
    let tipoDivisionSeleccionado = 'equitativa';

    function csrfToken() {
        return (document.querySelector('meta[name="csrf-token"]') || {}).content || config.csrfToken;
    }

    async function postJSON(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });
        return response.json();
    }

    if (btnAbrirDivision && panelIniciarDivision) {
        btnAbrirDivision.addEventListener('click', () => {
            panelIniciarDivision.classList.toggle('hidden');
        });
    }

    tipoDivisionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tipoDivisionSeleccionado = btn.dataset.tipoDivision;
            tipoDivisionBtns.forEach(b => {
                b.classList.remove('border-blue-500', 'bg-blue-500/10', 'text-blue-600', 'dark:text-blue-300');
                b.classList.add('border-zinc-200', 'dark:border-white/10', 'text-zinc-600', 'dark:text-zinc-300');
            });
            btn.classList.add('border-blue-500', 'bg-blue-500/10', 'text-blue-600', 'dark:text-blue-300');
            btn.classList.remove('border-zinc-200', 'dark:border-white/10', 'text-zinc-600', 'dark:text-zinc-300');
        });
    });

    if (btnConfirmarDivision) {
        btnConfirmarDivision.addEventListener('click', async () => {
            const personas = parseInt(inputNumeroPersonas.value, 10) || 0;
            if (personas < 2) {
                mostrarAlerta('Se necesitan al menos 2 personas para dividir la cuenta.', 'error');
                return;
            }

            btnConfirmarDivision.disabled = true;
            try {
                const data = await postJSON(config.urlDivisionIniciar, {
                    mesa_id: config.mesaId,
                    tipo: tipoDivisionSeleccionado,
                    personas
                });

                if (data.success) {
                    location.reload();
                } else {
                    mostrarAlerta(data.message || 'No se pudo dividir la cuenta.', 'error');
                    btnConfirmarDivision.disabled = false;
                }
            } catch (e) {
                console.error(e);
                mostrarAlerta('Ocurrió un error al dividir la cuenta.', 'error');
                btnConfirmarDivision.disabled = false;
            }
        });
    }

    if (btnCancelarDivision) {
        btnCancelarDivision.addEventListener('click', async () => {
            if (!confirm('¿Cancelar la división de la cuenta? Se perderán las asignaciones hechas.')) return;

            try {
                const data = await postJSON(config.urlDivisionCancelar, { mesa_id: config.mesaId });
                if (data.success) {
                    location.reload();
                } else {
                    mostrarAlerta(data.message || 'No se pudo cancelar la división.', 'error');
                }
            } catch (e) {
                console.error(e);
                mostrarAlerta('Ocurrió un error al cancelar la división.', 'error');
            }
        });
    }

    function aplicarDivisionAlDOM(division) {
        if (!division) return;

        (division.cuentas || []).forEach(cuenta => {
            const tab = document.querySelector(`.btn-cuenta[data-cuenta-id="${cuenta.id}"]`);
            if (!tab) return;

            tab.dataset.subtotal = cuenta.subtotal.toFixed(2);
            tab.dataset.iva = '0.00';
            tab.dataset.propina = cuenta.propina.toFixed(2);
            tab.dataset.total = cuenta.total.toFixed(2);

            const valorEl = tab.querySelector('.valor-cuenta');
            if (valorEl) valorEl.textContent = '$' + cuenta.total.toLocaleString('en-US', { minimumFractionDigits: 2 });

            if (String(cuenta.id) === String(cuentaSeleccionadaId)) {
                totalPagar = cuenta.total;
                if (resumenTotal) resumenTotal.textContent = '$' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
                if (resumenSubtotal) resumenSubtotal.textContent = '$' + cuenta.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 });
                if (resumenIva) resumenIva.textContent = '$0.00';
                if (resumenPropina) resumenPropina.textContent = '$' + cuenta.propina.toLocaleString('en-US', { minimumFractionDigits: 2 });
                if (avisoDivisionPanel) avisoDivisionPanel.textContent = 'Cobrando a Persona ' + cuenta.numero_cuenta + ' · $' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
                actualizarDisplay(montoActual);
            }
        });

        const asignaciones = division.asignacionesPorDetalle || {};
        Object.keys(asignaciones).forEach(detalleId => {
            const info = asignaciones[detalleId];
            document.querySelectorAll(`.stepper-persona[data-detalle-id="${detalleId}"]`).forEach(stepper => {
                const numero = stepper.dataset.numero;
                const valorEl = stepper.querySelector('.stepper-valor');
                if (valorEl) valorEl.textContent = (info.por_persona && info.por_persona[numero]) || 0;
            });

            const badge = document.querySelector(`.producto-asignacion[data-detalle-id="${detalleId}"] .sin-asignar-badge`);
            if (badge) {
                if (info.sin_asignar > 0) {
                    badge.textContent = info.sin_asignar + ' sin asignar';
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        });
    }

    document.querySelectorAll('.stepper-persona').forEach(stepper => {
        const detalleId = stepper.dataset.detalleId;
        const numeroCuenta = parseInt(stepper.dataset.numero, 10);
        const valorEl = stepper.querySelector('.stepper-valor');
        const btnMas = stepper.querySelector('.btn-stepper-sumar');
        const btnMenos = stepper.querySelector('.btn-stepper-restar');

        async function actualizarCantidad(nuevaCantidad) {
            if (nuevaCantidad < 0) return;

            const valorAnterior = valorEl.textContent;
            valorEl.textContent = nuevaCantidad;
            [btnMas, btnMenos].forEach(b => b && (b.disabled = true));

            try {
                const data = await postJSON(config.urlDivisionAsignar, {
                    mesa_id: config.mesaId,
                    detalle_id: detalleId,
                    numero_cuenta: numeroCuenta,
                    cantidad: nuevaCantidad
                });

                if (data.success) {
                    aplicarDivisionAlDOM(data.division);
                } else {
                    valorEl.textContent = valorAnterior;
                    mostrarAlerta(data.message || 'No se pudo asignar el producto.', 'error');
                }
            } catch (e) {
                console.error(e);
                valorEl.textContent = valorAnterior;
                mostrarAlerta('Ocurrió un error al asignar el producto.', 'error');
            } finally {
                [btnMas, btnMenos].forEach(b => b && (b.disabled = false));
            }
        }

        if (btnMas) {
            btnMas.addEventListener('click', () => {
                const actual = parseInt(valorEl.textContent, 10) || 0;
                actualizarCantidad(actual + 1);
            });
        }
        if (btnMenos) {
            btnMenos.addEventListener('click', () => {
                const actual = parseInt(valorEl.textContent, 10) || 0;
                if (actual <= 0) return;
                actualizarCantidad(actual - 1);
            });
        }
    });

    const tabsCuentas = document.querySelectorAll('#tabs-cuentas-division .btn-cuenta');
    const resumenTotal = document.getElementById('resumen-total');
    const resumenSubtotal = document.getElementById('resumen-subtotal');
    const resumenIva = document.getElementById('resumen-iva');
    const resumenPropina = document.getElementById('resumen-propina');
    const resumenPersonaSel = document.getElementById('resumen-persona-seleccionada');
    const avisoDivisionPanel = document.getElementById('aviso-division-panel');

    function actualizarBotonFinalizar() {
        if (!btnPagar || btnPagar.dataset.dividido !== '1') return;
        btnPagar.disabled = !cuentaSeleccionadaId;
        btnPagar.innerText = cuentaSeleccionadaId ? 'COBRAR ESTA PERSONA' : 'FINALIZAR';
    }

    function seleccionarCuenta(btn) {
        tabsCuentas.forEach(b => b.classList.remove('ring-2', 'ring-blue-500'));
        btn.classList.add('ring-2', 'ring-blue-500');

        cuentaSeleccionadaId = btn.dataset.cuentaId;
        if (inputCuentaDivisionId) inputCuentaDivisionId.value = cuentaSeleccionadaId;

        totalPagar = parseFloat(btn.dataset.total) || 0;

        if (totalElement) totalElement.innerText = '$' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (resumenTotal) resumenTotal.textContent = '$' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (resumenSubtotal) resumenSubtotal.textContent = '$' + (parseFloat(btn.dataset.subtotal) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (resumenIva) resumenIva.textContent = '$' + (parseFloat(btn.dataset.iva) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (resumenPropina) resumenPropina.textContent = '$' + (parseFloat(btn.dataset.propina) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (resumenPersonaSel) resumenPersonaSel.textContent = 'Cobrando a: Persona ' + btn.dataset.numero;
        if (avisoDivisionPanel) avisoDivisionPanel.textContent = 'Cobrando a Persona ' + btn.dataset.numero + ' · $' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });

        montoActual = totalPagar.toFixed(2);
        actualizarDisplay(montoActual);

        actualizarBotonFinalizar();
    }

    tabsCuentas.forEach(btn => {
        if (btn.disabled) return;
        btn.addEventListener('click', () => seleccionarCuenta(btn));
    });

    actualizarBotonFinalizar();

    // ==========================================================================
    // 2. TECLADO DIGITAL DEL PANEL DE CONTROL
    // ==========================================================================
    if (teclas.length > 0 && displayMonto && displayCambio) {
        teclas.forEach(tecla => {
            tecla.addEventListener('click', () => {
                const valor = tecla.dataset.value;
                if (valor === 'DEL') {
                    montoActual = "0.00";
                } else {
                    montoActual = (montoActual === "0.00") ? valor : montoActual + valor;
                }
                actualizarDisplay(montoActual);
            });
        });
    }

    function actualizarDisplay(valor) {
        const montoIngresado = parseFloat(valor.replace(/[^0-9.]/g, '')) || 0;
        displayMonto.textContent = '$' + montoIngresado.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        const cambio = montoIngresado - totalPagar;
        if (cambio >= 0) {
            displayCambio.textContent = '$' + cambio.toLocaleString('en-US', {minimumFractionDigits: 2});
            displayCambio.className = 'text-green-500 font-black';
        } else {
            displayCambio.textContent = '$' + Math.abs(cambio).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (Falta)';
            displayCambio.className = 'text-red-500 font-black';
        }
    }

    // ==========================================================================
    // 3. CONTROL DE APERTURA Y CIERRE DEL MODAL DE MÉTODOS
    // ==========================================================================
    if (btnAbrirModal && modalMetodo) {
        btnAbrirModal.addEventListener('click', (e) => {
            e.preventDefault();
            modalMetodo.classList.remove('hidden'); 
        });
    }

    if (btnCerrarModal && modalMetodo) {
        btnCerrarModal.addEventListener('click', () => {
            if (modoCombinadoActivo) {
                modoCombinadoActivo = false;
                tituloModal.textContent = "Método de Pago";
                seccionCombinado.classList.add('hidden');
                seccionLista.classList.remove('hidden');
                btnCerrarModal.textContent = "Cancelar";
            } else {
                modalMetodo.classList.add('hidden');
            }
        });
    }

    function actualizarVisibilidadReferencia(metodo) {
        if (!nonCashSection) return;
        const esEfectivo = metodo.toLowerCase() === 'efectivo';
        nonCashSection.classList.toggle('hidden', esEfectivo);
        if (esEfectivo && inputReferenciaUnica) {
            inputReferenciaUnica.value = '';
        }
    }

    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const metodo = this.getAttribute('data-metodo');
            
            if (inputMetodoOculto) inputMetodoOculto.value = metodo;
            if (labelMetodoVisible) labelMetodoVisible.textContent = metodo.charAt(0).toUpperCase() + metodo.slice(1);
            
            const icon = labelMetodoVisible ? labelMetodoVisible.previousElementSibling : null;
            if (icon) {
                icon.className = this.querySelector('i').className + " text-lg";
            }

            actualizarVisibilidadReferencia(metodo);
            
            if (modalMetodo) modalMetodo.classList.add('hidden');
        });
    });

    // ==========================================================================
    // 4. INTERCAMBIO DINÁMICO Y MATEMÁTICAS DEL PAGO COMBINADO
    // ==========================================================================
    if (btnActivarCombinado) {
        btnActivarCombinado.addEventListener('click', () => {
            modoCombinadoActivo = true;
            tituloModal.textContent = "Pagos Combinados";
            seccionLista.classList.add('hidden');
            seccionCombinado.classList.remove('hidden');
            btnCerrarModal.textContent = "Volver Atrás";

            if (cDisplayTotal) {
                cDisplayTotal.textContent = '$' + totalPagar.toLocaleString('en-US', {minimumFractionDigits: 2});
            }
            
            if (cInputEfectivo) cInputEfectivo.value = '';
            if (cInputTarjeta) cInputTarjeta.value = '';
            if (cInputTransferencia) cInputTransferencia.value = '';
            
            if (cInputRefTarjeta) cInputRefTarjeta.value = '';
            if (cInputRefTransferencia) cInputRefTransferencia.value = '';
            
            calcularMatematicasCombinado();
        });
    }

    [cInputEfectivo, cInputTarjeta, cInputTransferencia].forEach(input => {
        if (input) {
            input.addEventListener('input', () => {
                calcularMatematicasCombinado();
            });
        }
    });

    function calcularMatematicasCombinado() {
        if (!cDisplayStatus || !cLabelStatus || !btnConfirmarCombinado) return;

        const efec = parseFloat(cInputEfectivo.value) || 0;
        const tarj = parseFloat(cInputTarjeta.value) || 0;
        const transf = parseFloat(cInputTransferencia.value) || 0;

        const totalIngresado = efec + tarj + transf;
        const diferencia = totalIngresado - totalPagar;

        if (diferencia >= 0) {
            cLabelStatus.textContent = 'Cambio';
            cDisplayStatus.textContent = '$' + diferencia.toLocaleString('en-US', {minimumFractionDigits: 2});
            cDisplayStatus.className = 'text-xl font-black text-emerald-500';

            btnConfirmarCombinado.disabled = false;
            btnConfirmarCombinado.className = "w-full py-4 px-4 bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl border border-emerald-500 hover:bg-emerald-500 transition-all cursor-pointer";
        } else {
            cLabelStatus.textContent = 'Restante';
            cDisplayStatus.textContent = '$' + Math.abs(diferencia).toLocaleString('en-US', {minimumFractionDigits: 2});
            cDisplayStatus.className = 'text-xl font-black text-red-500';

            btnConfirmarCombinado.disabled = true;
            btnConfirmarCombinado.className = "w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 !text-gray-400 dark:!text-white/30 font-black text-sm uppercase tracking-wider rounded-2xl border !border-gray-200 dark:!border-white/5 cursor-not-allowed transition-all";
        }
    }

    // ==========================================================================
    // 5. ENVÍOS FETCH POST (FLUJO A Y FLUJO B)
    // ==========================================================================
    if (btnPagar) {
        btnPagar.addEventListener('click', async () => {
            const montoRaw = displayMonto.textContent.replace(/[^0-9.]/g, '');
            const montoIngresado = parseFloat(montoRaw) || 0;
            const inputMesa = document.getElementById('mesa-id');
            const metodo = inputMetodoOculto ? inputMetodoOculto.value : 'efectivo';

            if (!inputMesa) {
                mostrarAlerta('Faltan datos de la mesa.', 'error');
                return;
            }

            if (btnPagar.dataset.dividido === '1' && !cuentaSeleccionadaId) {
                mostrarAlerta('Selecciona primero a la persona que vas a cobrar.', 'error');
                return;
            }

            if (montoIngresado <= 0 && totalPagar > 0) {
                mostrarAlerta('Debes ingresar el monto que estás cobrando.', 'error');
                return;
            }

            btnPagar.disabled = true;
            btnPagar.innerText = 'PROCESANDO...';

            const esEfectivo = metodo.toLowerCase() === 'efectivo';
            const referencia = (!esEfectivo && inputReferenciaUnica) ? inputReferenciaUnica.value.trim() : null;

            const payload = {
                mesa_id: inputMesa.value,
                cuenta_division_id: cuentaSeleccionadaId || null,
                pagos: [
                    { 
                        metodo: metodo.toLowerCase(), 
                        monto: montoIngresado,
                        referencia: referencia || null
                    }
                ]
            };

            await enviarPeticionPago(payload, btnPagar);
        });
    }

    if (btnConfirmarCombinado) {
        btnConfirmarCombinado.addEventListener('click', async () => {
            const inputMesa = document.getElementById('mesa-id');
            if (!inputMesa) return;

            if (btnPagar && btnPagar.dataset.dividido === '1' && !cuentaSeleccionadaId) {
                mostrarAlerta('Selecciona primero a la persona que vas a cobrar.', 'error');
                return;
            }

            btnConfirmarCombinado.disabled = true;
            btnConfirmarCombinado.innerText = 'PROCESANDO...';

            const montoEfectivo = parseFloat(cInputEfectivo.value) || 0;
            const montoTarjeta = parseFloat(cInputTarjeta.value) || 0;
            const montoTransferencia = parseFloat(cInputTransferencia.value) || 0;

            const payload = {
                mesa_id: inputMesa.value,
                cuenta_division_id: cuentaSeleccionadaId || null,
                pagos: [
                    { 
                        metodo: 'efectivo', 
                        monto: montoEfectivo, 
                        referencia: null 
                    },
                    { 
                        metodo: 'tarjeta', 
                        monto: montoTarjeta, 
                        referencia: (montoTarjeta > 0 && cInputRefTarjeta) ? cInputRefTarjeta.value.trim() : null 
                    },
                    { 
                        metodo: 'transferencia', 
                        monto: montoTransferencia, 
                        referencia: (montoTransferencia > 0 && cInputRefTransferencia) ? cInputRefTransferencia.value.trim() : null 
                    }
                ]
            };

            await enviarPeticionPago(payload, btnConfirmarCombinado);
        });
    }

    async function enviarPeticionPago(payload, botonActivo) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            mostrarAlerta('Error de seguridad: Falta el token CSRF.', 'error');
            restaurarBoton(botonActivo);
            return;
        }

        const urlEnvio = (window.COBRO_CONFIG && window.COBRO_CONFIG.urlPago) 
            ? window.COBRO_CONFIG.urlPago 
            : '/caja/procesar-pago';

        try {
            const response = await fetch(urlEnvio, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            
            if (data.success) {
                if (data.mesa_liberada) {
                    // Invoca la impresión silenciosa y redirige al finalizar
                    mostrarModalTicket(() => {
                        window.location.href = data.redirect_url || '/caja';
                    });
                } else {
                    mostrarAlerta(data.message || 'Pago registrado. Selecciona a la siguiente persona.', 'exito');
                    location.reload();
                }
            } else {
                mostrarAlerta(data.message, 'error');
                restaurarBoton(botonActivo);
            }
        } catch (error) {
            console.error('Error de red o servidor:', error);
            mostrarAlerta('Ocurrió un error al procesar el pago en el servidor.', 'error');
            restaurarBoton(botonActivo);
        }
    }

    function restaurarBoton(boton) {
        if (!boton) return;
        boton.disabled = false;
        if (boton.id === 'btn-confirmar-combinado') {
            boton.className = "w-full py-4 px-4 bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl border border-emerald-500 hover:bg-emerald-500 transition-all cursor-pointer";
            boton.innerHTML = 'Confirmar Pago Combinado';
        } else {
            boton.innerText = 'FINALIZAR';
        }
    }

    // ==========================================================================
    // 6. CONTROL DEL BOTÓN TICKET
    // ==========================================================================
    if (btnTicket) {
        btnTicket.addEventListener('click', () => {
            if (!window.COBRO_CONFIG || !window.COBRO_CONFIG.urlTicket) {
                mostrarAlerta('No se encontró información de la mesa para generar el ticket.', 'error');
                return;
            }
            mostrarModalTicket();
        });
    }

    // ==========================================================================
    // 7. SELECTOR DE PROPINA
    // ==========================================================================
    const propinaBotones = document.querySelectorAll('.propina-btn');
    const propinaManualInput = document.getElementById('propina-manual-input');
    const btnAplicarPropinaManual = document.getElementById('btn-aplicar-propina-manual');

    async function aplicarPropina(tipo, valor, botonActivo) {
        const inputOrden = document.getElementById('orden-id');
        if (!inputOrden || !inputOrden.value) {
            mostrarAlerta('No se encontró una orden activa para aplicar la propina.', 'error');
            return;
        }

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            mostrarAlerta('Error de seguridad: Falta el token CSRF.', 'error');
            return;
        }

        if (botonActivo) {
            botonActivo.disabled = true;
        }

        try {
            const response = await fetch(`/caja/orden/${inputOrden.value}/propina`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tipo, valor })
            });

            const data = await response.json();

            if (data.success) {
                aplicarPropinaAlDOM(data);
                if (tipo === 'manual' && propinaManualInput) propinaManualInput.value = '';
            } else {
                mostrarAlerta(data.message || 'No se pudo aplicar la propina.', 'error');
            }
        } catch (error) {
            console.error('Error al aplicar propina:', error);
            mostrarAlerta('Ocurrió un error al aplicar la propina.', 'error');
        } finally {
            if (botonActivo) botonActivo.disabled = false;
        }
    }

    function aplicarPropinaAlDOM(data) {
        const propinaDisplay = document.getElementById('propina-actual-display');
        if (propinaDisplay) propinaDisplay.textContent = '$' + data.propina.toLocaleString('en-US', { minimumFractionDigits: 2 });

        const propinaRow = document.getElementById('resumen-propina-row');
        const propinaValor = document.getElementById('resumen-propina');
        if (propinaValor) propinaValor.textContent = '$' + data.propina.toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (propinaRow) propinaRow.classList.toggle('hidden', data.propina <= 0);

        if (data.division) {
            aplicarDivisionAlDOM(data.division);
        }

        if (!cuentaSeleccionadaId) {
            totalPagar = data.total;
            if (totalElement) totalElement.innerText = '$' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
            if (resumenTotal) resumenTotal.textContent = '$' + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2 });
            actualizarDisplay(montoActual);
        }
    }

    propinaBotones.forEach(btn => {
        btn.addEventListener('click', () => {
            const porcentaje = parseFloat(btn.dataset.porcentaje) || 0;
            aplicarPropina('porcentaje', porcentaje, btn);
        });
    });

    if (btnAplicarPropinaManual) {
        btnAplicarPropinaManual.addEventListener('click', () => {
            const valor = parseFloat(propinaManualInput.value) || 0;
            if (valor < 0) {
                mostrarAlerta('El monto de propina no puede ser negativo.', 'error');
                return;
            }
            aplicarPropina('manual', valor, btnAplicarPropinaManual);
        });
    }
});