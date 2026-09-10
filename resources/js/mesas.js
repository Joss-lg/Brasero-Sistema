// Id del usuario en sesion. Lo publica el blade en <body data-usuario-id>.
const USUARIO_ACTUAL_ID = parseInt(document.body?.dataset?.usuarioId || '0', 10) || null;

/**
 * mesas.js - Sistema centralizado de gestión de mesas
 */

let estadoGlobal = {
    mesas: [],
    filtroActual: 'todos',
    seccionFiltro: 'todas',
    vista: 'mapa',
    modoEdicion: false,
    modoFusion: false,
    mesaSeleccionada: null,
    primeraCarga: true,
    zoom: 1
};

const ZOOM_MIN = 0.4;
const ZOOM_MAX = 1.5;
const ZOOM_STEP = 0.15;

const permisosMesas = window.permisosMesas || { crear: false, editar: false, eliminar: false };

let dragState = {
    activo: false,
    elemento: null,
    mesaId: null,
    originX: 0,
    originY: 0,
    startX: 0,
    startY: 0,
    contenedor: null
};

// Función para normalizar texto (ignora mayúsculas, minúsculas y acentos)
function normalizar(str) {
    return (str || '')
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLowerCase();
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth < 640) {
        estadoGlobal.zoom = 0.6;
    }
    aplicarZoom();

    cargarMesas();
    inicializarArrastrePlano();

    setInterval(() => {
        if (!estadoGlobal.modoEdicion && !estadoGlobal.modoFusion) cargarMesas();
    }, 5000);

    document.getElementById('btnEditar')?.addEventListener('click', toggleModoEdicion);
    document.getElementById('btnGuardar')?.addEventListener('click', guardarPlanoEnServidor);
    document.getElementById('btnCancelar')?.addEventListener('click', cancelarEdicion);

    document.getElementById('btnAgregarMesa')?.addEventListener('click', abrirModalNuevaMesa);
    document.getElementById('btnConfirmarNueva')?.addEventListener('click', crearNuevaMesa);
    document.getElementById('btnEliminar')?.addEventListener('click', eliminarMesaDelPlano);
    document.getElementById('btnActualizar')?.addEventListener('click', actualizarPropiedadesMesa);

    document.querySelectorAll('.btnCerrarModal').forEach(btn => {
        btn.addEventListener('click', cerrarModalNuevaMesa);
    });

    document.getElementById('btnCerrarPanelMovil')?.addEventListener('click', cerrarPanelPropiedadesMovil);
    document.getElementById('panelBackdrop')?.addEventListener('click', cerrarPanelPropiedadesMovil);

    // Zoom
    document.getElementById('btnZoomIn')?.addEventListener('click', () => cambiarZoom(ZOOM_STEP));
    document.getElementById('btnZoomOut')?.addEventListener('click', () => cambiarZoom(-ZOOM_STEP));
    document.getElementById('btnZoomReset')?.addEventListener('click', () => {
        estadoGlobal.zoom = window.innerWidth < 640 ? 0.6 : 1;
        aplicarZoom();
    });
});

// --- DESPLAZAMIENTO DEL PLANO (DRAG-TO-SCROLL) ---
function inicializarArrastrePlano() {
    const contenedor = document.getElementById('planoContenedor');
    if (!contenedor) return;

    let isPanning = false;
    let startX = 0;
    let startY = 0;
    let initialScrollLeft = 0;
    let initialScrollTop = 0;

    contenedor.addEventListener('pointerdown', (e) => {
        // Bloquear completamente el paneo si se toca una mesa o cualquier elemento interactivo
        if (e.target.closest('.mesa-elemento, .mesa-item, .mesa-ui, input, select, button')) return;

        isPanning = true;
        startX = e.clientX;
        startY = e.clientY;
        initialScrollLeft = contenedor.scrollLeft;
        initialScrollTop = contenedor.scrollTop;
    });

    contenedor.addEventListener('pointermove', (e) => {
        if (!isPanning) return;
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;

        if (Math.abs(deltaX) > 2 || Math.abs(deltaY) > 2) {
            contenedor.style.cursor = 'grabbing';
            e.preventDefault();
            contenedor.scrollLeft = initialScrollLeft - deltaX;
            contenedor.scrollTop = initialScrollTop - deltaY;
        }
    });

    const finalizarPaneo = () => {
        if (!isPanning) return;
        isPanning = false;
        contenedor.style.cursor = 'grab';
    };

    contenedor.addEventListener('pointerup', finalizarPaneo);
    contenedor.addEventListener('pointercancel', finalizarPaneo);
    window.addEventListener('pointerup', finalizarPaneo);
}

