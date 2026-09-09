<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CajaMovimiento;
use App\Models\TicketImpreso; 

class HistorialCajaController extends Controller
{
    public function index()
    {
        // Cargamos los turnos de la tabla macro 'caja_movimientos'
        $turnos = CajaMovimiento::with('user') 
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.historial_cajas.index', compact('turnos'));
    }

    public function show($id)
    {
        // Buscamos el turno específico e incluimos sus flujos de dinero internos
        $turno = CajaMovimiento::with(['user', 'flujos'])->findOrFail($id);

        // --- Ventas (ingresos categoría 'Ventas') ---
        $historicoVentas = $turno->flujos
            ->where('categoria', 'Ventas')
            ->sortByDesc('fecha')
            ->values();

        // Cargar folios — match directo primero
        $ordenIds = $historicoVentas->pluck('flujoable_id')->filter()->unique();
        $foliosDirectos = \App\Models\TicketImpreso::whereIn('orden_referencia_id', $ordenIds)
            ->pluck('id', 'orden_referencia_id');

        // Folios indirectos: órdenes hermanas con mismo cerrada_el
        $sinFolio = $ordenIds->diff($foliosDirectos->keys());
        $foliosIndirectos = collect();
        if ($sinFolio->isNotEmpty()) {
            $ordenesSinFolio = \App\Models\Orden::withTrashed()
                ->whereIn('id', $sinFolio)
                ->whereNotNull('cerrada_el')
                ->get(['id', 'mesa_id', 'cerrada_el']);

            foreach ($ordenesSinFolio as $ord) {
                $hermanasIds = \App\Models\Orden::withTrashed()
                    ->where('mesa_id', $ord->mesa_id)
                    ->where('cerrada_el', $ord->cerrada_el)
                    ->pluck('id');

                $ticket = \App\Models\TicketImpreso::whereIn('orden_referencia_id', $hermanasIds)
                    ->oldest('id')
                    ->first();

                if ($ticket) {
                    $foliosIndirectos[$ord->id] = $ticket->id;
                }
            }
        }

        $foliosPorOrden = $foliosDirectos->union($foliosIndirectos);

        // Adjuntar folio_ticket a cada venta
        $historicoVentas = $historicoVentas->map(function ($venta) use ($foliosPorOrden) {
            $venta->folio_ticket = $foliosPorOrden[$venta->flujoable_id] ?? null;
            return $venta;
        });

        $totalVentas         = $historicoVentas->sum('monto');
        $ventasEfectivo      = $historicoVentas->where('metodo_pago', 'efectivo')->sum('monto');
        $ventasTarjeta       = $historicoVentas->where('metodo_pago', 'tarjeta')->sum('monto');
        $ventasTransferencia = $historicoVentas->where('metodo_pago', 'transferencia')->sum('monto');

        // --- Gastos y salidas (egresos) ---
        // Los gastos EXCLUYEN las cancelaciones: no son compras ni salidas de
        // dinero, es consumo que nunca se cobró. Se llevan aparte para que el
        // saldo del turno no las descuente como si fueran gasto operativo.
        $historicoGastos = $turno->flujos
            ->where('tipo', 'egreso')
            ->where('categoria', '!=', \App\Models\FlujoCaja::CATEGORIA_CANCELACIONES)
            ->sortByDesc('fecha')
            ->values();

        $totalGastos = $historicoGastos->sum('monto');

        $historicoCancelaciones = $turno->flujos
            ->where('tipo', 'egreso')
            ->where('categoria', \App\Models\FlujoCaja::CATEGORIA_CANCELACIONES)
            ->sortByDesc('fecha')
            ->values();

        $totalCancelaciones = $historicoCancelaciones->sum('monto');

        // --- Movimiento total del turno (todos los métodos de pago) ---
        $saldoEstimado = $turno->monto_inicial + $totalVentas - $totalGastos;

        // --- Arqueo de efectivo: solo lo que pasa por el cajón ---
        // Mismo cálculo que usa el cierre y el PDF, para que los tres coincidan.
        $efectivo = app(\App\Services\CajaService::class)->calcularEfectivoEsperado($turno);

        $cerrado    = $turno->estado === 'cerrada';
        $diferencia = $cerrado ? (float) $turno->diferencia : null;

        return view('admin.historial_cajas.show', compact(
            'turno', 'historicoVentas', 'totalVentas',
            'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'historicoGastos', 'totalGastos', 'saldoEstimado',
            'historicoCancelaciones', 'totalCancelaciones',
            'efectivo', 'cerrado', 'diferencia'
        ));
    }
}