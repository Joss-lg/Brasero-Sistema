<?php

namespace App\Services;

use App\Models\CajaMovimiento;
use App\Models\Configuracion;
use App\Models\CuentaDivision;
use App\Models\DetalleOrden;
use App\Models\DetalleOrdenDivision;
use App\Models\FlujoCaja;
use App\Models\Mesa;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CajaService
{
    /**
     * Calcula el desglose de una orden o grupo de órdenes de una mesa.
     */
    public function obtenerDesgloseMesa(Mesa $mesa): array
    {
        $ordenesActivas = $mesa->ordenesActivas()
            ->with([
                'detalles.producto',
                'detalles.variante', // <-- Carga ansiosa para mostrar variante/proteína
                'detalles.promocionAplicada.promocion',
                'promocionesAplicadas.promocion',
                'promocionesAplicadas.detalleOrden.producto',
            ])
            ->get();

        // NUEVO: los productos cancelados nunca deben cobrarse. Se excluyen
        // aquí, en el punto único de verdad que usan cobro, precuenta y propina.
        $subtotalBruto = $ordenesActivas->sum(function ($orden) {
            return $orden->detalles
                ->where('estado', '!=', 'cancelado')
                ->sum(fn($detalle) => $detalle->cantidad * $detalle->precio_unitario);
        });

        $descuentoPromociones = $ordenesActivas->sum(function ($orden) {
            return $orden->promocionesAplicadas
                ->filter(fn($op) => optional($op->detalleOrden)->estado !== 'cancelado')
                ->sum('monto_descuento');
        });

        // Lista de productos con su descuento, para mostrar en la vista
        $productosConDescuento = $ordenesActivas->flatMap(function ($orden) {
            return $orden->promocionesAplicadas
                ->filter(fn($op) => optional($op->detalleOrden)->estado !== 'cancelado')
                ->map(function ($op) {
                    return [
                        'producto'        => $op->detalleOrden->producto->nombre ?? 'Producto',
                        'promocion'       => $op->promocion->nombre ?? 'Promoción',
                        'monto_descuento' => (float) $op->monto_descuento,
                    ];
                });
        })->values();

        $subtotalTrasPromociones = round($subtotalBruto - $descuentoPromociones, 2);

        // --- DESCUENTO DE CAJA ---
        //
        // Se aplica DESPUÉS de las promociones, sobre lo que realmente queda
        // por pagar, y ANTES del IVA (el impuesto se calcula sobre el importe
        // ya rebajado, que es como debe facturarse).
        //
        // El porcentaje vive en la orden. Antes lo capturaba el mesero desde
        // la comanda, pero este servicio NUNCA lo aplicaba: el descuento se
        // veía en la comanda y Caja terminaba cobrando el total completo.
        // Ahora lo captura Caja y sí se refleja en el cobro, la precuenta y
        // el ticket, porque los tres salen de aquí.
        $descuentoPorcentaje = (float) ($ordenesActivas->max('descuento_porcentaje') ?? 0);
        $descuentoPorcentaje = max(0, min(100, $descuentoPorcentaje));

        $descuentoCaja = round($subtotalTrasPromociones * ($descuentoPorcentaje / 100), 2);

        $subtotal = round($subtotalTrasPromociones - $descuentoCaja, 2);
        $propina  = $ordenesActivas->sum('propina');

/* IVA_BLOCK_START — calculo_iva_obtenerDesgloseMesa
        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje(); // ej. 16

        $iva = $ivaHabilitado
            ? round($subtotal * ($ivaPorcentaje / 100), 2)
            : 0;
IVA_BLOCK_END */

        // --- NUEVO: comisión de plataforma de delivery (Rappi/Uber/DiDi) ---
        // Se calcula sobre el valor de venta al público (subtotal + IVA del
        // producto), que es la base que usan las plataformas para cobrar su
        // comisión. Se le suma el IVA que la propia plataforma cobra sobre
        // esa comisión (factura de servicio), y el resultado se SUMA al
        // total que paga el cliente (así lo pediste: el precio en delivery
        // sube para cubrir la comisión, no se descuenta de lo que ya cobró
        // el restaurante).
        $esDelivery = $mesa->esDelivery();
        $comisionPorcentaje = $esDelivery ? (float) ($mesa->comision_porcentaje ?? 0) : 0;
        $comisionIvaPorcentaje = $esDelivery ? (float) ($mesa->comision_iva_porcentaje ?? 0) : 0;

        /* IVA_BLOCK_START — base_comision_con_iva
        $baseComision = $subtotal + $iva;
        IVA_BLOCK_END */
        $baseComision = $subtotal; // IVA desactivado: base sin IVA
        $comisionMonto = $esDelivery ? round($baseComision * ($comisionPorcentaje / 100), 2) : 0;
        $comisionIvaMonto = $esDelivery ? round($comisionMonto * ($comisionIvaPorcentaje / 100), 2) : 0;
        $comisionTotal = round($comisionMonto + $comisionIvaMonto, 2);

        /* IVA_BLOCK_START — total_con_iva
        $total = round($subtotal + $iva + $propina + $comisionTotal, 2);
        IVA_BLOCK_END */
        $total = round($subtotal + $propina + $comisionTotal, 2); // IVA desactivado

        $division = $this->obtenerEstadoDivision($mesa);

        return [
            'subtotal'              => $subtotal,
            'subtotalBruto'         => round($subtotalBruto, 2),
            'descuentoPromociones'  => round($descuentoPromociones, 2),
            'productosConDescuento' => $productosConDescuento,
            // Descuento manual aplicado desde Caja
            'descuentoPorcentaje'   => $descuentoPorcentaje,
            'descuentoCaja'         => $descuentoCaja,
            /* IVA_BLOCK_START — return_iva_keys
            'iva'                   => $iva,
            'ivaHabilitado'         => $ivaHabilitado,
            'ivaPorcentaje'         => $ivaPorcentaje,
            IVA_BLOCK_END */
            'iva'                   => 0,
            'ivaHabilitado'         => false,
            'ivaPorcentaje'         => 0,
            'propina'               => round($propina, 2),
            // --- NUEVO: desglose de comisión de delivery ---
            'esDelivery'            => $esDelivery,
            'plataformaNombre'      => $esDelivery ? optional($mesa->plataformaDelivery)->nombre : null,
            'comisionPorcentaje'    => $comisionPorcentaje,
            'comisionMonto'         => $comisionMonto,
            'comisionIvaPorcentaje' => $comisionIvaPorcentaje,
            'comisionIvaMonto'      => $comisionIvaMonto,
            'comisionTotal'         => $comisionTotal,
            'total'                 => $total,
            'ordenes'               => $ordenesActivas,
            // NUEVO: división real de la cuenta (null si la mesa no está dividida)
            'division'              => $division,
            'cuentasDivididas'      => $division['cuentas'] ?? [],
            'totalCuentasDivision'  => $division['total_partes'] ?? 1,
        ];
    }

    /**
     * Devuelve el estado actual de la división de una mesa, o null si no
     * está dividida. En modo 'por_producto' también incluye, por cada
     * producto de la comanda, cuántas unidades están asignadas a cada
     * persona (y cuántas quedan sin asignar).
     */
    public function obtenerEstadoDivision(Mesa $mesa): ?array
    {
        $cuentas = $mesa->cuentasDivision()->orderBy('numero_cuenta')->get();

        if ($cuentas->isEmpty()) {
            return null;
        }

        $tipo = $cuentas->first()->tipo;

        $asignacionesPorDetalle = [];
        if ($tipo === 'por_producto') {
            $detalles = $mesa->ordenesActivas()
                ->with(['detalles.divisiones', 'detalles.variante', 'detalles.producto'])
                ->get()
                ->flatMap(fn($orden) => $orden->detalles->where('estado', '!=', 'cancelado'));

            foreach ($detalles as $detalle) {
                $porPersona = $detalle->divisiones->pluck('cantidad', 'numero_cuenta')->toArray();
                $asignacionesPorDetalle[$detalle->id] = [
                    'por_persona'   => $porPersona, // [numero_cuenta => cantidad]
                    'sin_asignar'   => $detalle->cantidad - array_sum($porPersona),
                ];
            }
        }

        return [
            'tipo'         => $tipo,
            'total_partes' => (int) $cuentas->first()->total_partes,
            'completa'     => $cuentas->every(fn($c) => $c->estado === 'pagada'),
            'asignacionesPorDetalle' => $asignacionesPorDetalle,
            'cuentas'      => $cuentas->map(function ($cuenta) {
                return [
                    'id'             => $cuenta->id,
                    'numero_cuenta'  => $cuenta->numero_cuenta,
                    'estado_orden'   => $cuenta->estado, // 'pendiente' | 'pagada' (nombre mantenido por compatibilidad con la vista)
                    'subtotal'       => (float) $cuenta->subtotal,
                    'iva'            => (float) $cuenta->iva,
                    'propina'        => (float) $cuenta->propina,
                    'total'          => (float) $cuenta->total,
                ];
            })->values(),
        ];
    }

    /**
     * Inicia la división de la cuenta de una mesa entre $numPersonas partes.
     *
     * - 'equitativa': el total se reparte en partes iguales (la última
     *   parte absorbe el residuo de redondeo, para que la suma cuadre
     *   siempre con el total real).
     * - 'por_producto': se crean las N partes en $0 a la espera de que
     *   se le vayan asignando productos con asignarProductoAPersona().
     */
    public function iniciarDivision(Mesa $mesa, string $tipo, int $numPersonas): array
    {
        if ($numPersonas < 2) {
            throw new Exception('Se necesitan al menos 2 personas para dividir la cuenta.');
        }

        if (!in_array($tipo, ['equitativa', 'por_producto'], true)) {
            throw new Exception('Tipo de división inválido.');
        }

        return DB::transaction(function () use ($mesa, $tipo, $numPersonas) {
            // Si ya había una división previa (p. ej. el cajero se equivocó
            // y quiere volver a repartir), la limpiamos primero.
            $this->cancelarDivision($mesa);

            $desglose = $this->obtenerDesgloseMesa($mesa);

            if ($tipo === 'equitativa') {
                $subtotalParte = round($desglose['subtotal'] / $numPersonas, 2);
                $ivaParte      = round($desglose['iva'] / $numPersonas, 2);
                $propinaParte  = round($desglose['propina'] / $numPersonas, 2);
                $totalParte    = round($desglose['total'] / $numPersonas, 2);

                for ($i = 1; $i <= $numPersonas; $i++) {
                    $esUltima = $i === $numPersonas;

                    CuentaDivision::create([
                        'mesa_id'       => $mesa->id,
                        'tipo'          => 'equitativa',
                        'numero_cuenta' => $i,
                        'total_partes'  => $numPersonas,
                        // La última parte absorbe el residuo de redondeo
                        'subtotal'      => $esUltima ? round($desglose['subtotal'] - $subtotalParte * ($numPersonas - 1), 2) : $subtotalParte,
                        'iva'           => $esUltima ? round($desglose['iva'] - $ivaParte * ($numPersonas - 1), 2) : $ivaParte,
                        'propina'       => $esUltima ? round($desglose['propina'] - $propinaParte * ($numPersonas - 1), 2) : $propinaParte,
                        'total'         => $esUltima ? round($desglose['total'] - $totalParte * ($numPersonas - 1), 2) : $totalParte,
                        'estado'        => 'pendiente',
                    ]);
                }
            } else {
                // por_producto: se crean las cuentas en $0; se recalculan
                // cada vez que se asigna un producto.
                for ($i = 1; $i <= $numPersonas; $i++) {
                    CuentaDivision::create([
                        'mesa_id'       => $mesa->id,
                        'tipo'          => 'por_producto',
                        'numero_cuenta' => $i,
                        'total_partes'  => $numPersonas,
                        'estado'        => 'pendiente',
                    ]);
                }
            }

            return $this->obtenerEstadoDivision($mesa);
        });
    }

    /**
     * Asigna N unidades de un producto de la comanda a una persona
     * (reemplaza la cantidad que esa persona ya tuviera asignada de ESE
     * producto), y recalcula el monto de cada cuenta 'por_producto'.
     *
     * Permite partir un mismo renglón entre varias personas: p. ej. de
     * "3 pizzas" en un solo detalle, 2 unidades para la Persona 1 y 1
     * unidad para la Persona 2, llamando este método dos veces.
     */
    public function asignarProductoAPersona(Mesa $mesa, DetalleOrden $detalle, int $numeroCuenta, int $cantidad): array
    {
        return DB::transaction(function () use ($mesa, $detalle, $numeroCuenta, $cantidad) {
            $cuenta = $mesa->cuentasDivision()->where('numero_cuenta', $numeroCuenta)->first();

            if (!$cuenta) {
                throw new Exception('Esa parte de la cuenta no existe.');
            }

            if ($cuenta->tipo !== 'por_producto') {
                throw new Exception('Esta mesa no está dividida por producto.');
            }

            if ($cuenta->estado === 'pagada') {
                throw new Exception('No se puede reasignar un producto a una parte ya pagada.');
            }

            if ($cantidad < 0) {
                throw new Exception('La cantidad no puede ser negativa.');
            }

            // Cuántas unidades de ESTE producto ya están repartidas a OTRAS
            // personas, para no dejar que la suma pase de lo que hay en el renglón.
            $asignadoAOtros = DetalleOrdenDivision::where('detalle_orden_id', $detalle->id)
                ->where('numero_cuenta', '!=', $numeroCuenta)
                ->sum('cantidad');

            if ($asignadoAOtros + $cantidad > $detalle->cantidad) {
                $disponibles = $detalle->cantidad - $asignadoAOtros;
                throw new Exception("Solo quedan {$disponibles} unidad(es) sin asignar de este producto.");
            }

            if ($cantidad === 0) {
                DetalleOrdenDivision::where('detalle_orden_id', $detalle->id)
                    ->where('numero_cuenta', $numeroCuenta)
                    ->delete();
            } else {
                DetalleOrdenDivision::updateOrCreate(
                    ['detalle_orden_id' => $detalle->id, 'numero_cuenta' => $numeroCuenta],
                    ['cantidad' => $cantidad]
                );
            }

            $this->recalcularCuentasPorProducto($mesa);

            return $this->obtenerEstadoDivision($mesa);
        });
    }

    /**
     * Recalcula subtotal/iva/propina/total de cada cuenta 'por_producto',
     * a partir de las unidades asignadas por persona en cada producto, y
     * prorrateando IVA y propina de la mesa según el peso de cada persona
     * en el subtotal.
     */
    protected function recalcularCuentasPorProducto(Mesa $mesa): void
    {
        $desgloseMesa = $this->obtenerDesgloseMesa($mesa->fresh());
        $subtotalMesa = $desgloseMesa['subtotal'];

        $detalles = $mesa->ordenesActivas()
            ->with(['detalles.divisiones', 'detalles.promocionAplicada', 'detalles.variante', 'detalles.producto'])
            ->get()
            ->flatMap(fn($orden) => $orden->detalles->where('estado', '!=', 'cancelado'));

        $cuentas = $mesa->cuentasDivision()->where('tipo', 'por_producto')->get();

        foreach ($cuentas as $cuenta) {
            if ($cuenta->estado === 'pagada') {
                continue; // no tocar lo ya cobrado
            }

            $subtotalCuenta = 0;

            foreach ($detalles as $detalle) {
                $cantidadPersona = (int) $detalle->divisiones
                    ->firstWhere('numero_cuenta', $cuenta->numero_cuenta)?->cantidad;

                if ($cantidadPersona <= 0) {
                    continue;
                }

                // Precio y descuento por UNIDAD (el descuento de la promo
                // viene por renglón completo, así que se prorratea entre
                // las unidades del renglón antes de multiplicar por lo
                // que le tocó a esta persona).
                $descuentoTotalRenglon = optional($detalle->promocionAplicada)->monto_descuento ?? 0;
                $descuentoPorUnidad = $detalle->cantidad > 0 ? ($descuentoTotalRenglon / $detalle->cantidad) : 0;

                $subtotalCuenta += $cantidadPersona * ($detalle->precio_unitario - $descuentoPorUnidad);
            }

            $subtotalCuenta = round($subtotalCuenta, 2);

            // Prorrateo de IVA y propina según el peso de esta parte en el subtotal total
            $proporcion = $subtotalMesa > 0 ? ($subtotalCuenta / $subtotalMesa) : 0;
            $ivaCuenta = round($desgloseMesa['iva'] * $proporcion, 2);
            $propinaCuenta = round($desgloseMesa['propina'] * $proporcion, 2);

            $cuenta->update([
                'subtotal' => $subtotalCuenta,
                'iva'      => $ivaCuenta,
                'propina'  => $propinaCuenta,
                'total'    => round($subtotalCuenta + $ivaCuenta + $propinaCuenta, 2),
            ]);
        }
    }

    /**
     * Cancela la división activa de una mesa: borra las cuentas y todas
     * las asignaciones de unidades por producto.
     */
    public function cancelarDivision(Mesa $mesa): void
    {
        DB::transaction(function () use ($mesa) {
            if ($mesa->cuentasDivision()->where('estado', 'pagada')->exists()) {
                throw new Exception('No se puede cancelar la división: ya hay partes pagadas. Debes cobrar o revertir el pago primero.');
            }

            $detalleIds = $mesa->ordenesActivas()
                ->get()
                ->flatMap(fn($orden) => $orden->detalles)
                ->pluck('id');

            DetalleOrdenDivision::whereIn('detalle_orden_id', $detalleIds)->delete();

            $mesa->cuentasDivision()->delete();
        });
    }

    /**
     * Recalcula la división activa de una mesa (si la hay) después de que
     * cambió la propina. Las partes YA PAGADAS no se tocan (ya se cobraron
     * y quedaron registradas); el nuevo monto de propina se reparte solo
     * entre las partes que todavía están pendientes.
     */
    public function recalcularDivisionTrasPropina(Mesa $mesa): void
    {
        $tipo = $mesa->cuentasDivision()->value('tipo');

        if (!$tipo) {
            return; // la mesa no está dividida, nada que recalcular
        }

        if ($tipo === 'por_producto') {
            // La propina por persona ya se prorratea según el peso de su
            // consumo cada vez que se recalculan las cuentas; solo hace
            // falta volver a correrlo con la propina nueva.
            $this->recalcularCuentasPorProducto($mesa);
            return;
        }

        // tipo === 'equitativa'
        $desglose = $this->obtenerDesgloseMesa($mesa->fresh());
        $cuentas = $mesa->cuentasDivision()->get();

        $pagadas = $cuentas->where('estado', 'pagada');
        $pendientes = $cuentas->where('estado', 'pendiente');

        if ($pendientes->isEmpty()) {
            return; // ya se cobraron todas las partes, no hay nada que repartir
        }

        // La propina ya cobrada a las partes pagadas se resta del total;
        // lo que queda se reparte en partes iguales entre las pendientes.
        $propinaYaCobrada = $pagadas->sum('propina');
        $propinaPendiente = max(0, $desglose['propina'] - $propinaYaCobrada);

        $numPendientes = $pendientes->count();
        $propinaPorParte = round($propinaPendiente / $numPendientes, 2);

        $pendientes->values()->each(function ($cuenta, $index) use ($propinaPorParte, $propinaPendiente, $numPendientes) {
            $esUltima = $index === $numPendientes - 1;

            $nuevaPropina = $esUltima
                ? round($propinaPendiente - $propinaPorParte * ($numPendientes - 1), 2)
                : $propinaPorParte;

            $cuenta->update([
                'propina' => $nuevaPropina,
                'total'   => round($cuenta->subtotal + $cuenta->iva + $nuevaPropina, 2),
            ]);
        });
    }

    /**
     * Libera una mesa y limpia sus estados.
     */
    public function liberarMesa(Mesa $mesa): bool
    {
        return DB::transaction(function () use ($mesa) {
            // Pasamos a pagadas todas las órdenes que estaban en proceso en la mesa
            $mesa->ordenes()
                ->whereIn('estado', Orden::getEstadosActivos())
                ->update([
                    'estado'     => Orden::ESTADO_PAGADA,
                    'cerrada_el' => Carbon::now(),
                ]);

            // Reseteamos por completo la mesa para dejarla lista para nuevos clientes
            $mesa->update([
                'estado'        => Mesa::ESTADO_DISPONIBLE,
                'mesero_id'     => null,
                'total_consumo' => 0,
                'updated_at'    => Carbon::now(),
            ]);

            // Limpiamos cualquier división de cuenta: ya está todo cobrado
            // y la mesa queda libre para nuevos comensales.
            $mesa->cuentasDivision()->delete();

            // --- NUEVO: las mesas de DELIVERY se retiran al cobrarse ---
            // Una mesa de Rappi/Uber/DiDi es virtual: se crea para UN pedido
            // concreto y no existe en el salón, así que no tiene sentido
            // dejarla "disponible" esperando comensales. Si no se retirara,
            // cada pedido de delivery iría acumulando una mesa fantasma en
            // la pantalla de Caja para siempre.
            //
            // Se usa borrado SUAVE (SoftDeletes), no DELETE real, para no
            // romper el historial: las órdenes, los pagos y el corte de caja
            // siguen apuntando a esta mesa_id y deben poder consultarse.
            if ($mesa->esDelivery()) {
                $mesa->delete();
            }

            return true;
        });
    }

    /**
     * Calcula cuánto EFECTIVO debe haber físicamente en el cajón de un turno.
     *
     * Fuente única de verdad: antes existían cuatro fórmulas distintas
     * (cierre, pantalla de flujo, PDF e historial) y no coincidían entre sí,
     * así que lo que veía el cajero durante el turno no era lo que el sistema
     * usaba para calcular la diferencia al cerrar.
     *
     * Solo cuenta EFECTIVO: las ventas con tarjeta y transferencia nunca
     * entran al cajón, y sumarlas marcaría faltantes que no existen.
     *
     *      esperado = monto inicial + entradas en efectivo − salidas en efectivo
     *
     * Dos detalles de los datos reales:
     *  - El método se compara en minúsculas porque el módulo de Finanzas
     *    guarda "Efectivo" con mayúscula y el de ventas "efectivo".
     *  - En las SALIDAS, los movimientos sin método definido se cuentan como
     *    efectivo: son gastos y retiros que salen del cajón. Se devuelven
     *    aparte en `egresos_sin_metodo` para que puedan revisarse.
     */
    public function calcularEfectivoEsperado(CajaMovimiento $caja): array
    {
        // Las cancelaciones de cuenta se guardan como egreso para que queden
        // asentadas en el reporte, pero NO son salidas del cajón: nadie sacó
        // ese dinero, simplemente nunca entró. Se excluyen de todo el arqueo.
        // Se filtra por categoría (y no solo por metodo_pago) para que siga
        // funcionando aunque alguien edite el método del movimiento.
        $base = FlujoCaja::where('caja_movimiento_id', $caja->id)
            ->where(function ($q) {
                $q->where('categoria', '<>', 'Cancelaciones')->orWhereNull('categoria');
            });

        $ingresosEfectivo = (clone $base)
            ->where('tipo', 'ingreso')
            ->whereRaw('LOWER(metodo_pago) = ?', ['efectivo'])
            ->sum('monto');

        $egresosEfectivo = (clone $base)
            ->where('tipo', 'egreso')
            ->where(function ($q) {
                $q->whereRaw('LOWER(metodo_pago) = ?', ['efectivo'])
                  ->orWhereNull('metodo_pago')
                  ->orWhereRaw('LOWER(metodo_pago) = ?', ['no especificado']);
            })
            ->sum('monto');

        $egresosSinMetodo = (clone $base)
            ->where('tipo', 'egreso')
            ->where(function ($q) {
                $q->whereNull('metodo_pago')
                  ->orWhereRaw('LOWER(metodo_pago) = ?', ['no especificado']);
            })
            ->sum('monto');

        // Movimientos que NO pasan por el cajón. Se calculan para poder
        // mostrarlos en pantalla y que quede claro por qué no se suman.
        $ingresosNoEfectivo = (clone $base)
            ->where('tipo', 'ingreso')
            ->whereRaw('LOWER(IFNULL(metodo_pago, "")) <> ?', ['efectivo'])
            ->sum('monto');

        $montoInicial = (float) $caja->monto_inicial;
        $esperado = $montoInicial + (float) $ingresosEfectivo - (float) $egresosEfectivo;

        return [
            'monto_inicial'        => round($montoInicial, 2),
            'ingresos_efectivo'    => round((float) $ingresosEfectivo, 2),
            'egresos_efectivo'     => round((float) $egresosEfectivo, 2),
            'egresos_sin_metodo'   => round((float) $egresosSinMetodo, 2),
            'ingresos_no_efectivo' => round((float) $ingresosNoEfectivo, 2),
            'esperado'             => round($esperado, 2),
        ];
    }
}