// --- ZOOM ---
function cambiarZoom(delta) {
    const nuevoZoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, estadoGlobal.zoom + delta));
    estadoGlobal.zoom = Math.round(nuevoZoom * 100) / 100;
    aplicarZoom();
}

function aplicarZoom() {
    const lienzo = document.getElementById('planoLienzo');
    const label = document.getElementById('zoomLabel');
    if (lienzo) {
        lienzo.style.transform = `scale(${estadoGlobal.zoom})`;
        lienzo.style.transformOrigin = 'top left';
    }
    if (label) label.innerText = `${Math.round(estadoGlobal.zoom * 100)}%`;
}

// --- CARGA Y RENDERIZADO ---
async function cargarMesas() {
    try {
        const res = await fetch('/plano-espacial/api/mesas');
        if (!res.ok) {
            console.error('Error HTTP al consultar API:', res.status);
            return;
        }

        const response = await res.json();
        
        // Soporta { data: [...] }, { success: true, data: [...] } o [...] directamente
        if (Array.isArray(response)) {
            estadoGlobal.mesas = response;
        } else if (Array.isArray(response.data)) {
            estadoGlobal.mesas = response.data;
        } else if (Array.isArray(response.mesas)) {
            estadoGlobal.mesas = response.mesas;
        } else {
            estadoGlobal.mesas = [];
        }

        renderizarMapaMesas();
    } catch (e) {
        console.error('Error al cargar mesas:', e);
    }
}

function renderizarMapaMesas() {
    const lienzo = document.getElementById('planoLienzo');
    if (!lienzo) return;

    lienzo.querySelectorAll('.mesa-elemento').forEach(el => el.remove());

    const fSec = normalizar(estadoGlobal.seccionFiltro);
    const fEst = normalizar(estadoGlobal.filtroActual);

    // Filtrar con validación robusta
    const mesasFiltradas = estadoGlobal.mesas.filter(mesa => {
        const seccionMesa = normalizar(mesa.seccion || mesa.zona || '');
        const estadoMesa = normalizar(mesa.estado || '');

        // 1. Filtro Sección
        const coincideSeccion = (fSec === 'todas' || fSec === '' || seccionMesa === fSec);

        // 2. Filtro Estado
        let coincideEstado = true;
        if (fEst === 'libre') {
            coincideEstado = (estadoMesa === 'disponible' || estadoMesa === 'libre');
        } else if (fEst !== 'todos' && fEst !== '') {
            coincideEstado = (estadoMesa === fEst);
        }

        return coincideSeccion && coincideEstado;
    });

    const planoVacio = document.getElementById('planoVacio');
    if (mesasFiltradas.length === 0) {
        planoVacio?.classList.remove('hidden');
    } else {
        planoVacio?.classList.add('hidden');
    }

    mesasFiltradas.forEach(mesa => {
        const div = document.createElement('div');

        let clasePropiedad = '';
        if (mesa.estado === 'ocupada') {
            clasePropiedad = (USUARIO_ACTUAL_ID && mesa.mesero_id === USUARIO_ACTUAL_ID)
                ? 'mesa-mia'
                : 'mesa-de-otro';
        }

        div.className = `mesa-elemento mesa-item mesa-ui absolute rounded-lg flex flex-col items-center justify-center font-bold border-2 text-[var(--text-color)] border-[var(--text-color)] mesa-${mesa.estado} ${clasePropiedad} select-none transition-shadow duration-150`;

        div.dataset.id = mesa.id;
        div.dataset.seccion = mesa.seccion || mesa.zona || 'Entrada';
        div.dataset.estado = mesa.estado || 'disponible';
        
        div.style.left = (mesa.posicion_x !== null ? mesa.posicion_x : 50) + 'px';
        div.style.top = (mesa.posicion_y !== null ? mesa.posicion_y : 50) + 'px';
        div.style.width = (mesa.ancho || 60) + 'px';
        div.style.height = (mesa.alto || 60) + 'px';
        div.style.cursor = estadoGlobal.modoEdicion ? 'move' : 'pointer';
        div.style.touchAction = 'none';
        
        const etiquetaSeccion = (mesa.seccion) 
            ? `<span class="text-[8px] opacity-75 leading-none mt-0.5 tracking-tight">${mesa.seccion}</span>` 
            : '';

        div.innerHTML = `<span>${mesa.numero}</span>${etiquetaSeccion}`;

        // Evento Pointerdown: detener propagación para que el plano no se mueva
        div.addEventListener('pointerdown', (e) => {
            e.stopPropagation();
            if (estadoGlobal.modoEdicion && permisosMesas.editar) {
                iniciarArrastre(e, div, mesa);
            }
        });

        div.addEventListener('click', (e) => {
            e.stopPropagation();
            if (estadoGlobal.modoEdicion) {
                seleccionarMesa(mesa);
            } else {
                window.location.href = `/mesero/comanda/${mesa.id}`;
            }
        });

        lienzo.appendChild(div);
    });

    const total = document.getElementById('totalMesas');
    if (total) total.innerText = `Mesas: ${mesasFiltradas.length}`;
}

