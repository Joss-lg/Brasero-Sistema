/**
 * Plano Espacial - Sistema de Gestión Interactivo de Mesas
 * Integra formulario modal, AJAX, y drag & drop en tiempo real
 * Compatible con Secciones / Zonas y Dropdowns nativos
 */

class PlanoEspacialMesas {
    constructor(config = {}) {
        this.config = {
            apiBase: config.apiBase || '/plano-espacial/api/mesas',
            apiGuardar: config.apiGuardar || '/plano-espacial/api/guardar',
            apiStore: config.apiStore || '/plano-espacial/api/crear',
            apiActualizar: config.apiActualizar || '/plano-espacial/api/actualizar',
            apiEliminar: config.apiEliminar || '/plano-espacial/api/eliminar',
            csrfToken: config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content,
            ...config,
        };

        this.estado = {
            modoEdicion: false,
            mesaSeleccionada: null,
            mesasOriginales: [],
            mesasActuales: [],
            filtroZona: 'todas',
            filtroEstado: 'todos',
            arrastrando: null,
            startX: 0,
            startY: 0,
            originX: 0,
            originY: 0,
            zoom: 1
        };

        this.elementos = {};
    }

    async init() {
        this.cacheElementos();
        await this.cargarMesas();
        this.setupEventos();
        this.setupZoom();
        console.log('✓ PlanoEspacialMesas inicializado');
    }

    cacheElementos() {
        this.elementos = {
            contenedor: document.getElementById('planoContenedor'),
            lienzo: document.getElementById('planoLienzo'),
            planoVacio: document.getElementById('planoVacio'),
            btnEditar: document.getElementById('btnEditar'),
            btnGuardar: document.getElementById('btnGuardar'),
            btnCancelar: document.getElementById('btnCancelar'),
            btnAgregar: document.getElementById('btnAgregarMesa'),
            modal: document.getElementById('modalCrearMesa'),
            inputNumero: document.getElementById('newNumero'),
            inputCapacidad: document.getElementById('newCapacidad'),
            inputSeccion: document.getElementById('newSeccion'),
            inputEstado: document.getElementById('newEstado'),
            btnConfirmar: document.getElementById('btnConfirmarNueva'),
            panelVacio: document.getElementById('panelVacio'),
            formularioPropiedades: document.getElementById('formularioMesa'),
            propNumero: document.getElementById('propNumero'),
            propCapacidad: document.getElementById('propCapacidad'),
            propSeccion: document.getElementById('propSeccion'),
            btnActualizar: document.getElementById('btnActualizar'),
            btnEliminar: document.getElementById('btnEliminar'),
            totalMesas: document.getElementById('totalMesas'),
            panelPropiedades: document.getElementById('panelPropiedades'),
            panelBackdrop: document.getElementById('panelBackdrop')
        };
    }

    normalizar(str) {
        return (str || '')
            .toString()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim()
            .toLowerCase();
    }

