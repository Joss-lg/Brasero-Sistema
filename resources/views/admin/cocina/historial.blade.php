@extends('layouts.admin')

@section('title', 'Historial de Comandas | ' . $area . ' - El Brasero')

@section('content')
<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-5xl mx-auto space-y-5" style="background-color: var(--bg-color);">

    {{-- CABECERA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.cocina.index') }}"
               class="text-xs font-bold transition-colors hover:text-[#b74309]"
               style="color: var(--text-muted);">
                &larr; Volver a {{ $area }}
            </a>
            <h1 class="text-xl sm:text-3xl font-black tracking-tight mt-1" style="color: var(--text-color);">
                Historial de comandas
            </h1>
            <p class="text-xs sm:text-sm mt-0.5" style="color: var(--text-muted);">
                Lo que llegó a <span class="font-bold" style="color: var(--text-color);">{{ $area }}</span>
                el {{ $fecha }}.
                Este registro es inmutable: muestra el pedido tal como llegó al momento del envío.
            </p>
        </div>

        {{-- Selector de area --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.cocina.historial', ['area' => 'Cocina']) }}"
               class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ $areaSeleccionada !== 'Barra' ? 'bg-[#b74309] text-white shadow-md shadow-[#b74309]/20' : 'border hover:border-[#b74309]' }}"
               style="{{ $areaSeleccionada === 'Barra' ? 'background-color: var(--card-color); border-color: var(--border-color); color: var(--text-muted);' : '' }}">
                Cocina
            </a>
            <a href="{{ route('admin.cocina.historial', ['area' => 'Barra']) }}"
               class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Barra' ? 'bg-[#b74309] text-white shadow-md shadow-[#b74309]/20' : 'border hover:border-[#b74309]' }}"
               style="{{ $areaSeleccionada !== 'Barra' ? 'background-color: var(--card-color); border-color: var(--border-color); color: var(--text-muted);' : '' }}">
                Barra
            </a>
        </div>
    </div>

    {{-- AVISO cuando no hay nada --}}
    @if($jobs->isEmpty())
        <div class="text-center py-16">
            <i class="fas fa-clipboard-list text-4xl mb-3 opacity-30" style="color: var(--text-muted);"></i>
            <p class="font-bold" style="color: var(--text-muted);">Sin comandas registradas hoy en {{ $area }}</p>
            <p class="text-xs mt-1" style="color: var(--text-muted);">
                Aquí aparecerá cada envío a cocina con el detalle exacto de lo que se pidió.
            </p>
        </div>

    @else
        {{-- Una tarjeta por LOTE DE ENVIO (cada vez que el mesero presionó "Enviar") --}}
        <div class="space-y-3">
            @foreach($jobs as $lote => $lotejobs)
                @php
                    $primerJob = $lotejobs->first();
                    $orden = $primerJob?->orden;
                    $mesa = optional($orden?->mesa)->numero ?? '—';
                    $mesero = optional($orden?->mesero)->nombre ?? optional($orden?->mesero)->name ?? 'Sin asignar';
                    $hora = $primerJob?->created_at?->format('H:i');
                @endphp

                <div class="rounded-2xl overflow-hidden border transition-colors" style="background-color: var(--card-color); border-color: var(--border-color);">

                    {{-- Encabezado del lote --}}
                    <button type="button"
                        class="btn-lote w-full px-4 py-3 flex items-center justify-between gap-3 text-left hover:opacity-90 transition-opacity cursor-pointer">

                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background-color: var(--input-bg);">
                                <i class="fas fa-receipt text-sm" style="color: var(--text-muted);"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-black text-sm" style="color: var(--text-color);">
                                    Mesa {{ $mesa }}
                                    <span class="font-normal text-xs ml-1" style="color: var(--text-muted);">· {{ $mesero }}</span>
                                </p>
                                <p class="text-[11px]" style="color: var(--text-muted);">
                                    {{ $hora }} hrs
                                    · lote <span class="font-mono">{{ substr($lote, 0, 12) }}</span>
                                </p>
                            </div>
                        </div>

                        <i class="fas fa-chevron-down text-xs shrink-0 transition-transform icono-lote" style="color: var(--text-muted);"></i>
                    </button>

                    {{-- Contenido del ticket (el texto exacto que llegó a cocina) --}}
                    <div class="contenido-lote border-t" style="border-top-color: var(--border-color);">
                        @foreach($lotejobs as $job)
                            <div class="px-4 py-3 {{ !$loop->first ? 'border-t' : '' }}" style="{{ !$loop->first ? 'border-top-color: var(--border-color);' : '' }}">

                                {{-- Estado del job --}}
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">
                                        {{ $job->area ?? $area }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                        {{ $job->estado === 'impreso' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                        {{ $job->estado === 'impreso' ? 'Impreso' : 'Pendiente' }}
                                    </span>
                                </div>

                                {{-- Texto del ticket en preformateado --}}
                                <pre class="text-xs font-mono border rounded-xl px-4 py-3 whitespace-pre-wrap overflow-x-auto leading-relaxed"
                                     style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">{{ $job->contenido }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-[11px] leading-snug text-center pb-4" style="color: var(--text-muted);">
            El contenido de cada comanda es exactamente lo que llegó al momento del envío.
            Si existe discrepancia entre lo que el cliente dice haber pedido y lo que aparece aquí,
            este registro es el referente oficial.
        </p>
    @endif
</div>

{{-- Botón flotante para volver a cocina --}}
<a href="{{ route('admin.cocina.index') }}"
   class="fixed bottom-5 right-5 w-12 h-12 rounded-full bg-[#b74309] hover:bg-[#8f3207] text-white flex items-center justify-center shadow-xl shadow-[#b74309]/30 hover:scale-105 active:scale-95 transition-all z-50"
   title="Volver a {{ $area }}">
    <i class="fas fa-arrow-left text-sm"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Plegar/desplegar cada lote. El primero arranca abierto.
    document.querySelectorAll('.btn-lote').forEach((btn, i) => {
        const contenido = btn.nextElementSibling;
        const icono = btn.querySelector('.icono-lote');

        // El mas reciente (primero en el DOM) viene abierto.
        if (i !== 0) {
            contenido.classList.add('hidden');
            icono.style.transform = 'rotate(-90deg)';
        }

        btn.addEventListener('click', () => {
            const oculto = contenido.classList.toggle('hidden');
            icono.style.transform = oculto ? 'rotate(-90deg)' : 'rotate(0deg)';
        });
    });
});
</script>
@endsection