// --- FUNCIONES GLOBALES DE FILTRADO ---
window.filtrarPorSeccion = function (seccion, btn) {
    estadoGlobal.seccionFiltro = seccion || 'todas';
    renderizarMapaMesas();
};

window.filtrarMesasPorEstado = function (estado, btn) {
    estadoGlobal.filtroActual = estado || 'todos';
    renderizarMapaMesas();
};

// --- LÓGICA DE ARRASTRE DE MESAS ---
function iniciarArrastre(e, el, mesa) {
    if (!estadoGlobal.modoEdicion) return;
    dragState = {
        activo: true,
        elemento: el,
        mesaId: mesa.id,
        originX: parseInt(el.style.left, 10) || 0,
        originY: parseInt(el.style.top, 10) || 0,
        startX: e.clientX,
        startY: e.clientY,
        contenedor: document.getElementById('planoContenedor')
    };
    el.setPointerCapture(e.pointerId);
    el.addEventListener('pointermove', manejarArrastre);
    el.addEventListener('pointerup', detenerArrastre);
    el.addEventListener('pointercancel', detenerArrastre);
}

function manejarArrastre(e) {
    if (!dragState.activo) return;
    const zoom = estadoGlobal.zoom || 1;
    const x = (e.clientX - dragState.startX) / zoom + dragState.originX;
    const y = (e.clientY - dragState.startY) / zoom + dragState.originY;
    dragState.elemento.style.left = Math.max(0, x) + 'px';
    dragState.elemento.style.top = Math.max(0, y) + 'px';
}

function detenerArrastre(e) {
    if (!dragState.activo) return;
    const mesa = estadoGlobal.mesas.find(m => m.id === dragState.mesaId);
    if (mesa) {
        mesa.posicion_x = parseInt(dragState.elemento.style.left, 10);
        mesa.posicion_y = parseInt(dragState.elemento.style.top, 10);
    }
    dragState.elemento?.removeEventListener('pointermove', manejarArrastre);
    dragState.elemento?.removeEventListener('pointerup', detenerArrastre);
    dragState.elemento?.removeEventListener('pointercancel', detenerArrastre);
    try {
        dragState.elemento?.releasePointerCapture(e.pointerId);
    } catch (_) {}
    dragState.activo = false;
}

function deseleccionarMesa() {
    estadoGlobal.mesaSeleccionada = null;
    document.getElementById('formularioMesa')?.classList.add('hidden');
    document.getElementById('panelVacio')?.classList.remove('hidden');
}

