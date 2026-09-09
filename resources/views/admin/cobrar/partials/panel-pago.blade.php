{{-- panel-pago.blade.php --}}
@php $anchoDerecha = $anchoDerecha ?? 'lg:w-3/5'; @endphp
<div class="w-full {{ $anchoDerecha }} p-4 lg:p-8 overflow-hidden h-full transition-colors duration-300" style="background-color: var(--bg-color);">
    <div class="max-w-xl mx-auto h-full flex flex-col">

        {{-- Inputs de Estado Ocultos --}}
        <input id="mesa-id" type="hidden" value="{{ $mesa->id }}">
        <input id="orden-id" type="hidden" value="{{ $ordenes->first()->id ?? '' }}">
        <input id="metodo-pago" type="hidden" value="Efectivo">
        {{-- Cuando la mesa está dividida, aquí va el id de la persona seleccionada --}}
        <input id="cuenta-division-id" type="hidden" value="">

        @if(!empty($division))
            <div class="mb-2 p-3 rounded-2xl text-center border" style="background-color: rgba(183, 67, 9, 0.1); border-color: rgba(183, 67, 9, 0.25);">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#b74309] dark:text-[#e8946a]" id="aviso-division-panel">
                    Selecciona una persona en el panel izquierdo para cobrar su parte
                </p>
            </div>
        @endif

        <div class="flex-1 space-y-6 overflow-y-auto pr-2 custom-scrollbar">

            {{-- 1. Selector de Método --}}
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <p class="uppercase tracking-[0.35em] text-[10px] font-black mb-3" style="color: var(--text-muted);">Método de pago</p>
                    <button id="btn-abrir-modal-metodo" type="button" 
                        class="inline-flex items-center gap-3 rounded-full px-6 py-3 font-black uppercase tracking-[0.25em] border transition-all cursor-pointer hover:opacity-90 active:scale-95 shadow-sm"
                        style="background-color: rgba(183, 67, 9, 0.12); border-color: rgba(183, 67, 9, 0.4); color: #b74309;">
                        <i class="fas fa-money-bill-wave text-lg"></i>
                        <span id="metodo-pago-label">Efectivo</span>
                    </button>
                </div>
            </div>

            {{-- Sección de Referencia (para pagos con tarjeta/transferencia) --}}
            <div id="non-cash-section" class="hidden space-y-4 border rounded-[2.5rem] p-6 shadow-sm" style="background-color: var(--card-color); border-color: var(--border-color);">
                <label class="uppercase tracking-[0.2em] text-[10px] font-black block text-center" style="color: var(--text-muted);">Referencia de operación</label>
                <input id="referencia" type="text" placeholder="Referencia de operación"
                    class="touch-input w-full rounded-2xl border p-4 outline-none transition font-bold text-sm focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" />
            </div>

            {{-- 2. Display de Montos --}}
            <div class="relative bg-gradient-to-br from-zinc-900 to-black border border-white/10 rounded-[2rem] overflow-hidden shadow-2xl">
                {{-- Línea de acento superior El Brasero --}}
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#b74309] via-[#e8946a] to-[#b74309] opacity-90"></div>
                <div class="px-6 pt-6 pb-5 text-center">
                    <p class="text-zinc-400 text-[9px] font-black uppercase tracking-[0.3em] mb-3">Monto a cobrar</p>
                    <div class="text-5xl font-black text-white tracking-tighter" id="monto-input">$0.00</div>
                    <div class="mt-4 flex items-center justify-center gap-6 text-xs">
                        <div class="flex flex-col items-center gap-0.5">
                            <span class="text-zinc-400 uppercase tracking-widest text-[9px] font-bold">Total</span>
                            <strong class="text-white font-black text-sm" id="total-pagar-derecha">${{ number_format($totalPagar ?? 0, 2) }}</strong>
                        </div>
                        <div class="w-px h-8 bg-white/15"></div>
                        <div class="flex flex-col items-center gap-0.5">
                            <span class="text-zinc-400 uppercase tracking-widest text-[9px] font-bold">Cambio</span>
                            <strong class="text-emerald-400 font-black text-sm" id="display-cambio">$0.00</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Teclado Numérico --}}
            <div id="cash-section" class="select-none">
                {{-- Filas 1-9 --}}
                <div class="grid grid-cols-3 gap-2 mb-2">
                    @foreach(['1','2','3','4','5','6','7','8','9'] as $key)
                        <button type="button"
                            class="btn-tecla h-14 rounded-2xl font-black text-lg border shadow-sm transition-all duration-100 cursor-pointer active:scale-95 hover:border-[#b74309]/50 hover:text-[#b74309]"
                            style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);"
                            data-value="{{ $key }}">{{ $key }}</button>
                    @endforeach
                </div>
                {{-- Fila . / 0 / 00 / DEL --}}
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['.','0','00'] as $key)
                        <button type="button"
                            class="btn-tecla h-14 rounded-2xl font-black text-lg border shadow-sm transition-all duration-100 cursor-pointer active:scale-95 hover:border-[#b74309]/50 hover:text-[#b74309]"
                            style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);"
                            data-value="{{ $key }}">{{ $key }}</button>
                    @endforeach
                    <button type="button"
                        class="btn-tecla h-14 rounded-2xl font-black bg-rose-500/10 border border-rose-500/20 text-rose-500 shadow-sm hover:bg-rose-500/20 active:scale-95 transition-all duration-100 flex items-center justify-center gap-1.5 cursor-pointer"
                        data-value="DEL">
                        <i class="fas fa-delete-left text-base"></i>
                    </button>
                </div>
            </div>

            {{-- Selector de Propina --}}
            <div class="border rounded-[2rem] p-5 shadow-sm transition-colors" style="background-color: var(--card-color); border-color: var(--border-color);">
                <p class="uppercase tracking-[0.25em] text-[10px] font-black mb-3 text-center" style="color: var(--text-muted);">
                    ¿Cuánta propina desea dejar?
                </p>

                @if(!empty($division))
                    <p class="text-center text-[9px] text-[#b74309] dark:text-[#e8946a] font-black uppercase mb-3">
                        <i class="fas fa-info-circle"></i> Se repartirá entre las personas que aún no han pagado
                    </p>
                @endif

                <div class="grid grid-cols-4 gap-2 mb-3" id="propina-porcentaje-botones">
                    <button type="button" class="propina-btn h-12 rounded-xl border font-black text-xs transition-all cursor-pointer hover:border-[#b74309] hover:text-[#b74309]" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" data-porcentaje="0">Sin propina</button>
                    <button type="button" class="propina-btn h-12 rounded-xl border font-black text-xs transition-all cursor-pointer hover:border-[#b74309] hover:text-[#b74309]" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" data-porcentaje="10">10%</button>
                    <button type="button" class="propina-btn h-12 rounded-xl border font-black text-xs transition-all cursor-pointer hover:border-[#b74309] hover:text-[#b74309]" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" data-porcentaje="15">15%</button>
                    <button type="button" class="propina-btn h-12 rounded-xl border font-black text-xs transition-all cursor-pointer hover:border-[#b74309] hover:text-[#b74309]" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" data-porcentaje="20">20%</button>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold" style="color: var(--text-muted);">$</span>
                        <input id="propina-manual-input" type="number" step="0.01" min="0" placeholder="Otro monto"
                            data-teclado="numerico"
                            class="touch-input pl-7 pr-4 h-12 rounded-xl border text-sm font-bold outline-none transition-all focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);" />
                    </div>
                    <button type="button" id="btn-aplicar-propina-manual" 
                        class="h-12 px-5 rounded-xl bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-xs uppercase tracking-wider transition-all shadow-sm active:scale-95 cursor-pointer">
                        Aplicar
                    </button>
                </div>

                <p class="text-center text-[11px] mt-3" style="color: var(--text-muted);">
                    Propina actual: <strong style="color: var(--text-color);" id="propina-actual-display">${{ number_format($orden->propina ?? 0, 2) }}</strong>
                </p>
            </div>

            {{-- Botones Finales --}}
            <div class="grid grid-cols-2 gap-4 pb-12">
                <button id="btn-ticket" type="button" 
                    class="font-black py-5 rounded-2xl border transition-all cursor-pointer hover:opacity-80 active:scale-95 text-xs uppercase tracking-widest"
                    style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);">
                    TICKET
                </button>
                <button id="btn-procesar-pago" type="button" data-dividido="{{ !empty($division) ? '1' : '0' }}" 
                    class="bg-emerald-600 hover:bg-emerald-500 text-white font-black py-5 rounded-2xl transition-all cursor-pointer shadow-lg shadow-emerald-600/20 active:scale-95 text-xs uppercase tracking-widest">
                    FINALIZAR
                </button>
            </div>
        </div>
    </div>
</div>