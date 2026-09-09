@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#0B0C10] p-3 sm:p-6 md:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-5 md:space-y-8 flex-1 flex flex-col transition-colors duration-300">
    
    {{-- CABECERA DE LA SECCIÓN --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 relative z-10">
        <div>
            <h1 class="text-2xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.crear'))
            <div class="relative group w-full sm:w-auto">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-[#b74309] to-[#8f3207] rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500 pointer-events-none"></div>
                <button type="button" onclick="abrirModalCrear()" class="relative flex items-center justify-center gap-2.5 bg-gradient-to-b from-[#b74309] to-[#8f3207] hover:from-[#c94d0a] hover:to-[#a03808] text-white px-6 py-3.5 rounded-xl text-sm font-bold transition-all duration-300 outline-none w-full sm:w-auto shadow-lg shadow-[#b74309]/20 hover:shadow-[#b74309]/30 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- LÓGICA DE ESTADÍSTICAS --}}
    @php
        $totalAdmin = 0; $totalCapitan = 0; $totalMesero = 0; $totalCocinero = 0; $totalCajero = 0;
        
        foreach($empleados ?? [] as $emp) {
            $rolStr = mb_strtolower($emp->rol?->nombre ?? '', 'UTF-8');
            
            if(str_contains($rolStr, 'admin')) $totalAdmin++;
            elseif(str_contains($rolStr, 'capitan') || str_contains($rolStr, 'capitán')) $totalCapitan++;
            elseif(str_contains($rolStr, 'mesero')) $totalMesero++;
            elseif(str_contains($rolStr, 'cocinero')) $totalCocinero++;
            elseif(str_contains($rolStr, 'cajero')) $totalCajero++;
        }

        $tarjetasStats = [
            [
                'titulo' => 'Administradores', 'valor' => $totalAdmin, 'icono' => 'user-shield',
                'bgIcono' => 'bg-rose-50 text-rose-600 border border-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400',
                'bgGlow' => 'bg-rose-500/[0.04] dark:bg-rose-500/10',
                'barra' => 'bg-rose-500',
                'bordeClases' => 'border border-gray-200/70 dark:border-white/5 hover:border-rose-300 dark:hover:border-rose-500/40 hover:shadow-[0_8px_30px_-8px_rgba(244,63,94,0.25)]'
            ],
            [
                'titulo' => 'Capitanes', 'valor' => $totalCapitan, 'icono' => 'clipboard-list',
                'bgIcono' => 'bg-[#b74309]/10 text-[#b74309] border border-[#b74309]/20 dark:border-[#b74309]/20 dark:bg-[#b74309]/10 dark:text-[#e8946a]',
                'bgGlow' => 'bg-[#b74309]/[0.04] dark:bg-[#b74309]/10',
                'barra' => 'bg-[#b74309]',
                'bordeClases' => 'border border-gray-200/70 dark:border-white/5 hover:border-[#b74309]/40 dark:hover:border-[#b74309]/40 hover:shadow-[0_8px_30px_-8px_rgba(183,67,9,0.25)]'
            ],
            [
                'titulo' => 'Meseros', 'valor' => $totalMesero, 'icono' => 'concierge-bell',
                'bgIcono' => 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400',
                'bgGlow' => 'bg-emerald-500/[0.04] dark:bg-emerald-500/10',
                'barra' => 'bg-emerald-500',
                'bordeClases' => 'border border-gray-200/70 dark:border-white/5 hover:border-emerald-300 dark:hover:border-emerald-500/40 hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.25)]'
            ],
            [
                'titulo' => 'Cocineros', 'valor' => $totalCocinero, 'icono' => 'fire',
                'bgIcono' => 'bg-orange-50 text-orange-600 border border-orange-100 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400',
                'bgGlow' => 'bg-orange-500/[0.04] dark:bg-orange-500/10',
                'barra' => 'bg-orange-500',
                'bordeClases' => 'border border-gray-200/70 dark:border-white/5 hover:border-orange-300 dark:hover:border-orange-500/40 hover:shadow-[0_8px_30px_-8px_rgba(249,115,22,0.25)]'
            ],
            [
                'titulo' => 'Cajeros', 'valor' => $totalCajero, 'icono' => 'cash-register',
                'bgIcono' => 'bg-purple-50 text-purple-600 border border-purple-100 dark:border-purple-500/20 dark:bg-purple-500/10 dark:text-purple-400',
                'bgGlow' => 'bg-purple-500/[0.04] dark:bg-purple-500/10',
                'barra' => 'bg-purple-500',
                'bordeClases' => 'border border-gray-200/70 dark:border-white/5 hover:border-purple-300 dark:hover:border-purple-500/40 hover:shadow-[0_8px_30px_-8px_rgba(168,85,247,0.25)]'
            ],
        ];
    @endphp

    {{-- GRID DE ESTADÍSTICAS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-5 relative z-10">
        @foreach($tarjetasStats as $stat)
        <div class="bg-white dark:bg-[#121318] {{ $stat['bordeClases'] }} rounded-xl md:rounded-[1.5rem] p-3 md:p-6 flex flex-col justify-between h-20 sm:h-24 md:h-36 relative group overflow-hidden transition-all duration-300 last:col-span-2 sm:last:col-span-1">
            <span class="absolute top-0 left-0 right-0 h-[3px] {{ $stat['barra'] }} opacity-70 group-hover:opacity-100 transition-opacity"></span>

            <div class="flex justify-between items-start w-full relative z-10">
                <h3 class="text-[8px] sm:text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest truncate pr-2">{{ $stat['titulo'] }}</h3>
                <div class="w-6 h-6 sm:w-7 sm:h-7 md:w-10 md:h-10 rounded-[0.5rem] md:rounded-xl {{ $stat['bgIcono'] }} flex items-center justify-center flex-shrink-0 transition-transform duration-300 group-hover:scale-105">
                    <i class="fas fa-{{ $stat['icono'] }} text-[9px] sm:text-[10px] md:text-base"></i>
                </div>
            </div>
            <p class="text-xl sm:text-2xl md:text-5xl font-black text-gray-900 dark:text-white relative z-10 mt-1 md:mt-0 tabular-nums">{{ $stat['valor'] }}</p>
            <div class="absolute -bottom-4 -right-4 w-16 h-16 md:w-24 md:h-24 {{ $stat['bgGlow'] }} blur-2xl pointer-events-none"></div>
        </div>
        @endforeach
    </div>

    {{-- CONTENEDOR PRINCIPAL DE EMPLEADOS --}}
    <div class="bg-white dark:bg-[#121318] border border-gray-200/70 dark:border-white/5 rounded-2xl md:rounded-3xl p-3 sm:p-4 md:p-8 w-full flex-1 flex flex-col shadow-sm dark:shadow-2xl dark:shadow-black/40 relative z-20">
        
        <div class="mb-4 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 relative z-30 pb-4 md:pb-6 border-b border-gray-100 dark:border-white/5">
            <div>
                <h2 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Lista de Empleados</h2>
                <p class="text-[10px] md:text-xs font-medium text-gray-400 dark:text-gray-500 mt-1">{{ count($empleados ?? []) }} registrados en el sistema</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-72 group">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm group-focus-within:text-[#b74309] transition-colors"></i>
                    <input type="text" id="buscadorEmpleados" data-teclado="texto" placeholder="Buscar empleado..." 
                        class="w-full h-11 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-full pl-11 pr-4 text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10 focus:bg-white dark:focus:bg-black/60 outline-none transition-all">
                </div>
                
                <a href="{{ request()->has('ver_inactivos') ? route('admin.empleados.index') : route('admin.empleados.index', ['ver_inactivos' => 1]) }}" 
                    title="{{ request()->has('ver_inactivos') ? 'Ver solo activos' : 'Ver inactivos' }}"
                    class="w-11 h-11 flex-shrink-0 flex items-center justify-center rounded-full border border-gray-200 dark:border-white/5 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-all hover:-translate-y-0.5 {{ request()->has('ver_inactivos') ? 'text-rose-500' : 'text-gray-400' }}">
                    <i class="fas fa-{{ request()->has('ver_inactivos') ? 'eye-slash' : 'eye' }} text-sm"></i> 
                </a>
            </div>
        </div>

        {{-- VISTA MÓVIL --}}
        <div id="listaEmpleadosMovil" class="flex flex-col gap-3 sm:hidden relative z-20">
            @forelse($empleados ?? [] as $empleado)
                @php
                    $rolStr = mb_strtolower($empleado->rol?->nombre ?? '', 'UTF-8');
                    $colorClass = 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300';
                    $dotClass = 'bg-gray-400';
                    if (str_contains($rolStr, 'admin')) { $colorClass = 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'; $dotClass = 'bg-rose-500'; }
                    elseif (str_contains($rolStr, 'cajero')) { $colorClass = 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400'; $dotClass = 'bg-purple-500'; }
                    elseif (str_contains($rolStr, 'mesero')) { $colorClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'; $dotClass = 'bg-emerald-500'; }
                    elseif (str_contains($rolStr, 'capitan') || str_contains($rolStr, 'capitán')) { $colorClass = 'bg-[#b74309]/10 text-[#b74309] dark:bg-[#b74309]/10 dark:text-[#e8946a]'; $dotClass = 'bg-[#b74309]'; }
                    elseif (str_contains($rolStr, 'cocinero')) { $colorClass = 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'; $dotClass = 'bg-orange-500'; }
                @endphp
                <div class="fila-empleado-movil border border-gray-100 dark:border-white/5 rounded-2xl p-3.5 bg-gray-50/60 dark:bg-white/[0.025] transition-all {{ !$empleado->esta_activo ? 'opacity-40 grayscale' : 'hover:border-gray-200 dark:hover:border-white/10' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200/60 dark:from-[#1e2028] dark:to-[#15171e] dark:border-white/10 flex items-center justify-center text-[#b74309] dark:text-[#e8946a] font-black text-sm flex-shrink-0 ring-2 ring-white dark:ring-[#121318]">
                                {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="nombre-empleado-txt font-bold text-sm text-gray-900 dark:text-gray-100 truncate">{{ $empleado->nombre }}</span>
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 mt-0.5">ID: EMP-{{ str_pad($empleado->id, 3, '0', STR_PAD_LEFT) }} · PIN {{ $empleado->codigo_empleado ?? '----' }}</span>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $colorClass }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ $empleado->rol?->nombre ?? 'Sin rol' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-3.5 pt-3 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-[10px] font-black uppercase text-gray-600 dark:bg-black/20 dark:border-white/5 dark:text-gray-400 dark:hover:bg-white/5 transition-all">
                            <i class="fas fa-shield-alt text-[#b74309] text-[11px]"></i> Permisos
                        </a>

                        @if($empleado->esta_activo)
                            <button type="button" title="Editar" onclick="window.ejecutarEditar(this)"
                                class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center border border-[#b74309]/50 dark:border-[#b74309]/50 bg-[#b74309]/10 dark:bg-[#b74309]/10 text-[#b74309] dark:text-[#e8946a] active:scale-95 transition-all"
                                data-id="{{ $empleado->id }}"
                                data-nombre="{{ $empleado->nombre }}"
                                data-codigo="{{ $empleado->codigo_empleado }}"
                                data-rol-id="{{ $empleado->rol_id }}"
                                data-rol-nombre="{{ $empleado->rol?->nombre ?? 'Seleccionar...' }}"
                                data-acceso="{{ $empleado->puede_acceder_pos ? 1 : 0 }}">
                                <i class="fas fa-pen text-[12px] pointer-events-none"></i>
                            </button>
                            <button type="button" onclick="abrirConfirmacionEliminar('form-delete-movil-{{ $empleado->id }}', false)" title="Desactivar"
                                class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center border border-amber-300 dark:border-amber-500/50 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 active:scale-95 transition-all">
                                <i class="fas fa-user-slash text-[12px] pointer-events-none"></i>
                            </button>
                            <form id="form-delete-movil-{{ $empleado->id }}" action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        @else
                            <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="shrink-0">
                                @csrf @method('PATCH')
                                <button type="submit" title="Reactivar" class="h-10 w-10 rounded-xl flex items-center justify-center border border-emerald-300 dark:border-emerald-500/50 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 active:scale-95 transition-all">
                                    <i class="fas fa-user-check text-[12px] pointer-events-none"></i>
                                </button>
                            </form>
                            <button type="button" onclick="abrirConfirmacionEliminar('form-delete-movil-{{ $empleado->id }}', true)" title="Eliminar Permanentemente"
                                class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center border border-red-300 dark:border-red-500/50 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 active:scale-95 transition-all">
                                <i class="fas fa-trash-alt text-[12px] pointer-events-none"></i>
                            </button>
                            <form id="form-delete-movil-{{ $empleado->id }}" action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-14">
                    <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                        <i class="fas fa-users-slash text-xl text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-400">No hay empleados registrados</span>
                </div>
            @endforelse
        </div>

        {{-- VISTA ESCRITORIO --}}
        <div class="hidden sm:block w-full overflow-x-auto relative z-20 pb-4">
            <table class="w-full min-w-[700px] text-left border-collapse table-fixed">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="w-[30%] pb-4 px-2 md:px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-left">Nombre</th>
                        <th class="w-[15%] pb-4 px-2 md:px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">PIN</th>
                        <th class="w-[20%] pb-4 px-2 md:px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Rol</th>
                        <th class="w-[20%] pb-4 px-2 md:px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Permisos</th>
                        <th class="w-[15%] pb-4 px-2 md:px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEmpleados" class="divide-y divide-gray-100 dark:divide-white/[0.03]">
                    @forelse($empleados ?? [] as $empleado)
                    
                    @php
                        $rolStr = mb_strtolower($empleado->rol?->nombre ?? '', 'UTF-8');
                        $colorClass = 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300';
                        $dotClass = 'bg-gray-400';
                        if (str_contains($rolStr, 'admin')) { $colorClass = 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'; $dotClass = 'bg-rose-500'; }
                        elseif (str_contains($rolStr, 'cajero')) { $colorClass = 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400'; $dotClass = 'bg-purple-500'; }
                        elseif (str_contains($rolStr, 'mesero')) { $colorClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'; $dotClass = 'bg-emerald-500'; }
                        elseif (str_contains($rolStr, 'capitan') || str_contains($rolStr, 'capitán')) { $colorClass = 'bg-[#b74309]/10 text-[#b74309] dark:bg-[#b74309]/10 dark:text-[#e8946a]'; $dotClass = 'bg-[#b74309]'; }
                        elseif (str_contains($rolStr, 'cocinero')) { $colorClass = 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'; $dotClass = 'bg-orange-500'; }
                    @endphp

                    <tr class="fila-empleado group hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-all duration-200 {{ !$empleado->esta_activo ? 'opacity-40 grayscale' : '' }}">
                        
                        <td class="py-4 px-2 md:px-4 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200/60 dark:from-[#1e2028] dark:to-[#15171e] dark:border-white/10 flex items-center justify-center text-[#b74309] dark:text-[#e8946a] font-black text-sm flex-shrink-0 ring-2 ring-white dark:ring-[#121318]">
                                    {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0 truncate">
                                    <span class="nombre-empleado-txt font-bold text-sm text-gray-900 dark:text-gray-100 truncate">{{ $empleado->nombre }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 mt-0.5">ID: EMP-{{ str_pad($empleado->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-2 md:px-4 align-middle text-center">
                            <span class="inline-block font-black text-xs text-gray-600 dark:text-gray-400 tracking-[0.2em] bg-gray-50 dark:bg-black/30 px-2.5 py-1 rounded-md">{{ $empleado->codigo_empleado ?? '----' }}</span>
                        </td>
                        
                        <td class="py-4 px-2 md:px-4 align-middle text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest {{ $colorClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ $empleado->rol?->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        
                        <td class="py-4 px-2 md:px-4 align-middle text-center">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-[9px] md:text-[10px] font-black uppercase text-gray-600 dark:bg-black/20 dark:border-white/5 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-[#e8946a] transition-all">
                                <i class="fas fa-shield-alt text-[#b74309] text-[10px]"></i> Configurar
                            </a>
                        </td>
                        
                        <td class="py-4 px-2 md:px-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-2 relative z-30">
                                @if($empleado->esta_activo)
                                    <button type="button" title="Editar"
                                        onclick="window.ejecutarEditar(this)"
                                        class="cursor-pointer relative z-50 h-9 w-9 md:h-10 md:w-10 rounded-xl flex items-center justify-center border border-[#b74309]/50 dark:border-[#b74309]/50 bg-[#b74309]/10 dark:bg-[#b74309]/10 text-[#b74309] dark:text-[#e8946a] hover:bg-[#b74309]/20 dark:hover:bg-[#b74309]/20 transition-all shadow-sm outline-none flex-shrink-0"
                                        data-id="{{ $empleado->id }}" 
                                        data-nombre="{{ $empleado->nombre }}" 
                                        data-codigo="{{ $empleado->codigo_empleado }}" 
                                        data-rol-id="{{ $empleado->rol_id }}" 
                                        data-rol-nombre="{{ $empleado->rol?->nombre ?? 'Seleccionar...' }}"
                                        data-acceso="{{ $empleado->puede_acceder_pos ? 1 : 0 }}">
                                        <i class="fas fa-pen text-[12px] pointer-events-none"></i>
                                    </button>
                                    
                                    <form id="form-delete-{{ $empleado->id }}" action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline-flex items-center justify-center m-0 p-0">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="abrirConfirmacionEliminar('form-delete-{{ $empleado->id }}', false)" title="Desactivar" class="h-9 w-9 md:h-10 md:w-10 rounded-xl flex items-center justify-center border border-amber-300 dark:border-amber-500/50 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-all shadow-sm outline-none flex-shrink-0">
                                            <i class="fas fa-user-slash text-[12px] pointer-events-none"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline-flex items-center justify-center m-0 p-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Reactivar" class="h-9 w-9 md:h-10 md:w-10 rounded-xl flex items-center justify-center border border-emerald-300 dark:border-emerald-500/50 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all shadow-sm outline-none flex-shrink-0">
                                            <i class="fas fa-user-check text-[12px] pointer-events-none"></i>
                                        </button>
                                    </form>
                                    
                                    <form id="form-delete-{{ $empleado->id }}" action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline-flex items-center justify-center m-0 p-0">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="abrirConfirmacionEliminar('form-delete-{{ $empleado->id }}', true)" title="Eliminar Permanentemente" class="h-9 w-9 md:h-10 md:w-10 rounded-xl flex items-center justify-center border border-red-300 dark:border-red-500/50 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all shadow-sm outline-none flex-shrink-0">
                                            <i class="fas fa-trash-alt text-[12px] pointer-events-none"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                                    <i class="fas fa-users-slash text-xl text-gray-300 dark:text-gray-600"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-400">No hay empleados registrados</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALES INCLUIDOS --}}
@include('admin.empleados.modal-editar')
@include('admin.empleados.modal-crear')

{{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
<div id="modal-confirmacion-eliminar" class="fixed inset-0 z-[999] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] p-6 sm:p-8 max-w-sm w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200 overflow-hidden text-center">
        
        <div id="glow-modal" class="absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex justify-center mb-5 relative z-10">
            <div id="wrapper-icono" class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border">
                <i id="icono-modal" class="fas text-2xl sm:text-3xl"></i>
            </div>
        </div>
        
        <h2 id="titulo-confirmacion" class="text-lg sm:text-xl font-black !text-gray-900 dark:!text-white mb-2 tracking-tight relative z-10">¿Desactivar Empleado?</h2>
        <p id="texto-confirmacion" class="!text-gray-500 dark:!text-gray-400 text-sm mb-6 font-medium relative z-10">
            El empleado no podrá acceder al sistema, pero sus registros se mantendrán.
        </p>
        
        <div class="flex gap-3 relative z-10">
            <button type="button" onclick="cerrarConfirmacionEliminar()" class="flex-1 py-3.5 sm:py-4 px-4 !bg-gray-100 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 !text-gray-500 dark:!text-white/60 hover:!text-gray-900 dark:hover:!text-white font-black text-[11px] sm:text-xs uppercase tracking-widest rounded-2xl border !border-gray-200 dark:!border-white/5 transition-all outline-none active:scale-[0.98]">
                Cancelar
            </button>
            <button type="button" id="btn-ejecutar-eliminar" class="flex-1 py-3.5 sm:py-4 px-4 !text-white font-black text-[11px] sm:text-xs uppercase tracking-widest rounded-2xl transition-all outline-none active:scale-[0.98]">
                Confirmar
            </button>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    let formularioEliminarSeleccionado = null;

    window.abrirConfirmacionEliminar = function(formId, esPermanente) {
        formularioEliminarSeleccionado = formId;
        const modal = document.getElementById('modal-confirmacion-eliminar');
        const titulo = document.getElementById('titulo-confirmacion');
        const texto = document.getElementById('texto-confirmacion');
        const glow = document.getElementById('glow-modal');
        const wrapperIcono = document.getElementById('wrapper-icono');
        const icono = document.getElementById('icono-modal');
        const btnConfirmar = document.getElementById('btn-ejecutar-eliminar');

        if (esPermanente) {
            titulo.innerText = '¿Eliminar Permanentemente?';
            texto.innerText = 'Esta acción no se puede deshacer. Se borrarán todos los datos de este empleado de la base de datos de manera definitiva.';
            glow.className = "absolute -top-24 -left-24 w-48 h-48 bg-red-500/10 dark:bg-red-500/20 rounded-full blur-3xl pointer-events-none";
            wrapperIcono.className = "flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20";
            icono.className = "fas fa-exclamation-triangle text-2xl sm:text-3xl text-red-500 dark:text-red-400";
            btnConfirmar.className = "flex-1 py-3.5 sm:py-4 px-4 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-black text-[11px] sm:text-xs uppercase tracking-widest rounded-2xl shadow-lg shadow-red-500/20 transition-all outline-none active:scale-[0.98]";
        } else {
            titulo.innerText = '¿Desactivar Empleado?';
            texto.innerText = 'El empleado no podrá acceder al sistema, pero sus registros se mantendrán.';
            glow.className = "absolute -top-24 -left-24 w-48 h-48 bg-amber-500/10 dark:bg-amber-500/20 rounded-full blur-3xl pointer-events-none";
            wrapperIcono.className = "flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20";
            icono.className = "fas fa-user-slash text-2xl sm:text-3xl text-amber-500 dark:text-amber-400";
            btnConfirmar.className = "flex-1 py-3.5 sm:py-4 px-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-black text-[11px] sm:text-xs uppercase tracking-widest rounded-2xl shadow-lg shadow-amber-500/20 transition-all outline-none active:scale-[0.98]";
        }

        modal.classList.remove('hidden');
    };

    window.cerrarConfirmacionEliminar = function() {
        document.getElementById('modal-confirmacion-eliminar').classList.add('hidden');
        formularioEliminarSeleccionado = null;
    };

    document.getElementById('btn-ejecutar-eliminar').addEventListener('click', function() {
        if (formularioEliminarSeleccionado) {
            document.getElementById(formularioEliminarSeleccionado).submit();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorEmpleados');
        const filasDesktop = document.querySelectorAll('.fila-empleado');
        const filasMovil = document.querySelectorAll('.fila-empleado-movil');
        
        function filtrarEmpleados(term) {
            filasDesktop.forEach(fila => {
                const nombre = fila.querySelector('.nombre-empleado-txt').textContent.toLowerCase();
                fila.style.display = nombre.includes(term) ? '' : 'none';
            });
            filasMovil.forEach(fila => {
                const nombre = fila.querySelector('.nombre-empleado-txt').textContent.toLowerCase();
                fila.style.display = nombre.includes(term) ? '' : 'none';
            });
        }

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                filtrarEmpleados(e.target.value.toLowerCase().trim());
            });
            buscador.addEventListener('virtualKeyboardInput', function(e) {
                filtrarEmpleados(e.target.value.toLowerCase().trim());
            });
        }
    });
</script>

@endsection