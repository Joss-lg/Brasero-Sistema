<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Mesa;
use App\Models\Orden;
use App\Models\FlujoCaja;
use App\Models\TicketImpreso;
use App\Models\CuentaDivision;

class TicketService
{
    /**
     * Devuelve el folio correlativo (001, 002...) y la fecha/hora exacta de
     * impresión de una venta, creándolos la primera vez que se piden.
     *
     * Se identifica la venta por el id de su PRIMERA orden, que es estable:
     * no cambia si se reconsulta el ticket ni si la mesa se libera/borra.
     * Por eso, aunque se vuelva a abrir esta misma pantalla, el folio y la
     * hora no cambian — igual que un recibo real no cambia de número ni de
     * hora cada vez que lo vuelves a ver.
     */
    private function obtenerOFolio(?Orden $primeraOrden, ?Mesa $mesa, $ordenes = null): TicketImpreso
    {
        if (!$primeraOrden) {
            return TicketImpreso::create([
                'orden_referencia_id' => 0,
                'mesa_numero'         => $mesa->numero ?? null,
                'impreso_en'          => now(),
            ]);
        }

        // Primero: buscar si YA existe un ticket para CUALQUIERA de las órdenes
        // del grupo. Si existe, reutilizarlo — nunca crear uno nuevo para el
        // mismo cobro. Esto evita que reimprimir genere folios duplicados.
        if ($ordenes && $ordenes->isNotEmpty()) {
            $ids = $ordenes->pluck('id')->filter();
            $existente = TicketImpreso::whereIn('orden_referencia_id', $ids)
                ->oldest('id') // el folio original, no el más reciente
                ->first();
            if ($existente) {
                return $existente;
            }
        }

        // No existe ticket — crear uno nuevo usando la orden con el ID más alto
        // (la más reciente del turno) para que futuros cobros de la misma mesa
        // en otro turno no colisionen con este folio.
        $ordenRef = $ordenes && $ordenes->isNotEmpty()
            ? $ordenes->sortByDesc('id')->first()
            : $primeraOrden;

        return TicketImpreso::create([
            'orden_referencia_id' => $ordenRef->id,
            'mesa_numero'         => $mesa->numero ?? null,
            'mesero_id'           => $ordenRef->mesero_id,
            'cajero_id'           => auth()->id(),
            'impreso_en'          => now(),
        ]);
    }

    /**
     * Genera los datos del ticket final de caja para una MESA completa,
     * agregando TODAS sus órdenes activas (puede haber más de una si
     * hubo varias rondas de envío a cocina que crearon órdenes separadas).
     * Esta es la misma unidad de agregación que ya usa CajaService::obtenerDesgloseMesa(),
     * así el ticket impreso siempre coincide con lo que se cobró.
     */
    /**
     * Genera el ticket a partir de una orden específica (para reimprimir
     * desde historial de caja). Usa el flujoable_id del FlujoCaja para
     * identificar exactamente qué órdenes se cobraron en ese pago.
     */
    public function obtenerDatosTicketPorOrden(int $ordenId): array
    {
        $orden = Orden::withTrashed()
            ->with(['mesero', 'detalles.producto', 'detalles.promocionAplicada.promocion', 'mesa'])
            ->findOrFail($ordenId);

        $mesa = Mesa::withTrashed()->findOrFail($orden->mesa_id);

        // Buscar todas las órdenes que se cerraron juntas con este pago
        // (mismo cerrada_el = mismo lote de cobro de CajaService::liberarMesa)
        $ordenes = collect([$orden]);
        if ($orden->cerrada_el) {
            $ordenes = Orden::withTrashed()
                ->where('mesa_id', $orden->mesa_id)
                ->where('cerrada_el', $orden->cerrada_el)
                ->with(['mesero', 'detalles.producto', 'detalles.promocionAplicada.promocion'])
                ->get();
        }

        // Reutilizar la lógica principal pasando las órdenes encontradas
        return $this->construirDatosTicket($mesa, $ordenes);
    }