window.toggleModoEdicion = () => {
    estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
    document.getElementById('modosEdicion')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnGuardar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnCancelar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    
    document.querySelectorAll('.mesa-elemento').forEach(el => {
        el.style.cursor = estadoGlobal.modoEdicion ? 'move' : 'pointer';
    });

    if (!estadoGlobal.modoEdicion) {
        deseleccionarMesa();
        cerrarPanelPropiedadesMovil();
    }
};

window.abrirModalNuevaMesa = () => {
    const modal = document.getElementById('modalCrearMesa') || document.getElementById('modalNuevaMesa');
    if (!modal) return;

    const seccionInicial = (estadoGlobal.seccionFiltro && normalizar(estadoGlobal.seccionFiltro) !== 'todas') 
        ? estadoGlobal.seccionFiltro 
        : 'Entrada';

    if (typeof window.seleccionarNewSeccion === 'function') {
        window.seleccionarNewSeccion(seccionInicial, seccionInicial);
    } else {
        const inputSeccion = document.getElementById('newSeccion');
        if (inputSeccion) inputSeccion.value = seccionInicial;
    }

    const inputNumero = document.getElementById('newNumero');
    const inputCapacidad = document.getElementById('newCapacidad');
    if (inputNumero) inputNumero.value = '';
    if (inputCapacidad) inputCapacidad.value = '';

    if (typeof window.seleccionarNewEstado === 'function') {
        window.seleccionarNewEstado('disponible', 'Disponible');
    }

    modal.classList.remove('hidden');
    modal.classList.remove('opacity-0');
};

window.cerrarModalNuevaMesa = () => {
    const modal = document.getElementById('modalCrearMesa') || document.getElementById('modalNuevaMesa');
    if (modal) modal.classList.add('hidden');

    document.getElementById('menuNewSeccion')?.classList.add('hidden');
    document.getElementById('iconoNewSeccion')?.classList.remove('rotate-180');
    document.getElementById('menuNewEstado')?.classList.add('hidden');
    document.getElementById('iconoNewEstado')?.classList.remove('rotate-180');
};

window.crearNuevaMesa = async () => {
    if (!permisosMesas.crear) {
        showToast('No tienes permiso para crear mesas', 'error');
        return;
    }

    const estadoElemento = document.getElementById('newEstado');
    const estadoRaw = estadoElemento?.value || 'disponible';

    const inputSeccion = document.getElementById('newSeccion');
    let seccionRaw = inputSeccion?.value?.trim();

    if (!seccionRaw) {
        seccionRaw = (estadoGlobal.seccionFiltro && normalizar(estadoGlobal.seccionFiltro) !== 'todas') 
            ? estadoGlobal.seccionFiltro 
            : 'Entrada';
    }

    const numero = document.getElementById('newNumero')?.value?.trim();
    const capacidad = parseInt(document.getElementById('newCapacidad')?.value, 10) || 1;

    if (!numero) {
        showToast('Ingresa el número de mesa', 'error');
        return;
    }

    const data = {
        numero: numero,
        capacidad: capacidad,
        seccion: seccionRaw,
        estado: estadoRaw.toLowerCase()
    };

    try {
        const res = await fetch('/plano-espacial/api/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            window.cerrarModalNuevaMesa();
            cargarMesas();
            showToast(`Mesa creada en ${seccionRaw}`, 'success');
        } else {
            const errorData = await res.json().catch(() => null);
            showToast(errorData?.message || 'Error al crear la mesa', 'error');
        }
    } catch (e) {
        console.error('Error al crear mesa:', e);
        showToast('Error al crear la mesa', 'error');
    }
};

function seleccionarMesa(mesa) {
    estadoGlobal.mesaSeleccionada = mesa;
    document.getElementById('panelVacio')?.classList.add('hidden');
    document.getElementById('formularioMesa')?.classList.remove('hidden');

    const inputNumero = document.getElementById('propNumero');
    const inputCapacidad = document.getElementById('propCapacidad');
    const selectSeccion = document.getElementById('propSeccion');

    if (inputNumero) inputNumero.value = mesa.numero;
    if (inputCapacidad) inputCapacidad.value = mesa.capacidad;
    if (selectSeccion) selectSeccion.value = mesa.seccion || 'Entrada';

    document.getElementById('btnActualizar')?.classList.remove('hidden');
    document.getElementById('btnEliminar')?.classList.remove('hidden');
    abrirPanelPropiedadesMovil();
}

