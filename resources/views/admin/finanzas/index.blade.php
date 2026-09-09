@extends('layouts.admin')

@section('title', 'Finanzas | Flujo de Caja | Ollintem Pro')

@section('content')
<div class="p-3 sm:p-6 lg:p-8 xl:p-12 max-w-[1800px] mx-auto w-full space-y-5 sm:space-y-8 flex-1 flex flex-col transition-all duration-300 relative z-10">

    {{-- CABECERA Y BOTONES --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-3 sm:gap-6 mb-2">
        <div class="space-y-1 w-full sm:w-auto">
            <h1 class="text-xl sm:text-3xl md:text-4xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">Flujo de Caja</h1>
            <p class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-400">Control centralizado de ingresos y egresos | <span class="text-zinc-700 dark:text-zinc-300 font-bold capitalize">{{ now()->format('F Y') }}</span></p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 w-full xl:w-auto">
            <a href="{{ route('admin.finanzas.corte.mensual') }}"
                class="w-full sm:w-auto bg-[#b74309]/10 hover:bg-[#b74309]/20 dark:bg-[#b74309]/10 dark:hover:bg-[#b74309]/20 text-[#b74309] dark:text-[#e8946a] border border-[#b74309]/20 dark:border-[#b74309]/20 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] shadow-sm">
                <i class="fas fa-calendar-check"></i> Corte Mensual
            </a>

            <a href="{{ route('admin.finanzas.meseros') }}"
                class="w-full sm:w-auto bg-[#b74309]/10 hover:bg-[#b74309]/20 dark:bg-[#b74309]/10 dark:hover:bg-[#b74309]/20 text-[#b74309] dark:text-[#e8946a] border border-[#b74309]/20 dark:border-[#b74309]/20 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] shadow-sm">
                <i class="fas fa-user-tie"></i> Meseros
            </a>

            @if(auth()->user()->tienePermiso('finanzas.mostrar'))
                <a href="{{ route('admin.finanzas.exportar') }}"
                    class="w-full sm:w-auto bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] shadow-sm">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
            @endif

            @if(auth()->user()->tienePermiso('finanzas.editar'))
                <button onclick="openModalCrearNomina()"
                    class="w-full sm:w-auto bg-purple-50 hover:bg-purple-100 dark:bg-purple-600/10 dark:hover:bg-purple-600/20 text-purple-700 dark:text-purple-500 border border-purple-200 dark:border-purple-500/20 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] shadow-sm">
                    <i class="fas fa-users"></i> Pagar Nómina
                </button>
            @endif

            @if(auth()->user()->tienePermiso('finanzas.crear'))
                <button onclick="openModalCrearGasto()"
                    class="w-full sm:w-auto bg-[#b74309] hover:bg-[#8f3207] text-white shadow-lg shadow-[#b74309]/20 px-6 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">
                    <i class="fas fa-plus"></i> Nuevo Gasto
                </button>
            @endif
        </div>
    </div>

    {{-- TARJETAS KPIs --}}
    <div class="grid grid-cols-1 min-[420px]:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-5">
        {{-- Ingresos --}}
        <div class="relative overflow-hidden bg-white dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl sm:rounded-3xl p-4 sm:p-6 group hover:border-emerald-200 dark:hover:border-emerald-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-arrow-up text-5xl sm:text-6xl text-emerald-500"></i>
            </div>
            <div class="flex items-center justify-between mb-3 sm:mb-4 relative z-10">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-500 border border-emerald-100 dark:border-emerald-500/20">
                    <i class="fas fa-arrow-up text-sm sm:text-lg"></i>
                </div>
                <span class="px-2 sm:px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-transparent rounded-full text-[8px] sm:text-[10px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest">Este Mes</span>
            </div>
            <div class="relative z-10">
                <p class="text-emerald-600 dark:text-emerald-600/70 text-[9px] sm:text-[11px] font-bold uppercase tracking-widest mb-1">Ingresos</p>
                <h3 class="text-xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 break-all">$ {{ number_format($ingresosMes, 2) }}</h3>
                <p class="text-[9px] sm:text-[11px] font-medium text-emerald-500 dark:text-emerald-600/60 mt-1 sm:mt-2">Ventas registradas</p>
            </div>
        </div>

        {{-- Egresos --}}
        <div class="relative overflow-hidden bg-white dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-2xl sm:rounded-3xl p-4 sm:p-6 group hover:border-rose-200 dark:hover:border-rose-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-arrow-down text-5xl sm:text-6xl text-rose-500"></i>
            </div>
            <div class="flex items-center justify-between mb-3 sm:mb-4 relative z-10">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-500 border border-rose-100 dark:border-rose-500/20">
                    <i class="fas fa-arrow-down text-sm sm:text-lg"></i>
                </div>
                <span class="px-2 sm:px-2.5 py-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-transparent rounded-full text-[8px] sm:text-[10px] font-black text-rose-700 dark:text-rose-500 uppercase tracking-widest">Este Mes</span>
            </div>
            <div class="relative z-10">
                <p class="text-rose-600 dark:text-rose-600/70 text-[9px] sm:text-[11px] font-bold uppercase tracking-widest mb-1">Egresos</p>
                <h3 class="text-xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 break-all">$ {{ number_format($egresosMes, 2) }}</h3>
                <p class="text-[9px] sm:text-[11px] font-medium text-rose-500 dark:text-rose-600/60 mt-1 sm:mt-2">Gastos registrados</p>
            </div>
        </div>

        {{-- Balance --}}
        <div class="relative overflow-hidden bg-white dark:bg-[#b74309]/5 border border-[#b74309]/20 dark:border-[#b74309]/20 rounded-2xl sm:rounded-3xl p-4 sm:p-6 group hover:border-[#b74309]/40 dark:hover:border-[#b74309]/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wallet text-5xl sm:text-6xl text-[#b74309]"></i>
            </div>
            <div class="flex items-center justify-between mb-3 sm:mb-4 relative z-10">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-[#b74309]/10 flex items-center justify-center text-[#b74309] dark:text-[#e8946a] border border-[#b74309]/20">
                    <i class="fas fa-chart-line text-sm sm:text-lg"></i>
                </div>
                <span class="px-2 sm:px-2.5 py-1 bg-[#b74309]/10 border border-[#b74309]/20 dark:border-transparent rounded-full text-[8px] sm:text-[10px] font-black text-[#b74309] dark:text-[#e8946a] uppercase tracking-widest">Balance</span>
            </div>
            <div class="relative z-10">
                <p class="text-[#b74309] dark:text-[#b74309]/70 text-[9px] sm:text-[11px] font-bold uppercase tracking-widest mb-1">Neto</p>
                <h3 class="text-xl sm:text-3xl font-black break-all {{ $balanceNeto >= 0 ? 'text-[#b74309] dark:text-[#e8946a]' : 'text-rose-600 dark:text-rose-400' }}">
                    $ {{ number_format($balanceNeto, 2) }}
                </h3>
                <p class="text-[9px] sm:text-[11px] font-medium mt-1 sm:mt-2 {{ $balanceNeto >= 0 ? 'text-[#b74309]/70 dark:text-[#b74309]/60' : 'text-rose-500 dark:text-rose-600/60' }}">
                    {{ $balanceNeto >= 0 ? 'Superávit registrado' : 'Déficit registrado' }}
                </p>
            </div>
        </div>

        {{-- Nómina --}}
        <div class="relative overflow-hidden bg-white dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-2xl sm:rounded-3xl p-4 sm:p-6 group hover:border-purple-200 dark:hover:border-purple-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-clock text-5xl sm:text-6xl text-purple-500"></i>
            </div>
            <div class="flex items-center justify-between mb-3 sm:mb-4 relative z-10">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-500 border border-purple-100 dark:border-purple-500/20">
                    <i class="fas fa-clock text-sm sm:text-lg"></i>
                </div>
                <span class="px-2 sm:px-2.5 py-1 bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-transparent rounded-full text-[8px] sm:text-[10px] font-black text-purple-700 dark:text-purple-500 uppercase tracking-widest">Este Mes</span>
            </div>
            <div class="relative z-10">
                <p class="text-purple-600 dark:text-purple-600/70 text-[9px] sm:text-[11px] font-bold uppercase tracking-widest mb-1">Nómina</p>
                <h3 class="text-xl sm:text-3xl font-black text-purple-600 dark:text-purple-400 break-all">$ {{ number_format($nominaPagada, 2) }}</h3>
                <p class="text-[9px] sm:text-[11px] font-medium text-purple-500 dark:text-purple-600/60 mt-1 sm:mt-2">Pagada a empleados</p>
            </div>
        </div>
    </div>

    {{-- PESTAÑAS --}}
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 p-1 bg-zinc-100 dark:bg-zinc-900/80 border border-zinc-200/60 dark:border-zinc-800 rounded-2xl w-full sm:w-fit">
        <a href="{{ route('admin.finanzas.index', ['tab' => 'todos']) }}"
            class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex justify-center items-center gap-1.5 sm:gap-2 {{ $tab === 'todos' ? 'bg-white dark:bg-zinc-800 text-[#b74309] dark:text-[#e8946a] shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-list"></i> Todos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'ingresos']) }}"
            class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex justify-center items-center gap-1.5 sm:gap-2 {{ $tab === 'ingresos' ? 'bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-arrow-up"></i> Ingresos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'egresos']) }}"
            class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex justify-center items-center gap-1.5 sm:gap-2 {{ $tab === 'egresos' ? 'bg-white dark:bg-zinc-800 text-rose-600 dark:text-rose-400 shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-arrow-down"></i> Egresos
        </a>
    </div>

    {{-- TABLA DE HISTORIAL --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl shadow-lg p-3 sm:p-6 lg:p-8 w-full flex-1 relative z-20 overflow-hidden">
        
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-[#b74309]/10 border border-[#b74309]/20 flex items-center justify-center text-[#b74309] dark:text-[#e8946a] shrink-0">
                    <i class="fas fa-exchange-alt text-base sm:text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">Historial de Transacciones</h2>
                    <p class="text-zinc-500 dark:text-zinc-500 text-xs sm:text-sm font-medium mt-0.5">Total: {{ $flujosCaja->total() }} registros</p>
                </div>
            </div>
            
            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500"></i>
                <input type="text" id="buscadorFlujo" data-teclado="texto" placeholder="Buscar concepto..." 
                    class="w-full bg-white dark:bg-zinc-950 border border-zinc-200/60 dark:border-zinc-800 rounded-xl py-3 sm:py-2.5 pl-11 pr-4 text-sm text-zinc-800 dark:text-zinc-200 focus:border-[#b74309] focus:ring-1 focus:ring-[#b74309] outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600 shadow-sm">
            </div>
        </div>

        {{-- VISTA MÓVIL --}}
        <div class="flex flex-col gap-2.5 sm:hidden">
            @forelse($flujosCaja as $flujo)
            <div class="fila-flujo-movil border border-zinc-100 dark:border-zinc-800/50 rounded-2xl p-3.5 bg-zinc-50/50 dark:bg-zinc-950/30">
                <div class="flex items-start justify-between gap-2 mb-2">
                    @if($flujo->tipo === 'ingreso')
                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-full text-[9px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit shrink-0">
                            <i class="fas fa-arrow-up text-[9px]"></i> Ingreso
                        </span>
                    @else
                        <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 rounded-full text-[9px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit shrink-0">
                            <i class="fas fa-arrow-down text-[9px]"></i> Egreso
                        </span>
                    @endif
                    <span class="text-[10px] font-bold text-zinc-500 dark:text-zinc-500 shrink-0">{{ $flujo->fecha->format('d M, Y') }}</span>
                </div>

                <p class="concepto-celda-movil text-sm font-bold text-zinc-800 dark:text-zinc-200 mb-2">{{ $flujo->concepto }}</p>

                <div class="flex items-center justify-between gap-2 flex-wrap pt-2 border-t border-zinc-100 dark:border-zinc-800/50">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-2 py-1 bg-white dark:bg-zinc-800 rounded-lg text-[9px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-widest border border-zinc-200 dark:border-zinc-700/50">
                            {{ $flujo->categoria }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-credit-card text-[9px] opacity-70"></i> {{ $flujo->metodo_pago }}
                        </span>
                    </div>
                    <span class="font-black text-sm {{ $flujo->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $flujo->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($flujo->monto, 2) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="py-12 text-center bg-zinc-50/50 dark:bg-zinc-950/20 rounded-2xl">
                <div class="flex flex-col items-center justify-center text-zinc-500 dark:text-zinc-500">
                    <i class="fas fa-folder-open text-3xl mb-3 opacity-40 dark:opacity-20"></i>
                    <p class="text-xs font-medium">No hay registros de flujo de caja aún.</p>
                </div>
            </div>
            @endforelse
        </div>

        {{-- VISTA ESCRITORIO --}}
        <div class="hidden sm:block overflow-x-auto rounded-xl sm:rounded-2xl border border-zinc-200 dark:border-zinc-800/50 pb-2">
            <table class="w-full min-w-[700px] text-left border-collapse whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800/50">
                    <tr>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest">Fecha</th>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest">Tipo</th>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest">Categoría</th>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest">Concepto</th>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest">Método</th>
                        <th class="py-3 sm:py-4 px-4 sm:px-5 text-[9px] sm:text-[10px] font-black text-zinc-500 uppercase tracking-widest text-right">Monto</th>
                    </tr>
                </thead>
                <tbody id="tablaFlujoCaja" class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($flujosCaja as $flujo)
                    <tr class="fila-flujo hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors group">
                        <td class="py-3 sm:py-4 px-4 sm:px-5 text-xs sm:text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ $flujo->fecha->format('d M, Y') }}
                        </td>
                        <td class="py-3 sm:py-4 px-4 sm:px-5">
                            @if($flujo->tipo === 'ingreso')
                                <span class="px-2.5 sm:px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-arrow-up text-[9px] sm:text-[10px]"></i> Ingreso
                                </span>
                            @else
                                <span class="px-2.5 sm:px-3 py-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-arrow-down text-[9px] sm:text-[10px]"></i> Egreso
                                </span>
                            @endif
                        </td>
                        <td class="py-3 sm:py-4 px-4 sm:px-5">
                            <span class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-white dark:bg-zinc-800 rounded-lg text-[9px] sm:text-[10px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-widest border border-zinc-200 dark:border-zinc-700/50 shadow-sm">
                                {{ $flujo->categoria }}
                            </span>
                        </td>
                        <td class="py-3 sm:py-4 px-4 sm:px-5 text-xs sm:text-sm font-bold text-zinc-800 dark:text-zinc-200 concepto-celda">
                            {{ $flujo->concepto }}
                        </td>
                        <td class="py-3 sm:py-4 px-4 sm:px-5">
                            <div class="flex items-center gap-1.5 sm:gap-2 text-zinc-500 dark:text-zinc-400">
                                <i class="fas fa-credit-card text-[10px] sm:text-xs opacity-70"></i>
                                <span class="text-[11px] sm:text-xs font-semibold">{{ $flujo->metodo_pago }}</span>
                            </div>
                        </td>
                        <td class="py-3 sm:py-4 px-4 sm:px-5 text-right font-black text-sm sm:text-base {{ $flujo->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $flujo->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($flujo->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 sm:py-16 text-center bg-zinc-50/50 dark:bg-zinc-950/20">
                            <div class="flex flex-col items-center justify-center text-zinc-500 dark:text-zinc-500">
                                <i class="fas fa-folder-open text-3xl sm:text-4xl mb-3 opacity-40 dark:opacity-20"></i>
                                <p class="text-xs sm:text-sm font-medium">No hay registros de flujo de caja aún.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 sm:mt-6 flex justify-between items-center text-xs sm:text-sm overflow-x-auto">
            {{ $flujosCaja->links() }}
        </div>
    </div>

    {{-- RESUMEN POR CATEGORÍAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 relative z-20">
        
        {{-- Resumen Ingresos --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl shadow-lg p-4 sm:p-6 lg:p-8">
            <div class="mb-4 sm:mb-6 flex items-center gap-3 sm:gap-4">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-500 shrink-0">
                    <i class="fas fa-chart-pie text-base sm:text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-lg font-black text-zinc-900 dark:text-zinc-100">Ingresos por Categoría</h3>
                    <p class="text-[10px] sm:text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">Distribución del período</p>
                </div>
            </div>
            <div class="space-y-2.5 sm:space-y-3">
                @forelse($categoriasIngresos as $cat)
                <div class="flex items-center justify-between gap-2 p-3 sm:p-4 bg-zinc-50 dark:bg-zinc-950/40 border border-zinc-100 dark:border-zinc-800/30 rounded-xl sm:rounded-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div class="w-1.5 sm:w-2 h-6 sm:h-8 bg-emerald-400 dark:bg-emerald-500/50 rounded-full shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-zinc-800 dark:text-zinc-200 truncate">{{ $cat->categoria }}</p>
                            <p class="text-[10px] sm:text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">{{ $cat->cantidad }} transacciones</p>
                        </div>
                    </div>
                    <p class="text-sm sm:text-lg font-black text-emerald-600 dark:text-emerald-400 shrink-0">$ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <div class="py-6 sm:py-8 text-center bg-zinc-50 dark:bg-zinc-950/20 rounded-xl sm:rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800">
                    <p class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-500">Sin ingresos este período</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Resumen Egresos --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl shadow-lg p-4 sm:p-6 lg:p-8">
            <div class="mb-4 sm:mb-6 flex items-center gap-3 sm:gap-4">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-500 shrink-0">
                    <i class="fas fa-chart-pie text-base sm:text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-lg font-black text-zinc-900 dark:text-zinc-100">Egresos por Categoría</h3>
                    <p class="text-[10px] sm:text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">Distribución del período</p>
                </div>
            </div>
            <div class="space-y-2.5 sm:space-y-3">
                @forelse($categoriasEgresos as $cat)
                <div class="flex items-center justify-between gap-2 p-3 sm:p-4 bg-zinc-50 dark:bg-zinc-950/40 border border-zinc-100 dark:border-zinc-800/30 rounded-xl sm:rounded-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div class="w-1.5 sm:w-2 h-6 sm:h-8 bg-rose-400 dark:bg-rose-500/50 rounded-full shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-zinc-800 dark:text-zinc-200 truncate">{{ $cat->categoria }}</p>
                            <p class="text-[10px] sm:text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">{{ $cat->cantidad }} transacciones</p>
                        </div>
                    </div>
                    <p class="text-sm sm:text-lg font-black text-rose-600 dark:text-rose-400 shrink-0">$ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <div class="py-6 sm:py-8 text-center bg-zinc-50 dark:bg-zinc-950/20 rounded-xl sm:rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800">
                    <p class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-500">Sin egresos este período</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@if(auth()->user()->tienePermiso('finanzas.crear'))
    @include('admin.finanzas.modal-crear-gasto')
@endif

@if(auth()->user()->tienePermiso('finanzas.editar'))
    @include('admin.finanzas.modal-crear-nomina')
@endif

@include('partials.teclado-virtual')
<script src="{{ asset('js/teclado-virtual.js') }}"></script>

<script>
    function openTailwindModal(modalId, containerId) {
        const modal = document.getElementById(modalId);
        const container = document.getElementById(containerId);
        if (!modal || !container) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeTailwindModal(modalId, containerId) {
        const modal = document.getElementById(modalId);
        const container = document.getElementById(containerId);
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }, 200);
    }

    function openModalCrearGasto() { openTailwindModal('modalCrearGasto', 'createGastoContainer'); }
    function closeCreateGastoModal() { closeTailwindModal('modalCrearGasto', 'createGastoContainer'); }
    function openModalCrearNomina() { openTailwindModal('modalCrearNomina', 'createNominaContainer'); }
    function closeCreateNominaModal() { closeTailwindModal('modalCrearNomina', 'createNominaContainer'); }

    function actualizarSueldo() {
        const select = document.getElementById('empleadoSelect');
        const inputSueldo = document.getElementById('sueldoBase');
        if (!select || !inputSueldo) return;
        const opcionSeleccionada = select.options[select.selectedIndex];
        const sueldo = opcionSeleccionada ? parseFloat(opcionSeleccionada.dataset.sueldo) || 0 : 0;
        inputSueldo.value = sueldo.toFixed(2);
        calcularMonto();
    }

    function calcularMonto() {
        const inputSueldo = document.getElementById('sueldoBase');
        const inputBonos = document.querySelector('input[name="bonos"]');
        const inputDeducciones = document.querySelector('input[name="deducciones"]');
        const spanMontoNeto = document.getElementById('montoNeto');
        if (!spanMontoNeto) return;
        const sueldo = parseFloat(inputSueldo ? inputSueldo.value : 0) || 0;
        const bonos = parseFloat(inputBonos ? inputBonos.value : 0) || 0;
        const deducciones = parseFloat(inputDeducciones ? inputDeducciones.value : 0) || 0;
        const montoNeto = sueldo + bonos - deducciones;
        spanMontoNeto.textContent = montoNeto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modales = ['modalCrearGasto', 'modalCrearNomina']; 
        modales.forEach(id => {
            const modalElement = document.getElementById(id);
            if (modalElement) document.body.appendChild(modalElement);
        });

        const tecladoVirtual = document.getElementById('tecladoVirtualOverlay');
        if (tecladoVirtual) document.body.appendChild(tecladoVirtual);

        const buscador = document.getElementById('buscadorFlujo');
        const filas = document.querySelectorAll('.fila-flujo, .fila-flujo-movil');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                filas.forEach(fila => {
                    const celdaConcepto = fila.querySelector('.concepto-celda') || fila.querySelector('.concepto-celda-movil');
                    if (!celdaConcepto) return;
                    fila.classList.toggle('hidden', !celdaConcepto.textContent.toLowerCase().includes(term));
                });
            });
        }
    });
</script>
@endsection