    public function obtenerDatosTicketPorMesa(int $mesaId): array
    {
        $mesa = Mesa::withTrashed()->findOrFail($mesaId);

        $ordenes = $mesa->ordenesActivas()
            ->with(['mesero', 'detalles.producto', 'detalles.promocionAplicada.promocion'])
            ->get();

        if ($ordenes->isEmpty()) {
            $ultimaCerrada = Orden::where('mesa_id', $mesa->id)
                ->where('estado', Orden::ESTADO_PAGADA)
                ->latest('cerrada_el')
                ->first();

            $ordenes = $ultimaCerrada
                ? Orden::where('mesa_id', $mesa->id)
                    ->where('estado', Orden::ESTADO_PAGADA)
                    ->where('cerrada_el', $ultimaCerrada->cerrada_el)
                    ->with(['mesero', 'detalles.producto', 'detalles.promocionAplicada.promocion'])
                    ->get()
                : collect();
        }

        return $this->construirDatosTicket($mesa, $ordenes);
    }

    private function construirDatosTicket(Mesa $mesa, $ordenes): array
    {
// --- Items: se aplanan los detalles de TODAS las órdenes de la mesa ---
        $items = $ordenes->flatMap(function ($orden) {
            return $orden->detalles
                ->filter(function ($detalle) {
                    // Excluir los productos cancelados ANTES de pasarlos al ticket
                    return strtolower($detalle->estado ?? '') !== 'cancelado';
                })
                ->map(function ($detalle) {
                    $descuento = $detalle->promocionAplicada?->monto_descuento ?? 0;
                    $subtotalLinea = $detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario);

                    return [
                        'cantidad'         => $detalle->cantidad,
                        'nombre'           => $detalle->producto->nombre ?? 'Producto sin registro',
                        'subtotal'         => $subtotalLinea,
                        'descuento'        => $descuento,
                        'promocion_nombre' => $detalle->promocionAplicada?->promocion?->nombre,
                    ];
                });
        });

        $ordenIds = $ordenes->pluck('id');

        // --- Pagos: se buscan por TODAS las órdenes de la mesa, no solo una ---
        $pagos = FlujoCaja::whereIn('flujoable_id', $ordenIds)
            ->where('flujoable_type', Orden::class)
            ->where('categoria', 'Ventas')
            ->get()
            ->map(function ($flujo) {
                // Extraer número de persona del concepto si existe: "(Persona 2/3)"
                $persona = null;
                $totalPersonas = null;
                if (preg_match('/\(Persona (\d+)\/(\d+)\)/i', $flujo->concepto ?? '', $m)) {
                    $persona       = (int) $m[1];
                    $totalPersonas = (int) $m[2];
                }
                return [
                    'metodo'        => ucfirst($flujo->metodo_pago),
                    'monto'         => $flujo->monto,
                    'referencia'    => $flujo->referencia,
                    'persona'       => $persona,
                    'totalPersonas' => $totalPersonas,
                ];
            });

        $subtotalBruto  = $items->sum('subtotal');
        $descuentoTotal = $items->sum('descuento'); // descuentos de PROMOCIONES
        $subtotalTrasPromociones = $subtotalBruto - $descuentoTotal;

        // --- Descuento aplicado en Caja al cobrar (distinto al de promociones) ---
        // Se calcula EXACTAMENTE igual que CajaService::obtenerDesgloseMesa():
        // sobre lo que queda tras las promociones y ANTES del IVA. Si no se
        // hiciera igual aquí, el ticket impreso no cuadraría con lo cobrado.
        $descuentoCajaPorcentaje = (float) ($ordenes->max('descuento_porcentaje') ?? 0);
        $descuentoCajaPorcentaje = max(0, min(100, $descuentoCajaPorcentaje));
        $descuentoCajaMonto = round($subtotalTrasPromociones * ($descuentoCajaPorcentaje / 100), 2);

        $baseImponible = round($subtotalTrasPromociones - $descuentoCajaMonto, 2);

        // --- IVA: unificado con la MISMA fuente de verdad que usa CajaService
        // y ComandaController (Configuracion), en vez de session(), para que
        // el ticket de caja siempre coincida con el desglose que ya se cobró.
        /* IVA_BLOCK_START — iva_ticket
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();
        IVA_BLOCK_END */
        $ivaHabilitado = false; // IVA desactivado
        $ivaPorcentaje = 0;
        $iva = $ivaHabilitado ? round($baseImponible * ($ivaPorcentaje / 100), 2) : 0;

