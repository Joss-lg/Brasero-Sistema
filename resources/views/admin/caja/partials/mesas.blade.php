{{--
    resources/views/admin/caja/partials/mesas.blade.php

    Tarjetas de las mesas/pedidos con cuenta abierta.

    Vive en un parcial porque lo usan DOS lugares: el render inicial de la
    pantalla y el endpoint admin.caja.api.mesas, que devuelve este mismo HTML
    para el auto-refresco cada 5s.

    Recibe: $mesas
--}}
@forelse ($mesas as $mesa)
    @php
        $cuenta = $mesa->ordenesActivas->first() ?? null; 
    @endphp

    @if($cuenta)
        {{-- MESA CON ORDEN ACTIVA --}}
        <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" 
           data-mesa-status="{{ $mesa->estado }}"
           class="group relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-emerald-500/30 bg-emerald-500/5 shadow-sm hover:shadow-lg active:scale-[0.98] cursor-pointer transition-all duration-300 hover:-translate-y-1 overflow-hidden p-3.5 sm:p-6">
            <div class="relative z-10 flex-1 flex flex-col w-full">
                <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                    <div class="w-full">
                        <h3 class="text-base sm:text-2xl font-black tracking-tight transition-colors group-hover:text-emerald-500 truncate" style="color: var(--text-color);">
                            {{ $mesa->numero }}
                        </h3>
                        @if($mesa->esDelivery())
                            <p class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md text-white mt-0.5"
                               style="background-color: {{ $mesa->plataformaDelivery->color ?? '#f97316' }}">
                                <i class="fas fa-motorcycle"></i> {{ $mesa->plataformaDelivery->nombre ?? 'Delivery' }}
                            </p>
                        @else
                            <p class="text-[10px] sm:text-xs font-semibold" style="color: var(--text-muted);">Cap. {{ $mesa->capacidad }} p.</p>
                        @endif
                    </div>
                </div>
                <div class="mt-auto w-full space-y-2 sm:space-y-3">
                    <div class="rounded-xl sm:rounded-2xl p-2.5 sm:p-4 flex justify-between items-center w-full border border-emerald-500/30 shadow-inner" style="background-color: var(--card-color);">
                        <p class="text-sm sm:text-xl font-black text-emerald-500">${{ number_format($mesa->total_real ?? 0, 2) }}</p>
                    </div>
                    <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-black text-xs sm:text-base bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition-all duration-200 uppercase tracking-wider">
                        <span class="inline">Cobrar</span>
                    </div>
                </div>
            </div>
        </a>
    @else
        {{-- MESA SIN ORDEN --}}
        <div data-mesa-status="{{ $mesa->estado }}"
           class="relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-rose-500/20 bg-rose-500/5 shadow-sm overflow-hidden p-3.5 sm:p-6">
            <div class="relative z-10 flex-1 flex flex-col w-full">
                <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                    <div class="w-full">
                        <h3 class="text-base sm:text-2xl font-black tracking-tight truncate opacity-50" style="color: var(--text-color);">{{ $mesa->numero }}</h3>
                        <p class="text-[10px] sm:text-xs font-semibold opacity-50" style="color: var(--text-muted);">Cap. {{ $mesa->capacidad }} p.</p>
                    </div>
                </div>
                <div class="mt-auto w-full">
                    <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-black text-[10px] sm:text-xs uppercase tracking-wider border border-rose-500/20 bg-rose-500/10 text-rose-500 cursor-not-allowed select-none text-center leading-tight px-1">
                        <i class="fas fa-ban mr-1.5"></i> <span>Mesa sin orden</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
@empty
    <div class="col-span-2 lg:col-span-3 xl:col-span-4 text-center w-full py-14">
        <i class="fas fa-mug-hot text-4xl mb-3 opacity-30" style="color: var(--text-muted);"></i>
        <p class="font-bold" style="color: var(--text-muted);">No hay cuentas abiertas</p>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Aquí aparecerán las mesas y los pedidos de delivery en cuanto tengan consumo.
        </p>
    </div>
@endforelse