<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\FlujoCaja;
use App\Models\Mesa;
use App\Models\User;
use App\Models\PropinaMesero;
use App\Models\Orden;
use App\Models\TicketImpreso;
use App\Services\CajaService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CajaController extends Controller
{
    protected $cajaService;
    protected $ticketService;

    public function __construct(CajaService $cajaService, TicketService $ticketService)
    {
        $this->cajaService = $cajaService;
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return view('admin.caja.apertura');
        }

        $datos = $this->construirMesasCaja();

        return view('admin.caja.index', array_merge($datos, ['cajaActiva' => $cajaActiva]));
    }

    /**
     * Devuelve las mesas con cuenta abierta y los contadores del encabezado.
     *
     * Vive aparte porque lo usan DOS entradas: el render inicial de la
     * pantalla (index) y el endpoint de auto-refresco (apiMesas). Tenerlo en
     * un solo lugar evita que la pantalla y el refresco muestren cosas
     * distintas cuando se cambie algún cálculo.
     */
    private function construirMesasCaja(): array
    {
        $mesas = Mesa::orderBy('numero', 'asc')->with(['ordenesActivas.detalles.producto', 'plataformaDelivery'])->get();

        $mesas->each(function ($mesa) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $mesa->ordenesActivas()->count() === 0) {
                $this->cajaService->liberarMesa($mesa);
                $mesa->estado = Mesa::ESTADO_DISPONIBLE;
                return;
            }

            // Delivery sin productos: ocultar de caja sin eliminarlo todavía
            // (el mesero puede estar capturando el pedido en ese momento).
            // Se elimina definitivamente cuando el mesero sale sin agregar nada.
            if ($mesa->esDelivery() && $mesa->estado === Mesa::ESTADO_OCUPADA) {
                $tieneProductos = $mesa->ordenesActivas
                    ->flatMap(fn($o) => $o->detalles)
                    ->where('estado', '!=', 'cancelado')
                    ->isNotEmpty();

                if (!$tieneProductos) {
                    $mesa->estado = Mesa::ESTADO_DISPONIBLE; // solo para ocultarla del listado
                }
            }
        });

        $mesas->each(function ($mesa) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $mesa->ordenesActivas->isNotEmpty()) {
                $desglose = $this->cajaService->obtenerDesgloseMesa($mesa);
                $mesa->total_real = $desglose['total'];
            } else {
                $mesa->total_real = 0;
            }
        });

        $mesasActivas = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->count();
        $totalAbierto = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->sum(fn($m) => floatval($m->total_real ?? 0));

        // Se cuenta ANTES de filtrar. Las mesas de delivery no cuentan como
        // "libres": son virtuales y desaparecen al cobrarse, así que este
        // número refleja solo las mesas reales del salón.
        $mesasLibres = $mesas
            ->where('estado', Mesa::ESTADO_DISPONIBLE)
            ->filter(fn($m) => !$m->esDelivery())
            ->count();

        // Caja solo muestra mesas con cuenta abierta: las libres no tienen
        // nada que cobrarse y solo estorban. En cuanto se cobra una mesa
        // queda "disponible" y desaparece sola de esta pantalla.
        //
        // OJO: se filtra DESPUÉS de calcular los totales de arriba para que
        // las tarjetas de resumen sigan siendo correctas.
        // Las mesas NO se borran: siguen intactas en el plano espacial.
        $mesas = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->values();

        return compact('mesas', 'mesasActivas', 'totalAbierto', 'mesasLibres');
    }

    /**
     * Endpoint de auto-refresco de la pantalla de Caja.
     *
     * Devuelve el HTML ya renderizado de las tarjetas más los contadores,
     * igual que hace Cocina. Antes la pantalla se refrescaba descargando la
     * PÁGINA COMPLETA cada 4s y recortándole dos pedazos con DOMParser: eso
     * traía el menú, los estilos y todos los modales en cada vuelta, y si
     * fallaba se silenciaba sin dejar rastro.
     */
    public function apiMesas()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        // Si cerraron la caja desde otra terminal, se avisa para que la
        // pantalla se recargue y muestre la vista de apertura.
        if (!$cajaActiva) {
            return response()->json([
                'success'     => false,
                'caja_cerrada' => true,
                'message'     => 'La caja fue cerrada.',
            ]);
        }

        $datos = $this->construirMesasCaja();

        return response()->json([
            'success'      => true,
            'html'         => view('admin.caja.partials.mesas', ['mesas' => $datos['mesas']])->render(),
            'totalAbierto' => '$' . number_format($datos['totalAbierto'], 2),
            'mesasActivas' => $datos['mesasActivas'],
            'mesasLibres'  => $datos['mesasLibres'],
        ]);
    }

    /**
     * Detalle de un cobro concreto del turno.
     *
     * Devuelve quien atendio (mesero de la orden), quien cobro (el cajero que
     * proceso el pago) y que se consumio en esa mesa.
     *
     * Sobre el cajero: hasta hace poco no se guardaba quien cobraba, solo a
     * que turno pertenecia el movimiento. Por eso, si el registro es viejo y
     * no trae registrado_por, se cae al usuario que ABRIO el turno y se marca
     * como aproximado, en vez de mostrar un dato que podria ser falso.
     */
    public function detalleVenta($id): JsonResponse
    {
        $venta = FlujoCaja::with(['registradoPor', 'cajaMovimiento.user'])->findOrFail($id);

        $orden = null;
        if ($venta->flujoable_id) {
            $orden = Orden::with(['mesero', 'mesa', 'detalles.producto'])->find($venta->flujoable_id);
        }

        $cajeroExacto = $venta->registradoPor;
        $cajeroTurno  = optional($venta->cajaMovimiento)->user;

        $productos = collect();
        if ($orden) {
            $productos = $orden->detalles->map(function ($d) {
                $cancelado = strtolower($d->estado ?? '') === 'cancelado';

                return [
                    'producto'        => optional($d->producto)->nombre ?? 'Producto eliminado',
                    'cantidad'        => (float) $d->cantidad,
                    'precio_unitario' => round((float) $d->precio_unitario, 2),
                    'importe'         => $cancelado ? 0 : round($d->cantidad * $d->precio_unitario, 2),
                    'cancelado'       => $cancelado,
                    'notas'           => $d->notas,
                ];
            });
        }

        return response()->json([
            'success'  => true,
            'concepto' => $venta->concepto,
            'monto'    => round((float) $venta->monto, 2),
            'metodo'   => $venta->metodo_pago,
            'referencia' => $venta->referencia,
            'hora'     => optional($venta->fecha)->format('d/m/Y H:i'),
            'mesa'     => optional(optional($orden)->mesa)->numero,
            'orden'    => optional($orden)->numero_orden,
            'personas' => optional($orden)->personas,
            'mesero'   => optional(optional($orden)->mesero)->nombre ?? 'Sin asignar',
            'cajero'   => $cajeroExacto->nombre ?? $cajeroTurno->nombre ?? 'Sin registrar',
            'cajero_aproximado' => $cajeroExacto === null,
            'productos' => $productos->values(),
            'consumo'   => round($productos->sum('importe'), 2),
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'turno'         => 'required|in:Matutino,Vespertino',
        ]);

        if (CajaMovimiento::where('estado', 'abierta')->exists()) {
            return redirect()->back()->with('error', 'Ya existe una sesión de caja abierta actualmente.');
        }

        CajaMovimiento::create([
            'user_id'       => Auth::id(),
            'turno'         => $request->turno,
            'monto_inicial' => $request->monto_inicial,
        ]);

        return redirect()->route('admin.caja.index')->with('success', '¡Caja abierta correctamente!');
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_final_real' => 'required|numeric|min:0',
            'comentarios'      => 'nullable|string|max:500'
        ]);

        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->firstOrFail();

        $propinasPendientes = PropinaMesero::where('caja_movimiento_id', $cajaActiva->id)
            ->where('pagada', false)
            ->get()
            ->groupBy('mesero_id');

        $totalPropinasEntregadas = 0;

        foreach ($propinasPendientes as $meseroId => $filas) {
            $montoMesero = $filas->sum('monto');
            if ($montoMesero <= 0) {
                continue;
            }

            $mesero = User::find($meseroId);
            $nombreMesero = $mesero ? $mesero->nombre : "Mesero #{$meseroId}";

            $egreso = FlujoCaja::create([
                'caja_movimiento_id' => $cajaActiva->id,
                'tipo'               => 'egreso',
                'categoria'          => 'Propinas',
                'concepto'           => "Propina — {$nombreMesero}",
                'monto'              => $montoMesero,
                'metodo_pago'        => 'efectivo',
                'fecha'              => now(),
            ]);

            PropinaMesero::whereIn('id', $filas->pluck('id'))
                ->update([
                    'pagada'        => true,
                    'pagada_el'     => now(),
                    'flujo_caja_id' => $egreso->id,
                ]);

            $totalPropinasEntregadas += $montoMesero;
        }

        // Se recalcula DESPUÉS de registrar las propinas como egreso, para que
        // el efectivo esperado ya las tenga descontadas. Usa el mismo método
        // que la pantalla y el PDF, así los tres números siempre coinciden.
        $desglose = $this->cajaService->calcularEfectivoEsperado($cajaActiva->fresh());

        $montoEsperado = $desglose['esperado'];
        $montoReal = (float) $request->monto_final_real;
        $diferencia = round($montoReal - $montoEsperado, 2);

        $cajaActiva->update([
            'monto_final_esperado' => $montoEsperado,
            'monto_final_real'     => $montoReal,
            'diferencia'           => $diferencia,
            'estado'               => 'cerrada',
            'comentarios'          => $request->comentarios
        ]);

        return redirect()->route('admin.caja.index')->with('success', 'La caja ha sido cerrada y el corte se generó con éxito.');
    }

    public function flujoDeCaja()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            // Con la caja cerrada ya no se queda en un callejón sin salida:
            // se muestran los últimos turnos cerrados para poder consultarlos
            // o reimprimir su corte sin tener que abrir caja primero.
            $turnosCerrados = CajaMovimiento::with('user')
                ->where('estado', 'cerrada')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get();

            return view('admin.caja.apertura', compact('turnosCerrados'));
        }

        $baseVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas');

        $totalVentas        = (clone $baseVentas)->sum('monto');
        $ventasEfectivo     = (clone $baseVentas)->porMetodoPago('efectivo')->sum('monto');
        $ventasTarjeta      = (clone $baseVentas)->porMetodoPago('tarjeta')->sum('monto');
        $ventasTransferencia = (clone $baseVentas)->porMetodoPago('transferencia')->sum('monto');
        
        // Los gastos EXCLUYEN las cancelaciones de cuenta: no son compras ni
        // salidas de dinero, son consumo que nunca se cobró. Se llevan aparte
        // para que el reporte no las confunda con gasto operativo.
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', '<>', 'Cancelaciones')->sum('monto');

        $totalCancelaciones = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', 'Cancelaciones')->sum('monto');
        $historicoCancelaciones = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', 'Cancelaciones')->ordenado()->get();

        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', '<>', 'Cancelaciones')->ordenado()->get();

        // Precargar folios: flujoable_id -> folio de tickets_impresos
        $ordenIdsVentas = $historicoVentas->pluck('flujoable_id')->filter()->unique();

        // Match directo: flujoable_id = orden_referencia_id
        $foliosDirectos = \App\Models\TicketImpreso::whereIn('orden_referencia_id', $ordenIdsVentas)
            ->pluck('id', 'orden_referencia_id');

        // Para los flujos sin folio directo (la mesa tenía múltiples órdenes y el
        // ticket se registró con otra orden del grupo), buscar via cerrada_el.
        $sinFolio = $ordenIdsVentas->diff($foliosDirectos->keys());
        $foliosIndirectos = collect();
        if ($sinFolio->isNotEmpty()) {
            $ordenesSinFolio = \App\Models\Orden::withTrashed()
                ->whereIn('id', $sinFolio)
                ->whereNotNull('cerrada_el')
                ->get(['id', 'mesa_id', 'cerrada_el']);

            foreach ($ordenesSinFolio as $ord) {
                // Buscar todas las órdenes hermanas (mismo mesa_id + cerrada_el)
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

        $propinasPendientes = PropinaMesero::with('mesero:id,nombre')
            ->where('caja_movimiento_id', $cajaActiva->id)
            ->where('pagada', false)
            ->get()
            ->groupBy('mesero_id')
            ->map(function ($filas) {
                return (object) [
                    'mesero_id' => $filas->first()->mesero_id,
                    'mesero'    => $filas->first()->mesero->nombre ?? ('Mesero #' . $filas->first()->mesero_id),
                    'total'     => $filas->sum('monto'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalPropinasPendientes = $propinasPendientes->sum('total');

        // Efectivo que debe haber físicamente en el cajón. Es el MISMO cálculo
        // que se usará al cerrar, para que el cajero no se lleve sorpresas.
        $efectivo = $this->cajaService->calcularEfectivoEsperado($cajaActiva);

        // Las propinas pendientes se pagan al cerrar (salen del cajón), así que
        // el efectivo que realmente debe quedar al final es el esperado menos
        // esas propinas. Este es el número contra el que se compara el conteo.
        $efectivoEsperadoAlCierre = round($efectivo['esperado'] - $totalPropinasPendientes, 2);

        return view('admin.caja.flujo', compact(
            'cajaActiva', 'totalVentas', 'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'totalGastos', 'saldoEstimado', 'historicoVentas', 'historicoGastos',
            'totalCancelaciones', 'historicoCancelaciones',
            'propinasPendientes', 'totalPropinasPendientes',
            'efectivo', 'efectivoEsperadoAlCierre', 'foliosPorOrden'
        ));
    }

    public function generarReportePdf($id, Request $request)
    {
        $cajaActiva = CajaMovimiento::with('user')->findOrFail($id);
        
        $totalVentas   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->sum('monto');
        // Los gastos EXCLUYEN las cancelaciones de cuenta: no son compras ni
        // salidas de dinero, son consumo que nunca se cobró. Se llevan aparte
        // para que el reporte no las confunda con gasto operativo.
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', '<>', 'Cancelaciones')->sum('monto');

        $totalCancelaciones = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', 'Cancelaciones')->sum('monto');
        $historicoCancelaciones = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', 'Cancelaciones')->ordenado()->get();

        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
            ->egresos()->where('categoria', '<>', 'Cancelaciones')->ordenado()->get();

        $ordenIdsVentas2 = $historicoVentas->pluck('flujoable_id')->filter()->unique();

        $foliosDirectos2 = \App\Models\TicketImpreso::whereIn('orden_referencia_id', $ordenIdsVentas2)
            ->pluck('id', 'orden_referencia_id');

        $sinFolio2 = $ordenIdsVentas2->diff($foliosDirectos2->keys());
        $foliosIndirectos2 = collect();
        if ($sinFolio2->isNotEmpty()) {
            $ordenesSinFolio2 = \App\Models\Orden::withTrashed()
                ->whereIn('id', $sinFolio2)
                ->whereNotNull('cerrada_el')
                ->get(['id', 'mesa_id', 'cerrada_el']);

            foreach ($ordenesSinFolio2 as $ord) {
                $hermanasIds = \App\Models\Orden::withTrashed()
                    ->where('mesa_id', $ord->mesa_id)
                    ->where('cerrada_el', $ord->cerrada_el)
                    ->pluck('id');

                $ticket = \App\Models\TicketImpreso::whereIn('orden_referencia_id', $hermanasIds)
                    ->oldest('id')
                    ->first();

                if ($ticket) {
                    $foliosIndirectos2[$ord->id] = $ticket->id;
                }
            }
        }

        $foliosPorOrden = $foliosDirectos2->union($foliosIndirectos2);

        // Desglose del efectivo del cajón, con el mismo cálculo del cierre.
        $efectivo = $this->cajaService->calcularEfectivoEsperado($cajaActiva);

        // Si el turno ya está cerrado se usan los valores que quedaron
        // guardados en el corte (son el registro oficial de lo que se contó);
        // si sigue abierto, se muestra el esperado del momento y aún no hay
        // diferencia que reportar.
        $cerrado = $cajaActiva->estado === 'cerrada';
        $montoEsperado = $cerrado ? (float) $cajaActiva->monto_final_esperado : $efectivo['esperado'];
        $montoReal     = $cerrado ? (float) $cajaActiva->monto_final_real : null;
        $diferencia    = $cerrado ? (float) $cajaActiva->diferencia : null;

        $pdf = Pdf::loadView('admin.caja.reporte_pdf', compact(
            'cajaActiva', 'totalVentas', 'totalGastos', 'saldoEstimado',
            'historicoVentas', 'historicoGastos',
            'totalCancelaciones', 'historicoCancelaciones',
            'efectivo', 'cerrado', 'montoEsperado', 'montoReal', 'diferencia'
        ));

        // Nombre con la fecha del turno para que los archivos descargados no
        // se pisen entre sí ni haya que abrirlos para saber a cuál corresponde.
        $fecha = optional($cajaActiva->updated_at ?? $cajaActiva->created_at)->format('Y-m-d') ?? 'sin-fecha';
        $nombre = "corte-caja-{$fecha}-turno-{$cajaActiva->id}.pdf";

        // ?descargar=1 fuerza la descarga; sin el parámetro se abre en el
        // navegador. Así el mismo enlace sirve para los dos botones.
        return $request->boolean('descargar')
            ? $pdf->download($nombre)
            : $pdf->stream($nombre);
    }

    public function imprimirTicket($mesaId)
    {
        // AJUSTE: ahora recibe el ID de la MESA (no de una orden individual).
        // Una mesa puede tener varias órdenes activas (varias rondas de
        // envío a cocina) y el ticket final debe reflejarlas TODAS, igual
        // que ya hace CajaService::obtenerDesgloseMesa() en el modal de cobro.
        $datos = $this->ticketService->obtenerDatosTicketPorMesa((int) $mesaId);
        return view('admin.caja.ticket', $datos);
    }

    /**
     * Reimprime el ticket de una venta ya cobrada a partir de la orden.
     *
     * En flujo de caja e historial las filas apuntan a la orden (flujoable_id).
     * Como la mesa ya fue liberada, se busca la orden directamente y se reconstruye
     * el ticket usando el mismo TicketService (respeta folio, hora original, etc.).
     */
    public function imprimirTicketPorOrden(int $ordenId)
    {
        // Usar el método específico por orden para que el ticket muestre
        // exactamente los productos y total de ese pago, no los de toda la mesa.
        $datos = $this->ticketService->obtenerDatosTicketPorOrden((int) $ordenId);
        return view('admin.caja.ticket', $datos);
    }

    public function toggleIva(Request $request)
    {
        $habilitado = $request->boolean('habilitado');
        session(['iva_habilitado' => $habilitado]);

        return response()->json([
            'success' => true,
            'ivaHabilitado' => $habilitado
        ]);
    }
}