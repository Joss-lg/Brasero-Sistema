<?php

namespace App\Services;

use App\Models\{Orden, DetalleOrden, MovimientoInventario, Producto, ProductoVariante, Mesa, Promocion, OrdenPromocion, PrintJob};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Exception;

class ComandaService
{
    /**
     * Procesa el envío de platillos a cocina, descuenta inventario y actualiza totales.
     */
    public function procesarEnvio(
        Mesa $mesa, 
        array $platillos, 
        $usuario, 
        float $totalGeneral = 0, 
        int $personas = 4, 
        float $descuentoPorcentaje = 0,
        bool $permitirSinStock = true
    ): Orden {
        return DB::transaction(function () use ($mesa, $platillos, $usuario, $personas, $descuentoPorcentaje, $permitirSinStock) {
            
            // 1. Buscar orden activa pendiente o crearla
            $orden = Orden::firstOrCreate(
                ['mesa_id' => $mesa->id, 'estado' => 'pendiente'],
                [
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesero_id'    => $usuario->id,
                    'abierta_el'   => now(),
                ]
            );

            $loteEnvio = now()->format('YmdHis') . '-' . rand(1000, 9999);

            $ordenDataUpdate = [];
            if (Schema::hasColumn('ordenes', 'personas')) $ordenDataUpdate['personas'] = $personas;
            if (Schema::hasColumn('ordenes', 'descuento_porcentaje')) $ordenDataUpdate['descuento_porcentaje'] = $descuentoPorcentaje;
            if (!empty($ordenDataUpdate)) {
                $orden->update($ordenDataUpdate);
            }

            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo = Schema::hasColumn('detalles_orden', 'tiempo');

            $productosParaTicket = [];

            $totalCalculado = 0.0;
            $totalDescuentosPromos = 0.0;

            foreach ($platillos as $platillo) {
                
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;

                $varianteId = !empty($platillo['variante_id']) ? (int)$platillo['variante_id'] : null;

                $detalleData = [
                    'orden_id'             => $orden->id,
                    'lote_envio'           => $loteEnvio,
                    'producto_id'          => $platillo['id'],
                    'producto_variante_id' => $varianteId,
                    'cantidad'             => $platillo['cantidad'],
                    'precio_unitario'      => $platillo['precio'],
                    'estado'               => 'en cocina',
                    'estado_preparacion'   => 'pendiente',
                    'notas'                => $notasFinales,
                ];

                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                if ($usaTiempo) $detalleData['tiempo'] = $platillo['tiempo'] ?? null;

                $detalle = DetalleOrden::create($detalleData); 

                // Subtotal de esta línea
                $subtotalProducto = $platillo['cantidad'] * $platillo['precio'];

                // Detectar promociones vigentes
                $descuentoProducto = 0.0;

                $promocionesAplicables = Promocion::activas()
                    ->whereHas('productos', function ($q) use ($platillo) {
                        $q->where('productos.id', $platillo['id']);
                    })
                    ->get();

                foreach ($promocionesAplicables as $promo) {
                    if (!$promo->aplicaHoy()) {
                        continue;
                    }

                    $montoDescuento = $promo->calcularDescuento($platillo['precio'], $platillo['cantidad']);

                    if ($montoDescuento > 0) {
                        OrdenPromocion::create([
                            'orden_id'         => $orden->id,
                            'promocion_id'     => $promo->id,
                            'detalle_orden_id' => $detalle->id,
                            'monto_descuento'  => $montoDescuento,
                        ]);
                        $descuentoProducto += $montoDescuento;
                    }
                }

                $totalDescuentosPromos += $descuentoProducto;
                $totalCalculado += ($subtotalProducto - $descuentoProducto);

                // 3. Inventario y preparación de ticket
                $producto = Producto::with(['insumos', 'categoria'])->find($platillo['id']);
                if ($producto) {
                    
                    $areaAsignada = $producto->categoria->area_impresion ?? 'Cocina';
                    if ($areaAsignada !== 'Barra') {
                        $areaAsignada = 'Cocina';
                    }

                    // Concatenar el nombre de la variante para la comanda de cocina
                    $nombreCompleto = $producto->nombre;
                    if ($varianteId) {
                        $varianteObj = ProductoVariante::find($varianteId);
                        if ($varianteObj) {
                            $nombreCompleto .= " ({$varianteObj->nombre})";
                        }
                    }

                    $productosParaTicket[] = [
                        'nombre'   => $nombreCompleto,
                        'cantidad' => $platillo['cantidad'],
                        'notas'    => $notasFinales,
                        'area'     => $areaAsignada,
                        'tiempo'   => $platillo['tiempo'] ?? null
                    ];

                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];
                        
                        if (!$permitirSinStock && $insumo->stock_actual < $cantidadUsada) {
                            throw new \Exception("Stock insuficiente en cocina para preparar: {$insumo->nombre}");
                        }
                        
                        $insumo->decrement('stock_actual', $cantidadUsada);
                        
                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id, 
                            'user_id'   => $usuario->id, 
                            'cantidad'  => $cantidadUsada, 
                            'tipo'      => 'salida',
                            'motivo'    => "Venta POS: {$nombreCompleto}"
                        ]);
                    }
                }
            }

            // 4. Actualizar Estado Financiero Global de la Mesa
            $mesaUpdateData = ['estado' => 'ocupada'];
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesaUpdateData['total_consumo'] = ($mesa->total_consumo ?? 0) + $totalCalculado;
            }
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesaUpdateData['mesero_id'] = $usuario->id;
            }

            $mesa->update($mesaUpdateData);

            if (Schema::hasColumn('ordenes', 'total')) {
                $orden->increment('total', $totalCalculado);
            }

            // Generar tickets por área
            $this->crearPrintJobs($orden, $loteEnvio, $productosParaTicket, $mesa, $usuario);

            return $orden;
        });
    }

    /**
     * Traspasa productos de una mesa origen a una mesa destino.
     */
    public function transferirProductos(
        Mesa $mesaOrigen,
        Mesa $mesaDestino,
        array $productosNuevos,
        array $detalleIds,
        $usuario,
        $meseroDestino = null
    ): array {
        return DB::transaction(function () use ($mesaOrigen, $mesaDestino, $productosNuevos, $detalleIds, $usuario, $meseroDestino) {

            $ordenDestino = Orden::where('mesa_id', $mesaDestino->id)
                ->whereIn('estado', Orden::getEstadosActivos())
                ->latest()
                ->first();

            if (!$ordenDestino) {
                $ordenDestino = Orden::create([
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesa_id'      => $mesaDestino->id,
                    'mesero_id'    => $meseroDestino ? $meseroDestino->id : $usuario->id,
                    'estado'       => Orden::ESTADO_PENDIENTE,
                    'abierta_el'   => now(),
                ]);
            }

            if ($meseroDestino && $ordenDestino->wasRecentlyCreated === false) {
                $ordenDestino->update(['mesero_id' => $meseroDestino->id]);
            }

            $loteEnvio = now()->format('YmdHis') . '-' . rand(1000, 9999);
            $montoTransferido = 0;

            // 2. Mover productos ya enviados
            if (!empty($detalleIds)) {
                $detallesExistentes = DetalleOrden::whereIn('id', $detalleIds)->get();
                foreach ($detallesExistentes as $detalle) {
                    $montoTransferido += $detalle->cantidad * $detalle->precio_unitario;
                }
                DetalleOrden::whereIn('id', $detalleIds)->update([
                    'orden_id'           => $ordenDestino->id,
                    'lote_envio'         => $loteEnvio,
                    'estado_preparacion' => 'pendiente',
                ]);
            }

            // 3. Crear productos nuevos en orden destino
            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo  = Schema::hasColumn('detalles_orden', 'tiempo');

            foreach ($productosNuevos as $platillo) {
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;
                $varianteId = !empty($platillo['variante_id']) ? (int)$platillo['variante_id'] : null;

                $detalleData = [
                    'orden_id'             => $ordenDestino->id,
                    'lote_envio'           => $loteEnvio,
                    'producto_id'          => $platillo['id'],
                    'producto_variante_id' => $varianteId,
                    'cantidad'             => $platillo['cantidad'],
                    'precio_unitario'      => $platillo['precio'],
                    'estado'               => 'en cocina',
                    'estado_preparacion'   => 'pendiente',
                    'notas'                => $notasFinales,
                ];
                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                if ($usaTiempo)  $detalleData['tiempo']  = $platillo['tiempo'] ?? null;

                $detalle = DetalleOrden::create($detalleData);

                $subtotalProducto = $platillo['cantidad'] * $platillo['precio'];
                $descuentoProducto = 0.0;

                $promocionesAplicables = Promocion::activas()
                    ->whereHas('productos', function ($q) use ($platillo) {
                        $q->where('productos.id', $platillo['id']);
                    })
                    ->get();

                foreach ($promocionesAplicables as $promo) {
                    if (!$promo->aplicaHoy()) {
                        continue;
                    }

                    $montoDescuento = $promo->calcularDescuento($platillo['precio'], $platillo['cantidad']);

                    if ($montoDescuento > 0) {
                        OrdenPromocion::create([
                            'orden_id'         => $ordenDestino->id,
                            'promocion_id'     => $promo->id,
                            'detalle_orden_id' => $detalle->id,
                            'monto_descuento'  => $montoDescuento,
                        ]);
                        $descuentoProducto += $montoDescuento;
                    }
                }

                $montoTransferido += $subtotalProducto - $descuentoProducto;
                $producto = Producto::with('insumos')->find($platillo['id']);
                if ($producto) {
                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];

                        if ($insumo->stock_actual < $cantidadUsada) {
                            throw new Exception("Stock insuficiente para preparar: {$insumo->nombre}");
                        }

                        $insumo->decrement('stock_actual', $cantidadUsada);

                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id,
                            'user_id'   => $usuario->id,
                            'cantidad'  => $cantidadUsada,
                            'tipo'      => 'salida',
                            'motivo'    => "Traspaso POS: {$producto->nombre}",
                        ]);
                    }
                }
            }

            // 4. Actualizar mesa destino
            $destinoUpdate = ['estado' => 'ocupada'];
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $destinoUpdate['mesero_id'] = $meseroDestino ? $meseroDestino->id : $usuario->id;
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $destinoUpdate['total_consumo'] = ($mesaDestino->total_consumo ?? 0) + $montoTransferido;
            }
            $mesaDestino->update($destinoUpdate);

            // 5. Descontar mesa origen
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesaOrigen->update([
                    'total_consumo' => max(0, ($mesaOrigen->total_consumo ?? 0) - $montoTransferido),
                ]);
            }

            return [
                'orden_destino'     => $ordenDestino,
                'monto_transferido' => $montoTransferido,
            ];
        });
    }

    private function crearPrintJobs(
        Orden $orden,
        string $loteEnvio,
        array $productos,
        Mesa $mesa,
        $usuario
    ): void {
        $agrupados = collect($productos)->groupBy('area');

        foreach ($agrupados as $area => $items) {
            $contenido = $this->formatearTicketTexto($area, $mesa, $usuario, $items);

            PrintJob::create([
                'orden_id'   => $orden->id,
                'lote_envio' => $loteEnvio,
                'area'       => $area,
                'contenido'  => $contenido,
                'estado'     => 'pendiente',
            ]);
        }
    }

    private function formatearTicketTexto($area, Mesa $mesa, $usuario, $items): string
    {
        $ancho = 32;
        $linea = str_repeat('-', $ancho) . "\n";

        $texto  = str_pad('', $ancho, '=', STR_PAD_BOTH) . "\n";
        $texto .= $this->centrarTexto('COMANDA: ' . mb_strtoupper($area), $ancho) . "\n";
        $texto .= str_pad('', $ancho, '=', STR_PAD_BOTH) . "\n";
        $texto .= "Mesa: " . ($mesa->numero ?? $mesa->id) . "\n";
        $texto .= "Mesero: " . ($usuario->nombre ?? $usuario->name ?? 'N/A') . "\n";
        $texto .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
        $texto .= $linea;

        foreach ($items as $item) {
            $texto .= ($item['cantidad'] . 'x ' . $item['nombre']) . "\n";

            if (!empty($item['tiempo']) && $item['tiempo'] !== 'sin-tiempo') {
                $texto .= '   [TIEMPO: ' . mb_strtoupper($item['tiempo']) . "]\n";
            }
            if (!empty($item['notas'])) {
                $texto .= '   * ' . $item['notas'] . "\n";
            }
        }

        $texto .= $linea;
        $texto .= "\n\n\n";

        return $texto;
    }

    private function centrarTexto(string $texto, int $ancho): string
    {
        $len = mb_strlen($texto);
        if ($len >= $ancho) return $texto;
        $espacios = intdiv($ancho - $len, 2);
        return str_repeat(' ', $espacios) . $texto;
    }

    private function enviarImpresionRed(array $productos, $nombreMesa, $nombreMesero)
    {
        $impresorasIps = [
            'Cocina' => '192.168.1.200',
            'Barra'  => '192.168.1.201',
        ];

        $comandasAgrupadas = collect($productos)->groupBy('area');

        foreach ($comandasAgrupadas as $area => $items) {
            try {
                $ip = $impresorasIps[$area] ?? $impresorasIps['Cocina'];
                
                $connector = new NetworkPrintConnector($ip, 9100);
                $printer = new Printer($connector);

                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
                $printer->text("COMANDA: " . strtoupper($area) . "\n");
                $printer->selectPrintMode();
                $printer->text("--------------------------------\n");
                
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Mesa: " . $nombreMesa . "\n");
                $printer->text("Mesero: " . $nombreMesero . "\n");
                $printer->text("Fecha: " . now()->format('d/m/Y h:i A') . "\n");
                $printer->text("--------------------------------\n");

                $printer->setEmphasis(true);
                $printer->text(str_pad("Cant.", 6) . "Producto\n");
                $printer->setEmphasis(false);
                $printer->text("--------------------------------\n");

                foreach ($items as $item) {
                    $cantStr = $item['cantidad'] . "x";
                    $printer->text(str_pad($cantStr, 6) . $item['nombre'] . "\n");
                    
                    if (!empty($item['tiempo'])) {
                        $printer->text("   [Tiempo: " . strtoupper($item['tiempo']) . "]\n");
                    }
                    if (!empty($item['notas'])) {
                        $printer->text("   * Ojo: " . $item['notas'] . "\n");
                    }
                }

                $printer->text("--------------------------------\n\n\n");
                $printer->cut();
                $printer->close();

            } catch (Exception $e) {
                \Log::error("No se pudo imprimir en el área {$area} (IP: {$ip}): " . $e->getMessage());
            }
        }
    }
}