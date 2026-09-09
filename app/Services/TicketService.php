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
    private function obtenerOFolio(?Orden $primeraOrden, ?Mesa $mesa, $ordenes = null): TicketImpreso
    {
        if (!$primeraOrden) {
            return TicketImpreso::create([
                'orden_referencia_id' => 0,
                'mesa_numero'         => $mesa->numero ?? null,
                'impreso_en'          => now(),
            ]);
        }

        if ($ordenes && $ordenes->isNotEmpty()) {
            $ids = $ordenes->pluck('id')->filter();
            $existente = TicketImpreso::whereIn('orden_referencia_id', $ids)
                ->oldest('id')
                ->first();
            if ($existente) {
                return $existente;
            }
        }

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

    public function obtenerDatosTicketPorOrden(int $ordenId): array
    {
        $orden = Orden::withTrashed()
            ->with(['mesero', 'detalles.producto', 'detalles.variante', 'detalles.promocionAplicada.promocion', 'mesa'])
            ->findOrFail($ordenId);

        $mesa = Mesa::withTrashed()->findOrFail($orden->mesa_id);

        $ordenes = collect([$orden]);
        if ($orden->cerrada_el) {
            $ordenes = Orden::withTrashed()
                ->where('mesa_id', $orden->mesa_id)
                ->where('cerrada_el', $orden->cerrada_el)
                ->with(['mesero', 'detalles.producto', 'detalles.variante', 'detalles.promocionAplicada.promocion'])
                ->get();
        }

        return $this->construirDatosTicket($mesa, $ordenes);
    }

    public function obtenerDatosTicketPorMesa(int $mesaId): array
    {
        $mesa = Mesa::withTrashed()->findOrFail($mesaId);

        $ordenes = $mesa->ordenesActivas()
            ->with(['mesero', 'detalles.producto', 'detalles.variante', 'detalles.promocionAplicada.promocion'])
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
                    ->with(['mesero', 'detalles.producto', 'detalles.variante', 'detalles.promocionAplicada.promocion'])
                    ->get()
                : collect();
        }

        return $this->construirDatosTicket($mesa, $ordenes);
    }

    private function construirDatosTicket(Mesa $mesa, $ordenes): array
    {
        $items = $ordenes->flatMap(function ($orden) {
            return $orden->detalles
                ->filter(function ($detalle) {
                    return strtolower($detalle->estado ?? '') !== 'cancelado';
                })
                ->map(function ($detalle) {
                    $descuento = $detalle->promocionAplicada?->monto_descuento ?? 0;
                    $subtotalLinea = $detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario);

                    // Nombre compuesto con la variante si fue seleccionada
                    $nombreItem = $detalle->producto->nombre ?? 'Producto sin registro';
                    if (!empty($detalle->variante?->nombre)) {
                        $nombreItem .= " ({$detalle->variante->nombre})";
                    }

                    return [
                        'cantidad'         => $detalle->cantidad,
                        'nombre'           => $nombreItem,
                        'subtotal'         => $subtotalLinea,
                        'descuento'        => $descuento,
                        'promocion_nombre' => $detalle->promocionAplicada?->promocion?->nombre,
                    ];
                });
        });

        $ordenIds = $ordenes->pluck('id');

        $pagos = FlujoCaja::whereIn('flujoable_id', $ordenIds)
            ->where('flujoable_type', Orden::class)
            ->where('categoria', 'Ventas')
            ->get()
            ->map(function ($flujo) {
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
        $descuentoTotal = $items->sum('descuento');
        $subtotalTrasPromociones = $subtotalBruto - $descuentoTotal;

        $descuentoCajaPorcentaje = (float) ($ordenes->max('descuento_porcentaje') ?? 0);
        $descuentoCajaPorcentaje = max(0, min(100, $descuentoCajaPorcentaje));
        $descuentoCajaMonto = round($subtotalTrasPromociones * ($descuentoCajaPorcentaje / 100), 2);

        $baseImponible = round($subtotalTrasPromociones - $descuentoCajaMonto, 2);

        $ivaHabilitado = false;
        $ivaPorcentaje = 0;
        $iva = 0;

        $propina = $ordenes->sum(fn ($orden) => $orden->propina ?? 0);

        $esDelivery = $mesa->esDelivery();
        $comisionPorcentaje = $esDelivery ? (float) ($mesa->comision_porcentaje ?? 0) : 0;
        $comisionIvaPorcentaje = $esDelivery ? (float) ($mesa->comision_iva_porcentaje ?? 0) : 0;

        $baseComision = $baseImponible + $iva;
        $comisionMonto = $esDelivery ? round($baseComision * ($comisionPorcentaje / 100), 2) : 0;
        $comisionIvaMonto = $esDelivery ? round($comisionMonto * ($comisionIvaPorcentaje / 100), 2) : 0;
        $comisionTotal = round($comisionMonto + $comisionIvaMonto, 2);

        $totalCalculado = $baseImponible + $iva + $propina + $comisionTotal;

        $primeraOrden = $ordenes->first();
        $ticketImpreso = $this->obtenerOFolio($primeraOrden, $mesa, $ordenes);

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
            'folio'                   => $ticketImpreso->folio_formateado,
            'fecha'                   => $ticketImpreso->impreso_en->format('d/m/Y'),
            'hora'                    => $ticketImpreso->impreso_en->format('h:i A'),
            'mesa'                    => $mesa->numero ?? null,
            'mesero'                  => optional($primeraOrden?->mesero)->nombre ?? optional($primeraOrden?->mesero)->name,
            'cajero'                  => optional($ticketImpreso->cajero)->nombre ?? optional($ticketImpreso->cajero)->name,
            'items'                   => $items->values(),
            'subtotal'                => $subtotalBruto,
            'descuentoTotal'          => $descuentoTotal,
            'descuentoCajaPorcentaje' => $descuentoCajaPorcentaje,
            'descuentoCajaMonto'      => $descuentoCajaMonto,
            'iva'                     => $iva,
            'ivaPorcentaje'           => $ivaPorcentaje,
            'ivaHabilitado'           => $ivaHabilitado,
            'propina'                 => $propina,
            'esDelivery'              => $esDelivery,
            'plataformaNombre'        => $esDelivery ? optional($mesa->plataformaDelivery)->nombre : null,
            'comisionPorcentaje'      => $comisionPorcentaje,
            'comisionMonto'           => $comisionMonto,
            'comisionIvaPorcentaje'   => $comisionIvaPorcentaje,
            'comisionIvaMonto'        => $comisionIvaMonto,
            'comisionTotal'           => $comisionTotal,
            'total'                   => round($totalCalculado, 2),
            'pagos'                   => $pagos,
            'negocio'                 => ['nombre' => 'El Brasero'],
            'hayDivision'             => $hayDivision,
            'totalPartes'             => $totalPartes,
            'partesDivision'          => $partesDivision,
        ];
    }
}