    async cargarMesas() {
        try {
            const response = await fetch(this.config.apiBase, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            const lista = Array.isArray(data) ? data : (data.data || []);

            this.estado.mesasActuales = lista;
            this.estado.mesasOriginales = JSON.parse(JSON.stringify(lista));
            this.renderizar();
        } catch (error) {
            console.error('Error al cargar mesas:', error);
            this.notificar('Error al cargar las mesas del plano', 'error');
        }
    }

    renderizar() {
        if (!this.elementos.lienzo) return;

        // Limpiar solo los elementos de mesa previos
        this.elementos.lienzo.querySelectorAll('.mesa-elemento').forEach(el => el.remove());

        const fZona = this.normalizar(this.estado.filtroZona);
        const fEstado = this.normalizar(this.estado.filtroEstado);

        const mesasFiltradas = this.estado.mesasActuales.filter(mesa => {
            const zonaMesa = this.normalizar(mesa.seccion || mesa.zona || '');
            const estadoMesa = this.normalizar(mesa.estado || '');

            const coincideZona = (fZona === 'todas' || fZona === '' || zonaMesa === fZona);

            let coincideEstado = true;
            if (fEstado === 'libre') {
                coincideEstado = (estadoMesa === 'disponible' || estadoMesa === 'libre');
            } else if (fEstado !== 'todos' && fEstado !== '') {
                coincideEstado = (estadoMesa === fEstado);
            }

            return coincideZona && coincideEstado;
        });

        if (this.elementos.planoVacio) {
            this.elementos.planoVacio.classList.toggle('hidden', mesasFiltradas.length > 0);
        }

        mesasFiltradas.forEach(mesa => {
            const elemento = this.crearElementoMesa(mesa);
            this.elementos.lienzo.appendChild(elemento);
        });

        this.actualizarConteo(mesasFiltradas.length);
    }

    crearElementoMesa(mesa) {
        const div = document.createElement('div');
        const usuarioActualId = parseInt(document.body?.dataset?.usuarioId || '0', 10) || null;

        let clasePropiedad = '';
        if (mesa.estado === 'ocupada') {
            clasePropiedad = (usuarioActualId && mesa.mesero_id === usuarioActualId)
                ? 'mesa-mia'
                : 'mesa-de-otro';
        }

        div.className = `mesa-elemento mesa-item mesa-ui absolute rounded-lg flex flex-col items-center justify-center font-bold border-2 text-[var(--text-color)] border-[var(--text-color)] mesa-${mesa.estado} ${clasePropiedad} select-none transition-shadow duration-150`;
        div.dataset.id = mesa.id;
        div.style.left = (mesa.posicion_x ?? 50) + 'px';
        div.style.top = (mesa.posicion_y ?? 50) + 'px';
        div.style.width = (mesa.ancho || 60) + 'px';
        div.style.height = (mesa.alto || 60) + 'px';
        div.style.cursor = this.estado.modoEdicion ? 'move' : 'pointer';
        div.style.touchAction = 'none';

        const seccionTexto = mesa.seccion || mesa.zona || '';
        const etiquetaSub = (this.estado.filtroZona === 'todas' && seccionTexto)
            ? `<span class="text-[8px] opacity-75 leading-none mt-0.5 tracking-tight pointer-events-none">${seccionTexto}</span>`
            : '';

        div.innerHTML = `<span class="pointer-events-none">${mesa.numero}</span>${etiquetaSub}`;

        div.addEventListener('pointerdown', (e) => {
            e.stopPropagation();
            if (this.estado.modoEdicion) {
                this.iniciarArrastre(e, div, mesa);
            }
        });

        div.addEventListener('click', (e) => {
            e.stopPropagation();
            if (this.estado.modoEdicion) {
                this.seleccionar(mesa);
            } else {
                window.location.href = `/mesero/comanda/${mesa.id}`;
            }
        });

        return div;
    }

    iniciarArrastre(e, elemento, mesa) {
        this.estado.arrastrando = mesa;
        this.estado.originX = parseInt(elemento.style.left, 10) || 0;
        this.estado.originY = parseInt(elemento.style.top, 10) || 0;
        this.estado.startX = e.clientX;
        this.estado.startY = e.clientY;

        elemento.setPointerCapture(e.pointerId);

        const onMove = (evt) => {
            if (!this.estado.arrastrando) return;
            const deltaX = (evt.clientX - this.estado.startX) / (this.estado.zoom || 1);
            const deltaY = (evt.clientY - this.estado.startY) / (this.estado.zoom || 1);

            const x = Math.max(0, Math.round(this.estado.originX + deltaX));
            const y = Math.max(0, Math.round(this.estado.originY + deltaY));

            elemento.style.left = `${x}px`;
            elemento.style.top = `${y}px`;

            mesa.posicion_x = x;
            mesa.posicion_y = y;
        };

        const onUp = (evt) => {
            elemento.removeEventListener('pointermove', onMove);
            elemento.removeEventListener('pointerup', onUp);
            elemento.removeEventListener('pointercancel', onUp);
            try { elemento.releasePointerCapture(evt.pointerId); } catch (_) {}
            this.estado.arrastrando = null;
        };

        elemento.addEventListener('pointermove', onMove);
        elemento.addEventListener('pointerup', onUp);
        elemento.addEventListener('pointercancel', onUp);
    }

    seleccionar(mesa) {
        this.estado.mesaSeleccionada = mesa;

        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-[#b74309]');
        });

        const elemento = document.querySelector(`.mesa-elemento[data-id="${mesa.id}"]`);
        if (elemento) elemento.classList.add('ring-4', 'ring-[#b74309]');

        if (this.elementos.panelVacio) this.elementos.panelVacio.classList.add('hidden');
        if (this.elementos.formularioPropiedades) this.elementos.formularioPropiedades.classList.remove('hidden');

        if (this.elementos.propNumero) this.elementos.propNumero.value = mesa.numero;
        if (this.elementos.propCapacidad) this.elementos.propCapacidad.value = mesa.capacidad;
        if (this.elementos.propSeccion) this.elementos.propSeccion.value = mesa.seccion || mesa.zona || 'Entrada';

        this.abrirPanelPropiedadesMovil();
    }