function abrirPanelPropiedadesMovil() {
    const panel = document.getElementById('panelPropiedades');
    const backdrop = document.getElementById('panelBackdrop');
    panel?.classList.remove('translate-y-full');
    panel?.classList.add('translate-y-0');
    backdrop?.classList.remove('hidden');
}

function cerrarPanelPropiedadesMovil() {
    const panel = document.getElementById('panelPropiedades');
    const backdrop = document.getElementById('panelBackdrop');
    panel?.classList.add('translate-y-full');
    panel?.classList.remove('translate-y-0');
    backdrop?.classList.add('hidden');
}
window.cerrarPanelPropiedadesMovil = cerrarPanelPropiedadesMovil;

async function guardarPlanoEnServidor() {
    if (!permisosMesas.editar) {
        showToast('No tienes permiso para editar mesas', 'error');
        return;
    }

    try {
        const res = await fetch('/plano-espacial/api/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ mesas: estadoGlobal.mesas })
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Respuesta del servidor no exitosa:', errorText);
            showToast('Error al guardar el plano', 'error');
            return;
        }

        showToast('Plano guardado correctamente', 'success');
    } catch (e) {
        console.error('Error en la petición:', e);
        showToast('Error al guardar el plano', 'error');
    }
}

function cancelarEdicion() {
    showConfirm('¿Deseas descartar los cambios sin guardar?', () => {
        estadoGlobal.modoEdicion = false;
        document.getElementById('btnGuardar')?.classList.add('hidden');
        document.getElementById('btnCancelar')?.classList.add('hidden');
        document.getElementById('modosEdicion')?.classList.add('hidden');
        deseleccionarMesa();
        cerrarPanelPropiedadesMovil();
        cargarMesas();
    }, { titulo: '¿Descartar cambios?', textoConfirmar: 'Descartar' });
}

window.actualizarPropiedadesMesa = async () => {
    if (!estadoGlobal.mesaSeleccionada) return;

    if (!permisosMesas.editar) {
        showToast('No tienes permiso para editar mesas', 'error');
        return;
    }

    const data = {
        numero: document.getElementById('propNumero')?.value,
        capacidad: document.getElementById('propCapacidad')?.value,
        seccion: document.getElementById('propSeccion')?.value || 'Entrada'
    };

    try {
        const res = await fetch(`/plano-espacial/api/actualizar/${estadoGlobal.mesaSeleccionada.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            showToast('Propiedades actualizadas', 'success');
            cargarMesas();
        } else {
            showToast('Error al actualizar la mesa', 'error');
        }
    } catch (e) {
        console.error('Error:', e);
        showToast('Error al actualizar la mesa', 'error');
    }
};

window.eliminarMesaDelPlano = () => {
    if (!estadoGlobal.mesaSeleccionada) return;

    if (!permisosMesas.eliminar) {
        showToast('No tienes permiso para eliminar mesas', 'error');
        return;
    }

    showConfirm('¿Estás seguro de eliminar esta mesa?', async () => {
        try {
            const res = await fetch(`/plano-espacial/api/eliminar/${estadoGlobal.mesaSeleccionada.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (res.ok) {
                showToast('Mesa eliminada correctamente', 'success');
                deseleccionarMesa();
                cerrarPanelPropiedadesMovil();
                cargarMesas();
            } else {
                const errorData = await res.json().catch(() => null);
                showToast(errorData?.message || 'Error al eliminar la mesa', 'error');
            }
        } catch (e) {
            console.error('Error al eliminar mesa:', e);
            showToast('Error al eliminar la mesa', 'error');
        }
    }, { titulo: 'Eliminar mesa', textoConfirmar: 'Eliminar' });
};