        // Propina: se suma la de TODAS las órdenes de la mesa. En la
        // práctica solo una tendrá valor > 0 (actualizarPropina() la
        // concentra en una sola orden y resetea las demás a 0), pero
        // sumar todas es seguro y no depende de ese detalle interno.
        $propina = $ordenes->sum(fn ($orden) => $orden->propina ?? 0);

        // --- NUEVO: comisión de plataforma de delivery (Rappi/Uber/DiDi) ---
        // Se lee el % "congelado" en la propia mesa (columna comision_porcentaje /
        // comision_iva_porcentaje) para que el ticket siempre coincida con lo que
        // se cobró, aunque después cambies el % en Configuración > Delivery.
        $esDelivery = $mesa->esDelivery();
        $comisionPorcentaje = $esDelivery ? (float) ($mesa->comision_porcentaje ?? 0) : 0;
        $comisionIvaPorcentaje = $esDelivery ? (float) ($mesa->comision_iva_porcentaje ?? 0) : 0;

        $baseComision = $baseImponible + $iva;
        $comisionMonto = $esDelivery ? round($baseComision * ($comisionPorcentaje / 100), 2) : 0;
        $comisionIvaMonto = $esDelivery ? round($comisionMonto * ($comisionIvaPorcentaje / 100), 2) : 0;
        $comisionTotal = round($comisionMonto + $comisionIvaMonto, 2);

        $totalCalculado = $baseImponible + $iva + $propina + $comisionTotal;

        $primeraOrden = $ordenes->first();

        // Folio correlativo + fecha/hora EXACTA de impresión (no de cierre de
        // la orden): se fijan la primera vez que se pide este ticket.
        $ticketImpreso = $this->obtenerOFolio($primeraOrden, $mesa, $ordenes);

        // --- División de cuenta ---
        $cuentasDivision = CuentaDivision::where('mesa_id', $mesa->id)
            ->orderBy('numero_cuenta')
            ->get();

        $hayDivision    = $cuentasDivision->isNotEmpty();
        $totalPartes    = $hayDivision ? $cuentasDivision->first()->total_partes : 1;
        $partesDivision = $hayDivision ? $cuentasDivision->map(fn($c) => [
            'numero'  => $c->numero_cuenta,
            'total'   => $c->total,
            'estado'  => $c->estado,
            'propina' => $c->propina ?? 0,
        ])->values() : collect();

        return [
            'folio'          => $ticketImpreso->folio_formateado, // "001", "002"...
            'fecha'          => $ticketImpreso->impreso_en->format('d/m/Y'),
            'hora'           => $ticketImpreso->impreso_en->format('h:i A'),
            'mesa'           => $mesa->numero ?? null,
            'mesero'         => optional($primeraOrden?->mesero)->nombre ?? optional($primeraOrden?->mesero)->name,
            'cajero'         => optional($ticketImpreso->cajero)->nombre ?? optional($ticketImpreso->cajero)->name,
            'items'          => $items->values(),
            'subtotal'       => $subtotalBruto,
            'descuentoTotal' => $descuentoTotal,
            // --- NUEVO: descuento aplicado en Caja (si lo hubo) ---
            'descuentoCajaPorcentaje' => $descuentoCajaPorcentaje,
            'descuentoCajaMonto'      => $descuentoCajaMonto,
            'iva'            => $iva,
            'ivaPorcentaje'  => $ivaPorcentaje,
            'ivaHabilitado'  => $ivaHabilitado,
            'propina'        => $propina,
            // --- NUEVO ---
            'esDelivery'            => $esDelivery,
            'plataformaNombre'      => $esDelivery ? optional($mesa->plataformaDelivery)->nombre : null,
            'comisionPorcentaje'    => $comisionPorcentaje,
            'comisionMonto'         => $comisionMonto,
            'comisionIvaPorcentaje' => $comisionIvaPorcentaje,
            'comisionIvaMonto'      => $comisionIvaMonto,
            'comisionTotal'         => $comisionTotal,
            'total'          => round($totalCalculado, 2),
            'pagos'          => $pagos,
            'negocio'        => ['nombre' => 'Agostadero'],
            // --- División ---
            'hayDivision'    => $hayDivision,
            'totalPartes'    => $totalPartes,
            'partesDivision' => $partesDivision,
        ];
    }
}