    limpiarSeleccion() {
        this.estado.mesaSeleccionada = null;
        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-[#b74309]');
        });
        if (this.elementos.formularioPropiedades) this.elementos.formularioPropiedades.classList.add('hidden');
        if (this.elementos.panelVacio) this.elementos.panelVacio.classList.remove('hidden');
        this.cerrarPanelPropiedadesMovil();
    }

    activarEdicion() {
        this.estado.modoEdicion = true;
        this.elementos.btnEditar?.classList.add('hidden');
        this.elementos.btnGuardar?.classList.remove('hidden');
        this.elementos.btnCancelar?.classList.remove('hidden');
        document.getElementById('modosEdicion')?.classList.remove('hidden');

        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.style.cursor = 'move';
        });
    }

    desactivarEdicion(restaurar = false) {
        this.estado.modoEdicion = false;
        this.elementos.btnEditar?.classList.remove('hidden');
        this.elementos.btnGuardar?.classList.add('hidden');
        this.elementos.btnCancelar?.classList.add('hidden');
        document.getElementById('modosEdicion')?.classList.add('hidden');

        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.style.cursor = 'pointer';
        });

        if (restaurar) {
            this.estado.mesasActuales = JSON.parse(JSON.stringify(this.estado.mesasOriginales));
            this.renderizar();
        }

        this.limpiarSeleccion();
    }

    async guardar() {
        try {
            const payload = {
                mesas: this.estado.mesasActuales.map(m => ({
                    id: m.id,
                    posicion_x: m.posicion_x,
                    posicion_y: m.posicion_y,
                    ancho: m.ancho,
                    alto: m.alto,
                    seccion: m.seccion || m.zona || 'Entrada',
                    zona: m.zona || m.seccion || 'Entrada'
                }))
            };

            const response = await fetch(this.config.apiGuardar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.notificar('✓ Plano guardado correctamente', 'success');
                this.estado.mesasOriginales = JSON.parse(JSON.stringify(this.estado.mesasActuales));
                this.desactivarEdicion(false);
            } else {
                this.notificar(data.message || 'Error al guardar posiciones', 'error');
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            this.notificar('Error de conexión al guardar el plano', 'error');
        }
    }

    async crearMesa() {
        const numero = this.elementos.inputNumero?.value?.trim();
        const capacidad = parseInt(this.elementos.inputCapacidad?.value, 10);
        const estado = this.elementos.inputEstado?.value || 'disponible';
        const seccion = this.elementos.inputSeccion?.value || 'Entrada';

        if (!numero) {
            this.notificar('Ingresa el número de mesa', 'error');
            return;
        }

        if (isNaN(capacidad) || capacidad < 1) {
            this.notificar('Capacidad inválida', 'error');
            return;
        }

        try {
            const response = await fetch(this.config.apiStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    numero,
                    capacidad,
                    estado,
                    seccion,
                    zona: seccion
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const nuevaMesa = data.data;
                this.estado.mesasActuales.push(nuevaMesa);
                this.estado.mesasOriginales.push(JSON.parse(JSON.stringify(nuevaMesa)));
                this.renderizar();
                this.cerrarModal();
                this.notificar(`✓ Mesa creada en ${seccion}`, 'success');
            } else {
                this.notificar(data.message || 'No se pudo crear la mesa', 'error');
            }
        } catch (error) {
            console.error('Error al crear:', error);
            this.notificar('Error al crear la mesa', 'error');
        }
    }

    async actualizarPropiedades() {
        if (!this.estado.mesaSeleccionada) return;

        const id = this.estado.mesaSeleccionada.id;
        const payload = {
            numero: this.elementos.propNumero?.value?.trim(),
            capacidad: parseInt(this.elementos.propCapacidad?.value, 10) || 1,
            seccion: this.elementos.propSeccion?.value || 'Entrada',
            zona: this.elementos.propSeccion?.value || 'Entrada'
        };

        try {
            const response = await fetch(`${this.config.apiActualizar}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                Object.assign(this.estado.mesaSeleccionada, payload);
                this.renderizar();
                this.seleccionar(this.estado.mesaSeleccionada);
                this.notificar('Propiedades actualizadas', 'success');
            } else {
                this.notificar(data.message || 'Error al actualizar', 'error');
            }
        } catch (error) {
            console.error('Error al actualizar:', error);
            this.notificar('Error al actualizar la mesa', 'error');
        }
    }

    async eliminarMesa() {
        if (!this.estado.mesaSeleccionada) return;
        const mesa = this.estado.mesaSeleccionada;

        if (!confirm(`¿Eliminar la Mesa ${mesa.numero} del plano?`)) return;

        try {
            const response = await fetch(`${this.config.apiEliminar}/${mesa.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.estado.mesasActuales = this.estado.mesasActuales.filter(m => m.id !== mesa.id);
                this.estado.mesasOriginales = this.estado.mesasOriginales.filter(m => m.id !== mesa.id);
                this.limpiarSeleccion();
                this.renderizar();
                this.notificar('Mesa eliminada correctamente', 'success');
            } else {
                this.notificar(data.message || 'No se pudo eliminar la mesa', 'error');
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            this.notificar('Error al eliminar la mesa', 'error');
        }
    }

    abrirModal() {
        if (!this.elementos.modal) return;

        const seccionInicial = (this.estado.filtroZona && this.normalizar(this.estado.filtroZona) !== 'todas')
            ? this.estado.filtroZona
            : 'Entrada';

        if (typeof window.seleccionarNewSeccion === 'function') {
            window.seleccionarNewSeccion(seccionInicial, seccionInicial);
        } else if (this.elementos.inputSeccion) {
            this.elementos.inputSeccion.value = seccionInicial;
        }

        if (this.elementos.inputNumero) this.elementos.inputNumero.value = '';
        if (this.elementos.inputCapacidad) this.elementos.inputCapacidad.value = '4';

        if (typeof window.seleccionarNewEstado === 'function') {
            window.seleccionarNewEstado('disponible', 'Disponible');
        }

        this.elementos.modal.classList.remove('hidden');
        this.elementos.inputNumero?.focus();
    }

    cerrarModal() {
        if (!this.elementos.modal) return;
        this.elementos.modal.classList.add('hidden');
        document.getElementById('menuNewSeccion')?.classList.add('hidden');
        document.getElementById('iconoNewSeccion')?.classList.remove('rotate-180');
        document.getElementById('menuNewEstado')?.classList.add('hidden');
        document.getElementById('iconoNewEstado')?.classList.remove('rotate-180');
    }

    abrirPanelPropiedadesMovil() {
        this.elementos.panelPropiedades?.classList.remove('translate-y-full');
        this.elementos.panelPropiedades?.classList.add('translate-y-0');
        this.elementos.panelBackdrop?.classList.remove('hidden');
    }

    cerrarPanelPropiedadesMovil() {
        this.elementos.panelPropiedades?.classList.add('translate-y-full');
        this.elementos.panelPropiedades?.classList.remove('translate-y-0');
        this.elementos.panelBackdrop?.classList.add('hidden');
    }

    actualizarConteo(total) {
        if (this.elementos.totalMesas) {
            this.elementos.totalMesas.textContent = `Mesas: ${total}`;
        }
    }

    setupZoom() {
        const aplicar = () => {
            if (this.elementos.lienzo) {
                this.elementos.lienzo.style.transform = `scale(${this.estado.zoom})`;
                this.elementos.lienzo.style.transformOrigin = 'top left';
            }
            const label = document.getElementById('zoomLabel');
            if (label) label.innerText = `${Math.round(this.estado.zoom * 100)}%`;
        };

        if (window.innerWidth < 640) this.estado.zoom = 0.6;
        aplicar();

        document.getElementById('btnZoomIn')?.addEventListener('click', () => {
            this.estado.zoom = Math.min(1.5, Math.round((this.estado.zoom + 0.15) * 100) / 100);
            aplicar();
        });

        document.getElementById('btnZoomOut')?.addEventListener('click', () => {
            this.estado.zoom = Math.max(0.4, Math.round((this.estado.zoom - 0.15) * 100) / 100);
            aplicar();
        });

        document.getElementById('btnZoomReset')?.addEventListener('click', () => {
            this.estado.zoom = window.innerWidth < 640 ? 0.6 : 1;
            aplicar();
        });
    }

    notificar(mensaje, tipo = 'info') {
        const notif = document.getElementById('notificacion');
        if (!notif) return;

        notif.textContent = mensaje;
        const clases = {
            'success': 'bg-emerald-600',
            'error': 'bg-rose-600',
            'info': 'bg-[#b74309]',
        };
        notif.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 transition-all shadow-xl ${clases[tipo] || clases.info}`;
        notif.classList.remove('hidden');

        clearTimeout(this._timeoutNotif);
        this._timeoutNotif = setTimeout(() => notif.classList.add('hidden'), 3000);
    }

    setupEventos() {
        this.elementos.btnEditar?.addEventListener('click', () => this.activarEdicion());
        this.elementos.btnGuardar?.addEventListener('click', () => this.guardar());
        this.elementos.btnCancelar?.addEventListener('click', () => this.desactivarEdicion(true));
        this.elementos.btnAgregar?.addEventListener('click', () => this.abrirModal());
        this.elementos.btnConfirmar?.addEventListener('click', () => this.crearMesa());

        document.querySelectorAll('.btnCerrarModal').forEach(btn => {
            btn.addEventListener('click', () => this.cerrarModal());
        });

        document.getElementById('btnCerrarPanelMovil')?.addEventListener('click', () => this.cerrarPanelPropiedadesMovil());
        this.elementos.panelBackdrop?.addEventListener('click', () => this.cerrarPanelPropiedadesMovil());

        this.elementos.btnActualizar?.addEventListener('click', () => this.actualizarPropiedades());
        this.elementos.btnEliminar?.addEventListener('click', () => this.eliminarMesa());

        // Conectar funciones de filtrado con los dropdowns del Blade
        window.filtrarPorSeccion = (zona) => {
            this.estado.filtroZona = zona || 'todas';
            this.renderizar();
        };

        window.filtrarMesasPorEstado = (estado) => {
            this.estado.filtroEstado = estado || 'todos';
            this.renderizar();
        };

        // Polling en tiempo real (cada 5s si no está editando)
        setInterval(() => {
            if (!this.estado.modoEdicion && !this.estado.arrastrando) {
                this.cargarMesas();
            }
        }, 5000);
    }
}

// Inicialización automática
document.addEventListener('DOMContentLoaded', () => {
    window.gestorPlano = new PlanoEspacialMesas();
    window.gestorPlano.init();
});