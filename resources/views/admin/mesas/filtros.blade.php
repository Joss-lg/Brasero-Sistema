{{-- FILTROS Y CONTROLES (Vista Lista) --}}
<div id="controlesLista" class="hidden flex-col lg:flex-row gap-3 justify-between items-start flex-shrink-0">
    <div class="flex gap-2 overflow-x-auto no-scrollbar flex-nowrap lg:flex-wrap p-1 rounded-xl bg-[var(--card-color)] border border-[var(--border-color)] w-full lg:w-auto -mx-1 px-1 lg:mx-0 lg:px-1">
        <button onclick="filtrarMesas('todos')" 
                class="shrink-0 px-3 py-2 lg:py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-[#b74309] hover:bg-[#8f3207] text-white shadow-sm shadow-[#b74309]/30 filtro-btn cursor-pointer" 
                data-filtro="todos">
            Todas
        </button>
        <button onclick="filtrarMesas('libre')" 
                class="shrink-0 px-3 py-2 lg:py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn cursor-pointer" 
                data-filtro="libre">
            Libres
        </button>
        <button onclick="filtrarMesas('ocupada')" 
                class="shrink-0 px-3 py-2 lg:py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn cursor-pointer" 
                data-filtro="ocupada">
            Ocupadas
        </button>
        <button onclick="filtrarMesas('reservada')" 
                class="shrink-0 px-3 py-2 lg:py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn cursor-pointer" 
                data-filtro="reservada">
            Reservadas
        </button>
    </div>
</div>