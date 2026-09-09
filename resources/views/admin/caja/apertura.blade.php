@extends('layouts.admin')

@section('content')
<style>
    /* Solo aplicamos el truco de subir la tarjeta en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #aperturaCajaWrapper {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }

        body.teclado-virtual-abierto #aperturaCajaCard {
            max-height: calc(100dvh - 340px) !important;
            overflow-y: auto !important;
        }
    }
</style>

<div id="aperturaCajaWrapper" class="flex items-center justify-center min-h-[80vh] px-4 py-8" style="background-color: var(--bg-color);">
    <div id="aperturaCajaCard" class="max-w-md w-full rounded-2xl sm:rounded-[2rem] shadow-xl p-6 sm:p-8 border transition-all duration-300"
         style="background-color: var(--card-color); border-color: var(--border-color);">
        
        <div class="text-center mb-6">
            <div class="inline-flex p-3 rounded-2xl mb-3 shadow-inner border"
                 style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.2); color: #b74309;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight" style="color: var(--text-color);">Apertura de Caja</h2>
            <p class="text-xs sm:text-sm font-medium mt-1" style="color: var(--text-muted);">
                Para comenzar a gestionar mesas y registrar cobros, es necesario iniciar un turno operativo.
            </p>
        </div>

        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 px-4 py-3 rounded-xl mb-4 text-xs sm:text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-4 py-3 rounded-xl mb-4 text-xs sm:text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.caja.abrir') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Dropdown Personalizado: Turno --}}
            <div x-data="{
                open: false,
                selected: '{{ old('turno', '') }}',
                selectedLabel: '{{ old('turno') ? old('turno') : 'Seleccionar turno...' }}',
                options: [
                    { value: 'Matutino', label: 'Matutino' },
                    { value: 'Vespertino', label: 'Vespertino' }
                ],
                select(opt) {
                    this.selected = opt.value;
                    this.selectedLabel = opt.label;
                    this.open = false;
                    const input = document.getElementById('input_turno');
                    if (input) input.value = opt.value;
                }
            }">
                <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Seleccionar Turno</label>
                
                <input type="hidden" name="turno" id="input_turno" :value="selected" value="{{ old('turno', '') }}" required>

                <div class="relative">
                    <button type="button" @click="open = !open"
                        class="w-full h-12 px-4 rounded-xl text-sm font-bold border flex items-center justify-between outline-none transition-all focus:border-[#b74309] cursor-pointer"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        <span x-text="selectedLabel" :class="{ 'opacity-60 font-normal': !selected }">Seleccionar turno...</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }" style="color: var(--text-muted);"></i>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition
                        class="absolute z-50 w-full mt-2 rounded-xl shadow-2xl py-2 border overflow-hidden"
                        style="background-color: var(--card-color); border-color: var(--border-color); display: none;">
                        <template x-for="opt in options" :key="opt.value">
                            <div @click="select(opt)"
                                class="px-4 py-2.5 text-sm font-bold cursor-pointer transition-colors hover:bg-[#b74309]/10"
                                :class="{ 'bg-[#b74309]/15 text-[#b74309]': selected === opt.value }"
                                style="color: var(--text-color);">
                                <span x-text="opt.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
                @error('turno')
                    <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="monto_inicial" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Monto Inicial (Fondo de Caja)</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-sm font-bold" style="color: var(--text-muted);">$</span>
                    </div>
                    <input type="text" name="monto_inicial" id="monto_inicial" pattern="[0-9]*\.?[0-9]*" required data-teclado="numerico" data-teclado-titulo="Monto Inicial" inputmode="none"
                        value="{{ old('monto_inicial', '0.00') }}"
                        class="w-full h-12 pl-7 pr-4 text-base font-bold rounded-xl border outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10 @error('monto_inicial') border-rose-500 @enderror"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);"
                        placeholder="0.00"
                        onfocus="this.select()">
                </div>
                @error('monto_inicial')
                    <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-lg shadow-[#b74309]/20 text-xs font-black uppercase tracking-widest text-white bg-[#b74309] hover:bg-[#8f3207] active:scale-[0.98] transition-all outline-none cursor-pointer">
                Iniciar Turno e Ir a Mesas
            </button>
        </form>

        {{-- HISTORIAL DE TURNOS CERRADOS --}}
        @if(($turnosCerrados ?? collect())->isNotEmpty())
            <div class="mt-8 pt-6 border-t" style="border-top-color: var(--border-color);">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider" style="color: var(--text-muted);">
                        Últimos turnos cerrados
                    </h3>
                    <a href="{{ route('historial.index') }}"
                       class="text-xs font-bold text-[#b74309] hover:underline">
                        Ver todo
                    </a>
                </div>

                <ul class="space-y-2">
                    @foreach($turnosCerrados as $turno)
                        <li>
                            <a href="{{ route('historial.show', $turno->id) }}"
                               class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl border transition-all hover:border-[#b74309]/50"
                               style="background-color: var(--input-bg); border-color: var(--border-color);">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold truncate" style="color: var(--text-color);">
                                        {{ $turno->updated_at?->format('d/m/Y') }}
                                        <span class="font-normal text-xs" style="color: var(--text-muted);">
                                            · {{ $turno->user->nombre ?? 'Sin usuario' }}
                                        </span>
                                    </p>
                                    <p class="text-[11px]" style="color: var(--text-muted);">
                                        Contado: ${{ number_format($turno->monto_final_real ?? 0, 2) }}
                                    </p>
                                </div>

                                @php $dif = (float) ($turno->diferencia ?? 0); @endphp
                                <span class="shrink-0 text-xs font-black px-2 py-1 rounded-lg
                                    {{ abs($dif) < 0.01
                                        ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                                        : ($dif < 0
                                            ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400'
                                            : 'bg-amber-500/15 text-amber-600 dark:text-amber-400') }}">
                                    @if(abs($dif) < 0.01)
                                        Cuadrada
                                    @elseif($dif < 0)
                                        Faltante ${{ number_format(abs($dif), 2) }}
                                    @else
                                        Sobrante ${{ number_format($dif, 2) }}
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });
</script>
@endsection