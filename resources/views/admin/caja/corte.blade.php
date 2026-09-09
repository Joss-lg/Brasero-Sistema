{{-- resources/views/admin/caja/corte.blade.php --}}
<div id="modalCierreCaja" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" id="backdropCierreCaja"></div>
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="modal-container inline-block rounded-3xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full p-6 z-10 border"
             style="background-color: var(--card-color); border-color: var(--border-color);">
            
            <div class="flex items-center justify-between mb-4 border-b pb-3" style="border-bottom-color: var(--border-color);">
                <h3 class="text-xl font-black flex items-center gap-2" style="color: var(--text-color);">
                    Realizar Corte de Caja
                </h3>
                <button type="button" id="btnCerrarModalX" class="cursor-pointer hover:opacity-80 transition-opacity" style="color: var(--text-muted);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Desglose de propinas pendientes de entregar en este turno --}}
            @if(isset($propinasPendientes) && $propinasPendientes->isNotEmpty())
                <div class="mb-4 rounded-2xl border border-amber-300 dark:border-amber-700/50 bg-amber-50 dark:bg-amber-900/20 overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-amber-200 dark:border-amber-800/50 flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                            <i class="fas fa-hand-holding-dollar"></i> Propinas a entregar
                        </span>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400">
                            ${{ number_format($totalPropinasPendientes, 2) }}
                        </span>
                    </div>

                    <ul class="divide-y divide-amber-200/60 dark:divide-amber-800/40 max-h-40 overflow-y-auto">
                        @foreach($propinasPendientes as $fila)
                            <li class="px-4 py-2 flex items-center justify-between text-sm">
                                <span class="font-medium truncate" style="color: var(--text-color);">{{ $fila->mesero }}</span>
                                <span class="font-black text-amber-600 dark:text-amber-400 shrink-0 ml-3">${{ number_format($fila->total, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="px-4 py-2 text-[10px] text-amber-700/80 dark:text-amber-400/70 bg-amber-100/60 dark:bg-amber-900/30 leading-snug">
                        Este monto se descontará automáticamente del efectivo esperado al confirmar el cierre.
                    </p>
                </div>
            @endif

            <form action="{{ route('admin.caja.cerrar') }}" method="POST" class="space-y-4">
                @csrf

                {{-- EFECTIVO QUE DEBE HABER --}}
                <div class="rounded-xl border overflow-hidden" style="background-color: var(--input-bg); border-color: var(--border-color);">
                    <div class="px-4 py-2.5 space-y-1.5 text-xs">
                        <div class="flex justify-between" style="color: var(--text-muted);">
                            <span>Fondo inicial</span>
                            <span class="font-bold" style="color: var(--text-color);">${{ number_format($efectivo['monto_inicial'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <span>(+) Entradas en efectivo</span>
                            <span class="font-bold">${{ number_format($efectivo['ingresos_efectivo'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-rose-600 dark:text-rose-400">
                            <span>(−) Salidas en efectivo</span>
                            <span class="font-bold">${{ number_format($efectivo['egresos_efectivo'] ?? 0, 2) }}</span>
                        </div>
                        @if(($totalPropinasPendientes ?? 0) > 0)
                            <div class="flex justify-between text-amber-600 dark:text-amber-400">
                                <span>(−) Propinas por entregar</span>
                                <span class="font-bold">${{ number_format($totalPropinasPendientes, 2) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="px-4 py-3 bg-black/90 dark:bg-black flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-300">Debe haber en caja</span>
                        <span class="text-xl font-black text-white"
                              id="efectivoEsperado"
                              data-esperado="{{ $efectivoEsperadoAlCierre ?? 0 }}">
                            ${{ number_format($efectivoEsperadoAlCierre ?? 0, 2) }}
                        </span>
                    </div>
                    @if(($efectivo['ingresos_no_efectivo'] ?? 0) > 0)
                        <p class="px-4 py-2 text-[10px] leading-snug border-t" style="border-color: var(--border-color); color: var(--text-muted);">
                            No se cuentan aquí ${{ number_format($efectivo['ingresos_no_efectivo'], 2) }} de tarjeta y transferencia: ese dinero no pasa por el cajón.
                        </p>
                    @endif
                </div>

                <p class="text-xs font-semibold" style="color: var(--text-muted);">
                    Ingresa el monto total en efectivo que tienes físicamente en la caja para realizar la conciliación automática.
                </p>

                <div>
                    <label for="monto_final_real" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Efectivo Físico en Caja</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-sm font-bold" style="color: var(--text-muted);">$</span>
                        </div>
                        <input type="text" inputmode="decimal" data-teclado="numerico" name="monto_final_real" id="monto_final_real" required
                            class="w-full pl-7 py-2.5 rounded-xl border text-sm font-bold focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10 focus:outline-none transition-all"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);"
                            placeholder="0.00" onfocus="this.select()">
                    </div>

                    {{-- Diferencia calculada en vivo mientras se teclea el conteo --}}
                    <div id="diferenciaCorte" class="hidden mt-2 px-3 py-2 rounded-xl text-sm font-black flex items-center justify-between"></div>
                </div>

                <div>
                    <label for="comentarios" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Notas de Auditoría (Opcional)</label>
                    <textarea name="comentarios" id="comentarios" rows="3" maxlength="500"
                        class="w-full p-3 rounded-xl border text-sm focus:border-[#b74309] focus:ring-2 focus:ring-[#b74309]/10 focus:outline-none transition-all resize-none"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);"
                        placeholder="Observaciones sobre faltantes, sobrantes o incidentes en el turno..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t" style="border-top-color: var(--border-color);">
                    <button type="button" id="btnCancelarModal"
                        class="px-4 py-2 text-sm font-bold rounded-xl transition cursor-pointer border hover:opacity-80"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-black uppercase tracking-wider text-white bg-rose-600 hover:bg-rose-500 rounded-xl transition shadow-md shadow-rose-600/20 active:scale-95 cursor-pointer">
                        Cerrar Turno Actual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>