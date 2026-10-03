/**
 * comanda-cancelacion.js
 * Cancelación de productos ya enviados a cocina, con doble candado:
 * confirmación (modal propio #modalConfirmarCancelacion, sin diálogo
 * nativo del navegador) + autorización por NIP de Capitán/Administrador
 * (modal propio #modalNipCancelacion, independiente del #modalNip de
 * Capitán/Traspaso en comanda-capitan-traspaso.js).
 * Depende de funciones genéricas de comanda-core.js: escribirNumVirtual,
 * borrarNumVirtual, mostrarError, mostrarExito, csrfToken.
 */
(function () {
    let detalleIdPendiente = null;
    let botonPendiente = null;

    function construirUrlCancelar(detalleId) {
        const config = window.ComandaConfig || {};
        const base = (config.rutas && config.rutas.comandaCancelarDetalle) || '/mesero/comanda/detalle/0/cancelar';
        return base.replace('/0/cancelar', `/${detalleId}/cancelar`);
    }

    // Punto de entrada: lo llama el botón de basurero en ticket-sidebar.blade.php.
    // Ya NO usa confirm() nativo: abre el modal propio de confirmación.
    window.cancelarProductoEnviado = function (detalleId, btn) {
        detalleIdPendiente = detalleId;
        botonPendiente = btn;

        const modal = document.getElementById('modalConfirmarCancelacion');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };

    window.cerrarModalConfirmarCancelacion = function () {
        const modal = document.getElementById('modalConfirmarCancelacion');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        detalleIdPendiente = null;
        botonPendiente = null;
    };

    // Se llama al tocar "Sí, cancelar" en el modal de confirmación.
    // Cierra ese modal y abre el de NIP, conservando detalleIdPendiente/botonPendiente.
    window.continuarCancelacionConNip = function () {
        const modalConfirmar = document.getElementById('modalConfirmarCancelacion');
        if (modalConfirmar) {
            modalConfirmar.classList.add('hidden');
            modalConfirmar.classList.remove('flex');
        }

        if (!detalleIdPendiente) return;

        // Limpiar campos del modal de NIP
        const inputNip = document.getElementById('nipCancelacionInput');
        if (inputNip) inputNip.value = '';

        const inputMotivo = document.getElementById('motivoCancelacionInput');
        if (inputMotivo) inputMotivo.value = '';

        const errorMotivo = document.getElementById('motivoCancelacionError');
        if (errorMotivo) errorMotivo.classList.add('hidden');

        const modalNip = document.getElementById('modalNipCancelacion');
        if (modalNip) {
            modalNip.classList.remove('hidden');
            modalNip.classList.add('flex');
        }
        // Enfocar el motivo primero (es lo que hay que llenar antes del NIP)
        setTimeout(() => { if (inputMotivo) inputMotivo.focus(); }, 100);
    };

    window.cerrarModalCancelacion = function () {
        const modal = document.getElementById('modalNipCancelacion');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        // Limpiar campos
        const inputMotivo = document.getElementById('motivoCancelacionInput');
        if (inputMotivo) inputMotivo.value = '';
        const errorMotivo = document.getElementById('motivoCancelacionError');
        if (errorMotivo) errorMotivo.classList.add('hidden');
        detalleIdPendiente = null;
        botonPendiente = null;
    };

    // Enter dentro del input de NIP también confirma
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('nipCancelacionInput');
        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    confirmarNipCancelacion();
                }
            });
        }
    });

    window.confirmarNipCancelacion = function () {
        // Validar motivo obligatorio antes de proceder
        const inputMotivo = document.getElementById('motivoCancelacionInput');
        const motivo = inputMotivo ? inputMotivo.value.trim() : '';
        const errorMotivo = document.getElementById('motivoCancelacionError');

        if (!motivo || motivo.length < 3) {
            if (errorMotivo) errorMotivo.classList.remove('hidden');
            if (inputMotivo) inputMotivo.focus();
            return;
        }
        if (errorMotivo) errorMotivo.classList.add('hidden');

        const input = document.getElementById('nipCancelacionInput');
        const nip = input ? input.value.trim() : '';

        if (!nip) { mostrarError('Ingresa el NIP de autorización.'); return; }
        if (!detalleIdPendiente) { cerrarModalCancelacion(); return; }

        const detalleId = detalleIdPendiente;
        const btn = botonPendiente;
        const btnConfirmar = document.getElementById('btnConfirmarNipCancelacion');

        if (btnConfirmar) { btnConfirmar.disabled = true; btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
        if (btn) btn.disabled = true;

        fetch(construirUrlCancelar(detalleId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nip: nip, motivo: motivo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarExito(data.message || 'Producto cancelado correctamente.');
                cerrarModalCancelacion();
                setTimeout(() => window.location.reload(), 800);
            } else {
                mostrarError(data.message || 'No se pudo cancelar el producto.');
                if (btn) btn.disabled = false;
            }
        })
        .catch(() => {
            mostrarError('Error de conexión al cancelar.');
            if (btn) btn.disabled = false;
        })
        .finally(() => {
            if (btnConfirmar) { btnConfirmar.disabled = false; btnConfirmar.innerHTML = 'Autorizar Cancelación'; }
        });
    };
})();