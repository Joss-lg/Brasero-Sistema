<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #editEmpleadoModal {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #editModalContent {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }

    #edit-rol-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes dropdownOpen {
        from { opacity: 0; transform: scaleY(0.9) translateY(-8px); }
        to   { opacity: 1; transform: scaleY(1) translateY(0); }
    }
</style>

<div id="editEmpleadoModal" class="fixed inset-0 bg-black/75 backdrop-blur-md z-[99999] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    
    <div class="relative bg-white dark:bg-[#121318] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 sm:p-8 md:p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500 shadow-2xl flex flex-col max-h-[85vh] sm:max-h-[90vh]" id="editModalContent">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#b74309]/10 dark:bg-[#b74309]/20 rounded-full blur-3xl pointer-events-none"></div>

        <button type="button" onclick="window.cerrarEditModal()" class="absolute top-5 right-5 sm:top-8 sm:right-8 text-gray-400 hover:text-gray-900 dark:hover:text-white hover:rotate-90 transition-all duration-300 outline-none z-50 bg-gray-100 dark:bg-zinc-800 sm:bg-transparent rounded-full w-9 h-9 flex items-center justify-center cursor-pointer">
            <i class="fas fa-times text-lg sm:text-xl pointer-events-none"></i>
        </button>

        <div class="mb-6 sm:mb-8 relative z-10 flex-shrink-0 pt-2 sm:pt-0">
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tighter">Editar Perfil</h2>
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-1 sm:mt-2">Actualización de credenciales</p>
        </div>

        <form id="formEditar" action="#" method="POST" class="space-y-5 sm:space-y-6 overflow-y-auto flex-1 pb-2 overscroll-contain scrollbar-thin" style="-webkit-overflow-scrolling: touch;">
            @csrf
            @method('PUT')
            
            {{-- AVISO DE PROTECCIÓN --}}
            <div id="alertaProteccion" class="hidden mb-4 sm:mb-6 p-3 sm:p-4 bg-[#b74309]/10 dark:bg-[#b74309]/10 border border-[#b74309]/20 dark:border-[#b74309]/20 rounded-xl sm:rounded-2xl flex items-start gap-3">
                <i class="fas fa-shield-check text-[#b74309] dark:text-[#e8946a] text-base sm:text-lg mt-0.5"></i>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-black text-[#b74309] dark:text-[#e8946a] uppercase tracking-widest">Protección de Cuenta</p>
                    <p class="text-[11px] sm:text-xs font-medium text-[#b74309]/80 dark:text-[#e8946a]/80 mt-1">Por seguridad, no puedes quitarte el acceso ni cambiarte tu propio rol.</p>
                </div>
            </div>

            {{-- Nombre --}}
            <div class="group">
                <label for="edit_nombre" class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">Nombre Completo</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#b74309] transition-colors"></i>
                    <input type="text" id="edit_nombre" name="nombre" data-teclado="texto" required 
                        class="w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-11 pr-4 sm:pr-6 text-sm font-bold text-gray-900 dark:text-white outline-none focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300">
                </div>
            </div>

            {{-- Switch de Acceso --}}
            <div id="cajaAcceso" class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 dark:bg-black/40 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300 group">
                <div class="flex flex-col pointer-events-none pr-4">
                    <span class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em]">Acceso al Sistema</span>
                    <p class="text-[8px] sm:text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">¿Este empleado usará la plataforma?</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" id="edit_toggleAcceso" name="puede_acceder_pos" value="1" onchange="toggleEditAccesoFields()" class="sr-only peer">
                    <div class="w-11 sm:w-12 h-6 bg-gray-300 dark:bg-zinc-700 rounded-full peer 
                                peer-checked:after:translate-x-[20px] sm:peer-checked:after:translate-x-[24px] peer-checked:after:border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                after:bg-white after:border border-gray-300 dark:after:border-zinc-600 
                                after:rounded-full after:h-5 after:w-5 
                                after:transition-all after:duration-300 
                                peer-active:after:w-6 
                                peer-checked:bg-[#b74309] peer-checked:shadow-[0_0_12px_rgba(183,67,9,0.4)]
                                transition-all duration-300"></div>
                </label>
            </div>

            {{-- Contenedor Condicional (PIN y ROL) --}}
            <div id="edit_accesoFields" class="hidden space-y-5 sm:space-y-6 transition-all duration-500">
                
                {{-- PIN --}}
                <div class="group">
                    <label for="edit_codigo_empleado" class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">PIN de Seguridad (4 dígitos)</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#b74309] transition-colors"></i>
                        <input type="text" id="edit_codigo_empleado" name="codigo_empleado" data-teclado="numerico" maxlength="4" 
                            class="w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-11 pr-4 sm:pr-6 font-black tracking-[0.8em] text-base sm:text-lg text-gray-900 dark:text-white outline-none focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300">
                    </div>
                </div>

                {{-- Rol (Dropdown personalizado) --}}
                <div class="relative group" id="cajaRol">
                    <label class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">Rol del Sistema</label>
                    <input type="hidden" name="rol_id" id="edit_rol_id_input">

                    <div class="relative" id="edit-rol-wrapper">
                        <button type="button" id="edit-rol-trigger"
                            onclick="toggleEditRolDropdown()"
                            class="w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-4 pr-4 sm:pr-6 flex items-center justify-between gap-3 transition-all outline-none hover:border-[#b74309]/50 focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div id="edit-rol-icon" class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-gray-200 dark:bg-gray-700 transition-all">
                                    <i class="fas fa-shield-alt text-gray-400 text-xs"></i>
                                </div>
                                <span id="edit-rol-label" class="text-sm font-semibold text-gray-400 dark:text-gray-500 truncate">Seleccionar...</span>
                            </div>
                            <i id="edit-rol-chevron" class="fas fa-chevron-down text-gray-400 text-[10px] transition-transform duration-200 flex-shrink-0"></i>
                        </button>

                        <div id="edit-rol-dropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#1a1d20] border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="p-2 space-y-1" id="edit-rol-options"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-between items-center mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-100 dark:border-white/5 flex-shrink-0">
                <button type="button" onclick="window.cerrarEditModal()" class="px-2 py-3 sm:py-4 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit" class="px-6 sm:px-10 py-3 sm:py-4 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest bg-[#b74309] hover:bg-[#8f3207] text-white shadow-lg shadow-[#b74309]/20 hover:-translate-y-0.5 active:translate-y-0 active:scale-95 transition-all duration-300 outline-none">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const idUsuarioActual = {{ auth()->id() ?? 'null' }};

    // --- VISUAL POR ROL ---
    const editRolVisual = {
        'administrador': { icon: 'fa-user-shield',    bg: 'bg-rose-100 dark:bg-rose-500/20',         text: 'text-rose-600 dark:text-rose-400',       dot: 'bg-rose-500' },
        'cajero':        { icon: 'fa-cash-register',  bg: 'bg-purple-100 dark:bg-purple-500/20',      text: 'text-purple-600 dark:text-purple-400',   dot: 'bg-purple-500' },
        'capitán':       { icon: 'fa-clipboard-list', bg: 'bg-[#b74309]/10 dark:bg-[#b74309]/20',     text: 'text-[#b74309] dark:text-[#e8946a]',     dot: 'bg-[#b74309]' },
        'capitan':       { icon: 'fa-clipboard-list', bg: 'bg-[#b74309]/10 dark:bg-[#b74309]/20',     text: 'text-[#b74309] dark:text-[#e8946a]',     dot: 'bg-[#b74309]' },
        'mesero':        { icon: 'fa-concierge-bell', bg: 'bg-emerald-100 dark:bg-emerald-500/20',    text: 'text-emerald-600 dark:text-emerald-400', dot: 'bg-emerald-500' },
        'cocinero':      { icon: 'fa-fire',           bg: 'bg-orange-100 dark:bg-orange-500/20',      text: 'text-orange-600 dark:text-orange-400',   dot: 'bg-orange-500' },
        'default':       { icon: 'fa-user',           bg: 'bg-gray-100 dark:bg-gray-700',             text: 'text-gray-500 dark:text-gray-400',       dot: 'bg-gray-400' },
    };

    function getEditRolVisual(nombre) {
        const key = (nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        for (const k of Object.keys(editRolVisual)) {
            if (key.includes(k)) return editRolVisual[k];
        }
        return editRolVisual['default'];
    }

    function buildEditRolOptions() {
        const container = document.getElementById('edit-rol-options');
        if (!container || !window._rolesCrear) return;
        container.innerHTML = '';
        window._rolesCrear.forEach(rol => {
            const v = getEditRolVisual(rol.nombre);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all group outline-none';
            btn.innerHTML = `
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 ${v.bg} ${v.text} transition-transform group-hover:scale-110">
                    <i class="fas ${v.icon} text-sm"></i>
                </div>
                <div class="flex-1 text-left min-w-0">
                    <span class="block text-sm font-bold text-gray-800 dark:text-gray-100 truncate">${rol.nombre}</span>
                </div>
                <span class="w-2 h-2 rounded-full flex-shrink-0 ${v.dot}"></span>
            `;
            btn.addEventListener('click', () => seleccionarEditRol(rol.id, rol.nombre, v));
            container.appendChild(btn);
        });
    }

    function seleccionarEditRol(id, nombre, v) {
        document.getElementById('edit_rol_id_input').value = id;

        const label = document.getElementById('edit-rol-label');
        label.textContent = nombre;
        label.className = 'text-sm font-semibold text-gray-800 dark:text-gray-100 truncate';

        const iconWrapper = document.getElementById('edit-rol-icon');
        iconWrapper.className = `w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${v.bg} ${v.text} transition-all`;
        iconWrapper.innerHTML = `<i class="fas ${v.icon} text-xs"></i>`;

        cerrarEditRolDropdown();
    }

    function toggleEditRolDropdown() {
        const dd = document.getElementById('edit-rol-dropdown');
        const chevron = document.getElementById('edit-rol-chevron');
        if (dd.classList.contains('hidden')) {
            buildEditRolOptions();
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarEditRolDropdown();
        }
    }

    function cerrarEditRolDropdown() {
        document.getElementById('edit-rol-dropdown').classList.add('hidden');
        document.getElementById('edit-rol-chevron').style.transform = 'rotate(0deg)';
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('edit-rol-wrapper');
        if (wrapper && !wrapper.contains(e.target)) cerrarEditRolDropdown();
    });

    function toggleEditAccesoFields() {
        const checkbox = document.getElementById('edit_toggleAcceso');
        const fields = document.getElementById('edit_accesoFields');
        if (checkbox && fields) {
            fields.classList.toggle('hidden', !checkbox.checked);
        }
    }

    window.cerrarEditModal = function() {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        if (modal && content) {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 500);
        }
    };

    window.ejecutarEditar = function(btn) {
        const id          = btn.getAttribute('data-id');
        const nombre      = btn.getAttribute('data-nombre');
        const codigo      = btn.getAttribute('data-codigo');
        const rolId       = btn.getAttribute('data-rol-id');
        const rolNombre   = btn.getAttribute('data-rol-nombre');
        const tieneAcceso = btn.getAttribute('data-acceso');

        const modal            = document.getElementById('editEmpleadoModal');
        const content          = document.getElementById('editModalContent');
        const form             = document.getElementById('formEditar');
        const toggle           = document.getElementById('edit_toggleAcceso');
        const alertaProteccion = document.getElementById('alertaProteccion');
        const cajaAcceso       = document.getElementById('cajaAcceso');
        const cajaRol          = document.getElementById('cajaRol');

        if (form) form.action = `/admin/empleados/${id}`;
        if (document.getElementById('edit_nombre')) document.getElementById('edit_nombre').value = nombre || '';
        if (document.getElementById('edit_codigo_empleado')) document.getElementById('edit_codigo_empleado').value = codigo || '';

        // Actualizar dropdown de rol con visual
        const v = getEditRolVisual(rolNombre);
        document.getElementById('edit_rol_id_input').value = rolId || '';
        const label = document.getElementById('edit-rol-label');
        label.textContent = rolNombre || 'Seleccionar...';
        label.className = rolNombre ? 'text-sm font-semibold text-gray-800 dark:text-gray-100 truncate' : 'text-sm font-semibold text-gray-400 dark:text-gray-500 truncate';
        const iconWrapper = document.getElementById('edit-rol-icon');
        if (rolNombre) {
            iconWrapper.className = `w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${v.bg} ${v.text} transition-all`;
            iconWrapper.innerHTML = `<i class="fas ${v.icon} text-xs"></i>`;
        } else {
            iconWrapper.className = 'w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-gray-200 dark:bg-gray-700 transition-all';
            iconWrapper.innerHTML = '<i class="fas fa-shield-alt text-gray-400 text-xs"></i>';
        }

        if (toggle) {
            toggle.checked = (tieneAcceso == 1 || tieneAcceso == "1");
            toggleEditAccesoFields();
        }

        if (typeof idUsuarioActual !== 'undefined' && idUsuarioActual && parseInt(id) === parseInt(idUsuarioActual)) {
            if (alertaProteccion) alertaProteccion.classList.remove('hidden');
            if (cajaAcceso) cajaAcceso.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
            if (cajaRol) cajaRol.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
        } else {
            if (alertaProteccion) alertaProteccion.classList.add('hidden');
            if (cajaAcceso) cajaAcceso.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
            if (cajaRol) cajaRol.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
        }

        if (modal && content) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        }
    };
</script>