@extends('layouts.admin')

@section('title', 'Meseros | Finanzas')

@section('content')
<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-7xl mx-auto space-y-5 sm:space-y-6">

    {{-- CABECERA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.finanzas.index') }}"
               class="text-xs font-bold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                &larr; Volver a Finanzas
            </a>
            <h1 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Desglose por Mesero
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                Ventas por turno y aportes al fondo de barra y cocina.
            </p>
        </div>

        {{-- Selector de día --}}
        <form method="GET" action="{{ route('admin.finanzas.meseros') }}" class="flex items-end gap-2">
            <div>
                <label for="fecha" class="block text-[10px] font-black uppercase tracking-wider text-zinc-500 mb-1">Día</label>
                <input type="date" name="fecha" id="fecha" value="{{ $fecha->toDateString() }}"
                       class="px-3 py-2 rounded-xl border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-sm font-bold text-zinc-900 dark:text-white outline-none focus:ring-2 focus:ring-[#b74309]">
            </div>
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-[#b74309] hover:bg-[#8f3207] text-white text-xs font-black uppercase tracking-wider transition-colors">
                Ver
            </button>
        </form>
    </div>

    {{-- TARJETAS DE RESUMEN --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Venta del día</p>
            <p class="text-2xl font-black text-zinc-900 dark:text-white mt-1">${{ number_format($ventaDelDia, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Mesas atendidas</p>
            <p class="text-2xl font-black text-zinc-900 dark:text-white mt-1">{{ $mesasDelDia }}</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-500/15 to-transparent border border-emerald-500/30 rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                <i class="fas fa-hand-holding-dollar mr-1"></i> Fondo barra y cocina
            </p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1" id="fondo-dia-display">
                ${{ number_format($fondoDelDia, 2) }}
            </p>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                Por repartir del {{ $fecha->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-white/[0.03] text-[10px] font-black uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-white/10">
                        <th class="py-3 px-3 text-left">Mesero</th>
                        <th class="py-3 px-3">Turno</th>
                        <th class="py-3 px-3">Mesas</th>
                        <th class="py-3 px-3 text-right">Venta total</th>
                        <th class="py-3 px-3 text-right">Efectivo</th>
                        <th class="py-3 px-3 text-right">Tarjeta</th>
                        <th class="py-3 px-3 text-right">Transfer.</th>
                        <th class="py-3 px-3 text-center">Aporte al fondo</th>
                        <th class="py-3 px-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                    @forelse($filas as $fila)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors"
                            data-caja="{{ $fila->caja_movimiento_id }}"
                            data-mesero="{{ $fila->mesero_id }}">

                            <td class="py-3 px-3 font-bold text-zinc-900 dark:text-white">{{ $fila->mesero }}</td>

                            <td class="py-3 px-3 text-center">
                                <span class="text-[10px] font-black uppercase px-2 py-1 rounded-md bg-zinc-100 dark:bg-white/5 text-zinc-600 dark:text-zinc-300">
                                    {{ $fila->turno ?? 'Sin turno' }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-center font-bold text-zinc-900 dark:text-white">{{ $fila->mesas_atendidas }}</td>

                            <td class="py-3 px-3 text-right font-black text-zinc-900 dark:text-white">${{ number_format($fila->venta_total, 2) }}</td>
                            <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 font-semibold">${{ number_format($fila->efectivo, 2) }}</td>
                            <td class="py-3 px-3 text-right text-[#b74309] dark:text-[#e8946a] font-semibold">${{ number_format($fila->tarjeta, 2) }}</td>
                            <td class="py-3 px-3 text-right text-purple-600 dark:text-purple-400 font-semibold">${{ number_format($fila->transferencia, 2) }}</td>

                            {{-- APORTE AL FONDO --}}
                            <td class="py-3 px-3">
                                @if(auth()->user()->tienePermiso('finanzas.editar'))
                                    <div class="flex items-center justify-center gap-1.5">
                                        <div class="relative">
                                            <input type="text" inputmode="decimal" data-teclado="numerico"
                                                   class="input-aporte w-16 pl-2 pr-5 py-1.5 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-xs font-bold text-zinc-900 dark:text-white outline-none focus:ring-2 focus:ring-[#b74309] text-center"
                                                   value="{{ $fila->aporte_porcentaje !== null ? rtrim(rtrim(number_format($fila->aporte_porcentaje, 2, '.', ''), '0'), '.') : $porcentajeSugerido }}">
                                            <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-zinc-400">%</span>
                                        </div>
                                        <button type="button"
                                            class="btn-aporte px-2.5 py-1.5 rounded-lg bg-[#b74309] hover:bg-[#8f3207] text-white text-[10px] font-black uppercase tracking-wider transition-colors">
                                            Aplicar
                                        </button>
                                    </div>
                                    <p class="monto-aporte text-center text-[11px] font-black mt-1 {{ $fila->aporte_monto > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                                        {{ $fila->aporte_monto > 0 ? '$' . number_format($fila->aporte_monto, 2) : 'Sin aporte' }}
                                    </p>
                                @else
                                    <p class="text-center text-[11px] font-black {{ $fila->aporte_monto > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                                        {{ $fila->aporte_monto > 0 ? '$' . number_format($fila->aporte_monto, 2) : 'Sin aporte' }}
                                    </p>
                                @endif
                            </td>

                            <td class="py-3 px-3 text-center">
                                <button type="button"
                                    class="btn-detalle px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-white/10 text-zinc-700 dark:text-zinc-300 text-[10px] font-black uppercase tracking-wider hover:border-[#b74309] hover:text-[#b74309] transition-colors whitespace-nowrap">
                                    Ver detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <i class="fas fa-user-tie text-3xl text-zinc-300 dark:text-zinc-700 mb-2"></i>
                                <p class="text-sm font-bold text-zinc-500">No hay ventas registradas ese día</p>
                                <p class="text-xs text-zinc-400 mt-1">Elige otra fecha en el selector de arriba.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
        El aporte se calcula sobre la venta total del mesero en ese turno. El porcentaje sugerido
        ({{ $porcentajeSugerido }}%) se puede cambiar en cada renglón; escribir 0 elimina el aporte.
    </p>
</div>

{{-- MODAL DE DETALLE --}}
<div id="modal-detalle-mesero" class="hidden fixed inset-0 z-[9998] items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-cerrar-detalle></div>

    <div class="relative w-full max-w-3xl bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-2xl overflow-hidden max-h-[85vh] flex flex-col">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-zinc-900 dark:text-white" id="detalle-titulo">Detalle</h3>
                <p class="text-xs text-zinc-500" id="detalle-subtitulo"></p>
            </div>
            <button type="button" data-cerrar-detalle
                class="w-8 h-8 rounded-lg border border-zinc-300 dark:border-white/10 text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition-colors">
                &times;
            </button>
        </div>

        <div class="overflow-y-auto flex-1" id="detalle-contenido">
            <p class="p-8 text-center text-sm text-zinc-500">Cargando...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // ---------------- APORTE AL FONDO ----------------
    document.querySelectorAll('.btn-aporte').forEach(btn => {
        btn.addEventListener('click', async () => {
            const fila = btn.closest('tr');
            const input = fila.querySelector('.input-aporte');
            const etiqueta = fila.querySelector('.monto-aporte');

            const crudo = (input.value || '').trim().replace(',', '.');
            const porcentaje = crudo === '' ? 0 : parseFloat(crudo);

            if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                etiqueta.textContent = 'Entre 0 y 100';
                etiqueta.className = 'monto-aporte text-center text-[11px] font-black mt-1 text-rose-500';
                return;
            }

            btn.disabled = true;
            const textoOriginal = btn.textContent;
            btn.textContent = '...';

            try {
                const res = await fetch(@json(route('admin.finanzas.meseros.aporte')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        caja_movimiento_id: fila.dataset.caja,
                        mesero_id: fila.dataset.mesero,
                        porcentaje: porcentaje,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    etiqueta.textContent = data.monto > 0
                        ? '$' + Number(data.monto).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                        : 'Sin aporte';
                    etiqueta.className = 'monto-aporte text-center text-[11px] font-black mt-1 ' +
                        (data.monto > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400');

                    document.getElementById('fondo-dia-display').textContent =
                        '$' + Number(data.fondo_dia).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    etiqueta.textContent = data.message || 'Error';
                    etiqueta.className = 'monto-aporte text-center text-[11px] font-black mt-1 text-rose-500';
                }
            } catch (e) {
                console.error('Error al aplicar aporte:', e);
                etiqueta.textContent = 'Error de conexión';
                etiqueta.className = 'monto-aporte text-center text-[11px] font-black mt-1 text-rose-500';
            } finally {
                btn.disabled = false;
                btn.textContent = textoOriginal;
            }
        });
    });

    // ---------------- DETALLE DE MESAS ----------------
    const modal = document.getElementById('modal-detalle-mesero');
    const contenido = document.getElementById('detalle-contenido');

    const cerrar = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    modal.querySelectorAll('[data-cerrar-detalle]').forEach(el => el.addEventListener('click', cerrar));

    const dinero = n => '$' + Number(n).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.querySelectorAll('.btn-detalle').forEach(btn => {
        btn.addEventListener('click', async () => {
            const fila = btn.closest('tr');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            contenido.innerHTML = '<p class="p-8 text-center text-sm text-zinc-500">Cargando...</p>';

            const url = @json(route('admin.finanzas.meseros.detalle'))
                + '?caja_movimiento_id=' + fila.dataset.caja
                + '&mesero_id=' + fila.dataset.mesero;

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">No se pudo cargar el detalle.</p>';
                    return;
                }

                document.getElementById('detalle-titulo').textContent = data.mesero;
                document.getElementById('detalle-subtitulo').textContent =
                    'Turno ' + data.turno + ' · ' + data.fecha + ' · ' + data.totales.mesas + ' mesa(s)';

                if (!data.mesas.length) {
                    contenido.innerHTML = '<p class="p-8 text-center text-sm text-zinc-500">Sin mesas registradas.</p>';
                    return;
                }

                let html = '<div class="p-4 space-y-3">';

                data.mesas.forEach((m, i) => {
                    const hora = m.cerrada_el
                        ? new Date(m.cerrada_el.replace(' ', 'T')).toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'})
                        : '--';

                    html += '<div class="border border-zinc-200 dark:border-white/10 rounded-xl overflow-hidden">'
                        + '<button type="button" class="btn-mesa w-full px-4 py-3 flex flex-wrap items-center justify-between gap-2 bg-zinc-50 dark:bg-white/[0.03] hover:bg-zinc-100 dark:hover:bg-white/[0.06] transition-colors text-left">'
                        + '<div>'
                        + '<span class="font-black text-zinc-900 dark:text-white">Mesa ' + m.mesa + '</span>'
                        + '<span class="text-[11px] text-zinc-500 ml-2">' + (m.numero_orden || '') + '</span>'
                        + '<div class="text-[11px] text-zinc-500 mt-0.5">'
                        + (m.personas || '-') + ' pers. · cerrada ' + hora + ' · ' + m.piezas + ' producto(s)'
                        + (m.hay_cancelados ? ' · <span class="text-rose-500 font-bold">con cancelados</span>' : '')
                        + '</div></div>'
                        + '<div class="text-right">'
                        + '<div class="font-black text-zinc-900 dark:text-white">' + dinero(m.total) + '</div>'
                        + '<div class="text-[10px] text-zinc-500">'
                        + (m.efectivo > 0 ? 'efvo ' + dinero(m.efectivo) + ' ' : '')
                        + (m.tarjeta > 0 ? 'tarj ' + dinero(m.tarjeta) + ' ' : '')
                        + (m.transferencia > 0 ? 'transf ' + dinero(m.transferencia) : '')
                        + '</div></div>'
                        + '</button>';

                    html += '<div class="detalle-consumo ' + (i === 0 ? '' : 'hidden') + '">';

                    if (!m.productos.length) {
                        html += '<p class="px-4 py-3 text-xs text-zinc-500">Sin productos registrados en esta mesa.</p>';
                    } else {
                        html += '<table class="w-full text-xs"><thead>'
                            + '<tr class="text-[10px] font-black uppercase tracking-wider text-zinc-400 border-b border-zinc-200 dark:border-white/10">'
                            + '<th class="py-2 px-4 text-left">Producto</th>'
                            + '<th class="py-2 px-2">Cant.</th>'
                            + '<th class="py-2 px-2 text-right">P. unit.</th>'
                            + '<th class="py-2 px-4 text-right">Importe</th>'
                            + '</tr></thead><tbody class="divide-y divide-zinc-100 dark:divide-white/5">';

                        m.productos.forEach(p => {
                            const tachado = p.cancelado ? 'line-through opacity-50' : '';
                            html += '<tr class="' + tachado + '">'
                                + '<td class="py-2 px-4 text-zinc-800 dark:text-zinc-200">'
                                + p.producto
                                + (p.gramaje ? ' <span class="text-zinc-400">(' + p.gramaje + ' g)</span>' : '')
                                + (p.cancelado ? ' <span class="text-rose-500 font-bold text-[10px] no-underline">CANCELADO</span>' : '')
                                + (p.notas ? '<div class="text-[10px] text-zinc-400 italic">' + p.notas + '</div>' : '')
                                + '</td>'
                                + '<td class="py-2 px-2 text-center">' + p.cantidad + '</td>'
                                + '<td class="py-2 px-2 text-right text-zinc-500">' + dinero(p.precio_unitario) + '</td>'
                                + '<td class="py-2 px-4 text-right font-bold text-zinc-900 dark:text-white">' + dinero(p.importe) + '</td>'
                                + '</tr>';
                        });

                        html += '</tbody><tfoot>'
                            + '<tr class="border-t border-zinc-200 dark:border-white/10">'
                            + '<td colspan="3" class="py-2 px-4 text-right text-[10px] font-black uppercase tracking-wider text-zinc-500">Consumo</td>'
                            + '<td class="py-2 px-4 text-right font-black text-zinc-900 dark:text-white">' + dinero(m.consumo) + '</td>'
                            + '</tr></tfoot></table>';
                    }

                    html += '</div></div>';
                });

                const t = data.totales;
                html += '<div class="mt-4 rounded-xl bg-[#b74309] text-white px-4 py-3 flex flex-wrap items-center justify-between gap-2">'
                    + '<span class="text-[11px] font-black uppercase tracking-wider text-white/80">Total del turno</span>'
                    + '<div class="text-right">'
                    + '<div class="text-lg font-black">' + dinero(t.total) + '</div>'
                    + '<div class="text-[10px] text-white/70">'
                    + 'efectivo ' + dinero(t.efectivo) + ' · tarjeta ' + dinero(t.tarjeta) + ' · transf. ' + dinero(t.transferencia)
                    + '</div></div></div>';

                html += '</div>';
                contenido.innerHTML = html;

                contenido.querySelectorAll('.btn-mesa').forEach(b => {
                    b.addEventListener('click', () => {
                        b.nextElementSibling.classList.toggle('hidden');
                    });
                });
            } catch (e) {
                console.error('Error al cargar detalle:', e);
                contenido.innerHTML = '<p class="p-8 text-center text-sm text-rose-500">Error de conexión.</p>';
            }
        });
    });
});
</script>
@endsection