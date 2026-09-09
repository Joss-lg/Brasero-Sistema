{{-- resources/views/admin/cocina/partials/comandas.blade.php --}}

{{-- Estilos para las animaciones de parpadeo --}}
<style>
    @keyframes parpadeoAmarillo {
        0%, 100% { border-color: #f59e0b; box-shadow: 0 0 15px rgba(245, 158, 11, 0.5); }
        50% { border-color: transparent; box-shadow: none; }
    }
    @keyframes parpadeoRojo {
        0%, 100% { border-color: #ef4444; box-shadow: 0 0 20px rgba(239, 68, 68, 0.8); background-color: rgba(239, 68, 68, 0.08); }
        50% { border-color: transparent; box-shadow: none; background-color: transparent; }
    }
    .alerta-amarilla {
        animation: parpadeoAmarillo 1.5s infinite !important;
        border-width: 2px !important;
    }
    .alerta-roja {
        animation: parpadeoRojo 1s infinite !important;
        border-width: 2px !important;
    }
</style>

@if($comandas->isEmpty())
    <div class="rounded-[24px] px-6 py-16 sm:py-24 text-center border shadow-xl mt-6 sm:mt-8 transition-colors" 
         style="background-color: var(--card-color); border-color: var(--border-color);">
        <i class="fas fa-check-double text-5xl text-emerald-500 mb-4"></i>
        <h2 class="text-xl sm:text-2xl font-black" style="color: var(--text-color);">¡{{ $areaSeleccionada }} Despejada!</h2>
    </div>
@else
    <div class="grid gap-3 sm:gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-4 sm:mt-8 items-start w-full">
        @foreach($comandas as $comanda)
            @php
                // Parseo seguro de fecha
                $fechaCarbon = !empty($comanda->creado_en) ? \Carbon\Carbon::parse($comanda->creado_en) : now();
                $minutosEspera = $fechaCarbon->diffInMinutes(now());

                // Determinamos la clase de parpadeo según los minutos
                $claseAlerta = '';
                if ($minutosEspera >= 15) {
                    $claseAlerta = 'alerta-roja';
                } elseif ($minutosEspera >= 10) {
                    $claseAlerta = 'alerta-amarilla';
                }

                // Formateo del número de mesa para evitar "Mesa mesa 45"
                $numMesa = $comanda->mesa->numero ?? 'S/N';
                $labelMesa = \Illuminate\Support\Str::startsWith(strtolower($numMesa), 'mesa') 
                    ? $numMesa 
                    : 'Mesa ' . $numMesa;

                // Delivery
                $esDelivery  = $comanda->mesa && $comanda->mesa->esDelivery();
                $plataforma  = $esDelivery ? optional($comanda->mesa->plataformaDelivery)->nombre : null;
                $colorBorde  = $esDelivery ? 'border-t-orange-500' : 'border-t-emerald-500';
            @endphp

            <article class="w-full rounded-[20px] border border-t-[6px] {{ $colorBorde }} shadow-lg flex flex-col h-full overflow-hidden relative transition-all duration-300 comanda-card {{ $claseAlerta }}"
                     style="background-color: var(--card-color); border-color: var(--border-color);"
                     data-comanda-id="{{ $comanda->id }}"
                     data-lote="{{ $comanda->detalles->first()?->lote_envio ?? $comanda->id }}"
                     data-tiempo-inicio="{{ $fechaCarbon->getTimestampMs() }}">
                
                {{-- Cabecera comanda --}}
                <div class="p-4 border-b min-w-0 flex items-start justify-between gap-2" style="border-bottom-color: var(--border-color);">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="font-black text-lg truncate capitalize" style="color: var(--text-color);">{{ $labelMesa }}</h3>
                            @if($esDelivery)
                                <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-orange-500 text-white text-[9px] font-black uppercase tracking-wider shadow-sm">
                                    <i class="fas fa-motorcycle text-[8px]"></i>
                                    {{ $plataforma ?? 'Delivery' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs truncate" style="color: var(--text-muted);">Mesero: {{ $comanda->mesero->nombre ?? 'N/A' }}</p>
                    </div>
                    <span class="tiempo-espera shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wide whitespace-nowrap"
                          style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);"
                          data-creado="{{ $fechaCarbon->toIso8601String() }}">
                        <i class="fas fa-clock"></i>
                        <span class="tiempo-texto">--</span>
                    </span>
                </div>

                {{-- Detalles de productos --}}
                <div class="p-4 flex-1 min-w-0">
                    <ul class="space-y-2">
                        @foreach($comanda->detalles as $detalle)
                            @php
                                $tiempoClases = [
                                    'sin-tiempo'     => ['label' => 'S', 'clase' => 'border text-[9px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md', 'style' => 'background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);'],
                                    'primer-tiempo'  => ['label' => '1', 'clase' => 'text-[#b74309] dark:text-[#e8946a] bg-[#b74309]/10 border border-[#b74309]/30 text-[9px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md', 'style' => ''],
                                    'segundo-tiempo' => ['label' => '2', 'clase' => 'text-purple-500 bg-purple-500/10 border border-purple-500/30 text-[9px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md', 'style' => ''],
                                    'tercer-tiempo'  => ['label' => '3', 'clase' => 'text-pink-500 bg-pink-500/10 border border-pink-500/30 text-[9px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md', 'style' => ''],
                                ];
                                $tInfo = $tiempoClases[$detalle->tiempo] ?? null;
                            @endphp
                            <li class="flex flex-col text-sm gap-1.5 detalle-item"
                                data-detalle-id="{{ $detalle->id }}"
                                data-estado="{{ $detalle->estado_preparacion }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold break-words flex flex-wrap items-center gap-1.5 nombre-producto transition-all {{ in_array($detalle->estado_preparacion, ['listo_cocina','servida']) ? 'line-through opacity-40' : '' }}"
                                          style="{{ in_array($detalle->estado_preparacion, ['listo_cocina','servida']) ? 'color: var(--text-muted);' : 'color: var(--text-color);' }}">
                                        {{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto Eliminado' }}
                                        @if($tInfo)
                                            <span class="inline-flex items-center gap-1 {{ $tInfo['clase'] }}" style="{{ $tInfo['style'] }}">
                                                <i class="fas fa-clock"></i>Tiempo {{ $tInfo['label'] }}
                                            </span>
                                        @endif
                                        @if($detalle->gramaje)
                                            @php
                                                $gramajeLimpio = rtrim(rtrim(number_format((float) $detalle->gramaje, 2, '.', ''), '0'), '.');
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wide text-orange-500 bg-orange-500/10 border border-orange-500/30 px-1.5 py-0.5 rounded-md">
                                                <i class="fas fa-weight-hanging"></i>{{ $gramajeLimpio }}g
                                            </span>
                                        @endif
                                    </span>

                                    {{-- Boton de tachar --}}
                                    <button type="button"
                                        class="btn-tachar shrink-0 w-8 h-8 rounded-xl border-2 transition-all flex items-center justify-center cursor-pointer {{ in_array($detalle->estado_preparacion, ['listo_cocina','servida']) ? 'bg-emerald-500 border-emerald-500 text-white scale-95' : 'hover:border-emerald-500 hover:text-emerald-500 hover:scale-105' }}"
                                        style="{{ !in_array($detalle->estado_preparacion, ['listo_cocina','servida']) ? 'border-color: var(--border-color); color: var(--text-muted);' : '' }}"
                                        title="{{ in_array($detalle->estado_preparacion, ['listo_cocina','servida']) ? 'Desmarcar' : 'Marcar como listo' }}">
                                        <i class="fas fa-check text-[11px]"></i>
                                    </button>
                                </div>
                                @if($detalle->notas)
                                    <span class="flex items-start gap-2 px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/30 text-red-500 text-xs font-bold w-full break-words leading-snug">
                                        <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
                                        <span>{{ $detalle->notas }}</span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Formulario con ÚNICO Botón para finalizar la comanda --}}
                <div class="p-3 sm:p-4 border-t" style="background-color: var(--bg-color); border-top-color: var(--border-color);">
                    <form action="{{ route('admin.cocina.orden.estado', $comanda->orden_id) }}" method="POST" class="form-avanzar-estado">
                        @csrf @method('PATCH')
                        <input type="hidden" name="estado" value="servida">
                        <input type="hidden" name="lote" value="{{ $comanda->lote }}">
                        <input type="hidden" name="area" value="{{ strtolower($areaSeleccionada) }}">
                        <button type="submit"
                            class="w-full py-3.5 rounded-xl font-black uppercase text-[12px] tracking-[0.1em] transition-all active:scale-95 bg-emerald-500 hover:bg-emerald-400 text-white shadow-md cursor-pointer">
                            MARCAR COMO LISTA
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
@endif