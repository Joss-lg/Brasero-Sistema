<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearEmpleado {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #modalCrearContent {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }

    /* Animación del dropdown */
    #crear-rol-dropdown {
        transform-origin: top;
        animation: dropdownOpen 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes dropdownOpen {
        from { opacity: 0; transform: scaleY(0.9) translateY(-8px); }
        to   { opacity: 1; transform: scaleY(1) translateY(0); }
    }
</style>

<div id="modalCrearEmpleado" class="fixed inset-0 z-[99999] hidden bg-black/75 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 transition-all duration-300">
    
    <div class="bg-white dark:bg-[#111315] border border-gray-200 dark:border-gray-800 w-full max-w-md rounded-3xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[88dvh] sm:max-h-[90dvh]" id="modalCrearContent">
        
        <form id="formCrearEmpleado" method="POST" action="{{ route('admin.empleados.store') }}" class="flex flex-col h-full relative z-10 overflow-hidden bg-white dark:bg-[#111315]">
            @csrf

            <div class="p-5 sm:p-8 overflow-y-auto flex-1 space-y-5 bg-white dark:bg-[#111315] overscroll-contain scrollbar-thin" style="-webkit-overflow-scrolling: touch;">
                
                {{-- Encabezado --}}
                <div class="flex justify-between items-start pb-2 gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight flex items-center gap-2 flex-wrap">
                            Registrar Perfil <i class="fas fa-user-plus text-[#b74309] dark:text-[#e8946a] text-base"></i>
                        </h2>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-0.5">Nueva credencial de acceso</p>
                    </div>
                    <button type="button" onclick="cerrarModalCrear()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800/60 flex-shrink-0 outline-none">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Campo: Nombre Completo --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Nombre Completo</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400 dark:text-gray-500 group-focus-within:text-[#b74309] transition-colors text-sm"></i>
                        </div>
                        <input type="text" name="nombre" id="crear_nombre" required placeholder="Ej. Juan Pérez" autocomplete="off"
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-11 pr-4 text-base sm:text-sm font-semibold text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 outline-none transition-all shadow-sm">
                    </div>
                </div>

                {{-- Campo: Rol del Sistema (Dropdown personalizado) --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Rol del Sistema</label>
                    
                    {{-- Input oculto que se envía con el form --}}
                    <input type="hidden" name="rol_id" id="crear_rol_id" required>

                    {{-- Trigger del dropdown --}}
                    <div class="relative" id="crear-rol-wrapper">
                        <button type="button" id="crear-rol-trigger"
                            onclick="toggleCrearRolDropdown()"
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-4 pr-4 flex items-center justify-between gap-3 transition-all outline-none hover:border-[#b74309]/50 focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div id="crear-rol-icon" class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-gray-200 dark:bg-gray-700 transition-all">
                                    <i class="fas fa-shield-alt text-gray-400 text-xs"></i>
                                </div>
                                <span id="crear-rol-label" class="text-sm font-semibold text-gray-400 dark:text-gray-500 truncate">Seleccionar rol...</span>
                            </div>
                            <i id="crear-rol-chevron" class="fas fa-chevron-down text-gray-400 text-[10px] transition-transform duration-200 flex-shrink-0"></i>
                        </button>

                        {{-- Lista del dropdown --}}
                        <div id="crear-rol-dropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#1a1d20] border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="p-2 space-y-1" id="crear-rol-options">
                                {{-- Opciones generadas por JS con los roles de Blade --}}
                            </div>
                        </div>
                    </div>

                    {{-- Roles de Blade pasados a JS --}}
                    <script>
                        window._rolesCrear = @json($roles ?? []);
                    </script>
                </div>

                {{-- Switch: Acceso al Sistema --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#1a1d20] border border-gray-200 dark:border-gray-800/80 rounded-xl shadow-sm gap-3">
                    <div class="min-w-0 flex-1">
                        <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider leading-tight">Acceso al sistema</label>
                        <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 truncate">¿Usará la plataforma?</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 select-none">
                        <input type="checkbox" name="puede_acceder_pos" id="crear_acceso" value="1" onchange="toggleCrearAccesoFields()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#b74309]"></div>
                    </label>
                </div>

                {{-- PIN --}}
                <div id="crear_accesoFields" class="hidden transition-all duration-300 space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">PIN de Seguridad (4 dígitos)</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400 dark:text-gray-500 group-focus-within:text-[#b74309] transition-colors text-sm"></i>
                        </div>
                        <input type="password" name="codigo_empleado" id="crear_codigo" maxlength="4" pattern="[0-9]*" placeholder="••••" autocomplete="new-password"
                            data-teclado="numerico" data-teclado-max="4" inputmode="none"
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-11 pr-4 text-lg tracking-[0.4em] font-black text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#b74309] dark:focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/20 outline-none transition-all shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Barra inferior --}}
            <div class="px-5 sm:px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-[#15181b] flex-shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="cerrarModalCrear()" class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#b74309] hover:bg-[#8f3207] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md active:scale-95 outline-none">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- CONFIGURACIÓN VISUAL POR ROL ---
    const rolVisual = {
        'administrador': { icon: 'fa-user-shield', bg: 'bg-rose-100 dark:bg-rose-500/20', text: 'text-rose-600 dark:text-rose-400', badge: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400', dot: 'bg-rose-500' },
        'cajero':        { icon: 'fa-cash-register', bg: 'bg-purple-100 dark:bg-purple-500/20', text: 'text-purple-600 dark:text-purple-400', badge: 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400', dot: 'bg-purple-500' },
        'capitán':       { icon: 'fa-clipboard-list', bg: 'bg-[#b74309]/10 dark:bg-[#b74309]/20', text: 'text-[#b74309] dark:text-[#e8946a]', badge: 'bg-[#b74309]/10 text-[#b74309] dark:bg-[#b74309]/10 dark:text-[#e8946a]', dot: 'bg-[#b74309]' },
        'capitan':       { icon: 'fa-clipboard-list', bg: 'bg-[#b74309]/10 dark:bg-[#b74309]/20', text: 'text-[#b74309] dark:text-[#e8946a]', badge: 'bg-[#b74309]/10 text-[#b74309] dark:bg-[#b74309]/10 dark:text-[#e8946a]', dot: 'bg-[#b74309]' },
        'mesero':        { icon: 'fa-concierge-bell', bg: 'bg-emerald-100 dark:bg-emerald-500/20', text: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', dot: 'bg-emerald-500' },
        'cocinero':      { icon: 'fa-fire', bg: 'bg-orange-100 dark:bg-orange-500/20', text: 'text-orange-600 dark:text-orange-400', badge: 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400', dot: 'bg-orange-500' },
        'default':       { icon: 'fa-user', bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-500 dark:text-gray-400', badge: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', dot: 'bg-gray-400' },
    };

    function getRolVisual(nombre) {
        const key = (nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        for (const k of Object.keys(rolVisual)) {
            if (key.includes(k)) return rolVisual[k];
        }
        return rolVisual['default'];
    }

    // --- CONSTRUIR OPCIONES DEL DROPDOWN ---
    function buildCrearRolOptions() {
        const container = document.getElementById('crear-rol-options');
        if (!container || !window._rolesCrear) return;

        container.innerHTML = '';
        window._rolesCrear.forEach(rol => {
            const v = getRolVisual(rol.nombre);
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
            btn.addEventListener('click', () => seleccionarCrearRol(rol.id, rol.nombre, v));
            container.appendChild(btn);
        });
    }

    function seleccionarCrearRol(id, nombre, v) {
        // Actualizar input hidden
        document.getElementById('crear_rol_id').value = id;

        // Actualizar trigger visual
        document.getElementById('crear-rol-label').textContent = nombre;
        document.getElementById('crear-rol-label').classList.remove('text-gray-400', 'dark:text-gray-500');
        document.getElementById('crear-rol-label').classList.add('text-gray-800', 'dark:text-gray-100');

        const iconWrapper = document.getElementById('crear-rol-icon');
        iconWrapper.className = `w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${v.bg} ${v.text} transition-all`;
        iconWrapper.innerHTML = `<i class="fas ${v.icon} text-xs"></i>`;

        cerrarCrearRolDropdown();
    }

    function toggleCrearRolDropdown() {
        const dd = document.getElementById('crear-rol-dropdown');
        const chevron = document.getElementById('crear-rol-chevron');
        if (dd.classList.contains('hidden')) {
            buildCrearRolOptions();
            dd.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            cerrarCrearRolDropdown();
        }
    }

    function cerrarCrearRolDropdown() {
        const dd = document.getElementById('crear-rol-dropdown');
        const chevron = document.getElementById('crear-rol-chevron');
        dd.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }

    // Cerrar al hacer click fuera
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('crear-rol-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            cerrarCrearRolDropdown();
        }
    });

    // --- RESTO DE FUNCIONES ---
    function toggleCrearAccesoFields() {
        const checkbox = document.getElementById('crear_acceso');
        const fields = document.getElementById('crear_accesoFields');
        const inputPin = document.getElementById('crear_codigo');
        if (checkbox && fields && inputPin) {
            if (checkbox.checked) {
                fields.classList.remove('hidden');
                inputPin.setAttribute('required', 'required');
            } else {
                fields.classList.add('hidden');
                inputPin.removeAttribute('required');
                inputPin.value = '';
            }
        }
    }

    function abrirModalCrear() {
        const form = document.getElementById('formCrearEmpleado');
        if (form) form.reset();

        // Reset dropdown visual
        document.getElementById('crear_rol_id').value = '';
        document.getElementById('crear-rol-label').textContent = 'Seleccionar rol...';
        document.getElementById('crear-rol-label').className = 'text-sm font-semibold text-gray-400 dark:text-gray-500 truncate';
        document.getElementById('crear-rol-icon').className = 'w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-gray-200 dark:bg-gray-700 transition-all';
        document.getElementById('crear-rol-icon').innerHTML = '<i class="fas fa-shield-alt text-gray-400 text-xs"></i>';

        toggleCrearAccesoFields();

        const modal = document.getElementById('modalCrearEmpleado');
        const content = document.getElementById('modalCrearContent');
        if (modal && content) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }
    }

    function cerrarModalCrear() {
        const modal = document.getElementById('modalCrearEmpleado');
        const content = document.getElementById('modalCrearContent');
        if (modal && content) {
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    }
</script>