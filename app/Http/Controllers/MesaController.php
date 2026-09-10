<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Promocion;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\OrdenPromocion;
use App\Services\MesaService;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MesaController extends Controller
{
    protected $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    public function index()
    {
        $mesas = $this->mesaService->obtenerMesasParaUsuario(auth()->user());
        $plataformasDelivery = \App\Models\PlataformaDelivery::activas()->orderBy('nombre')->get();

        // Para avisar en pantalla que no se puede levantar pedidos. El bloqueo
        // real lo hace el middleware 'caja.abierta' en las rutas; esto es solo
        // para que el mesero se entere antes de capturar todo el pedido.
        $cajaAbierta = \App\Models\CajaMovimiento::where('estado', 'abierta')->exists();

        return view('admin.mesas.index', compact('mesas', 'plataformasDelivery', 'cajaAbierta'));
    }

    public function show($mesaId)
    {
        $mesa = Mesa::with('plataformaDelivery')->findOrFail($mesaId);
        $usuario = auth()->user();

        $this->mesaService->verificarAccesoMesa($mesa, $usuario);
        $esCapitan = $this->mesaService->esCapitan($usuario);

        $categorias = Categoria::all();

        // Consulta incluyendo soporte para variantes de proteínas/precios
        $productos = Producto::with([
                'categoria', 
                'modificadores',
                'variantes' => function($q) {
                    $q->where('esta_disponible', true);
                }
            ])
            ->select([
                'id', 'categoria_id', 'nombre', 'descripcion', 'precio',
                'tiene_variantes', 'se_vende_por_peso', 'precio_por_100g', 'esta_disponible',
            ])
            ->orderBy('nombre', 'asc')
            ->get();

        $nombreRol = strtolower(trim($usuario->rol?->nombre ?? ''));
        $esAdmin = $nombreRol === 'administrador';

        $mesasAbiertas = ($esCapitan || $esAdmin)
            ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get()
            : collect();

        $ordenActiva = Orden::where('mesa_id', $mesa->id)
            ->where('estado', '!=', 'pagada')
            ->latest()
            ->first();

        $platillosEnviados = collect();
        if ($ordenActiva) {
            $platillosEnviados = DetalleOrden::where('orden_id', $ordenActiva->id)
                ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                ->select('detalles_orden.*', 'productos.nombre as nombre', 'detalles_orden.precio_unitario as precio')
                ->get();
        }

        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();

        return view('mesero.index', compact(
            'mesa', 'categorias', 'productos', 'mesasAbiertas',
            'esCapitan', 'platillosEnviados', 'ordenActiva',
            'ivaHabilitado', 'ivaPorcentaje'
        ));
    }

    public function enviar(Request $request)
    {
        try {
            $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'platillos' => 'required|array|min:1',
                'total' => 'nullable|numeric|min:0',
                'personas' => 'nullable|integer|min:1',
                'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            ]);

            $mesa = Mesa::findOrFail($request->mesa_id);

            $permitirSinStock = $request->boolean('permitir_sin_stock', true);

            $orden = app(ComandaService::class)->procesarEnvio(
                $mesa,
                $request->platillos,
                auth()->user(),
                $request->total ?? 0,
                $request->personas ?? $mesa->capacidad ?? 4,
                $request->descuento_porcentaje ?? 0,
                $permitirSinStock // <-- Se lo pasamos como último argumento
            );

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada correctamente',
                'orden_id' => $orden->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancela un producto individual que ya fue enviado a cocina.
     * Requiere autorización por NIP de un Capitán o Administrador.
     * No borra el registro: lo marca como 'cancelado' (queda de historial),
     * revierte cualquier descuento de promoción asociado y ajusta los
     * totales acumulados de la orden y la mesa.
     */
    public function cancelarProducto(Request $request, $detalleId)
    {
        $request->validate([
            'nip'               => 'required|string',
            'motivo'           => 'nullable|string|max:255',
            'cantidad_cancelar' => 'nullable|integer|min:1',
        ]);

        // --- Verificación de autorización por NIP (Capitán o Administrador) ---
        $autorizador = User::where('codigo_empleado', $request->nip)->first();

        if (!$autorizador) {
            return response()->json([
                'success' => false,
                'message' => 'NIP inválido.'
            ], 403);
        }

        // Solo el Administrador puede autorizar cancelaciones de productos.
        $autorizador->loadMissing('rol');
        $nombreRol = strtolower(trim($autorizador->rol?->nombre ?? ''));
        $esAdmin = in_array($nombreRol, ['administrador', 'admin']) || $autorizador->id === 1;

        if (!$esAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el Administrador puede autorizar cancelaciones de productos.'
            ], 403);
        }

        $detalle = DetalleOrden::findOrFail($detalleId);

        if ($detalle->estado === 'cancelado') {
            return response()->json([
                'success' => false,
                'message' => 'Este producto ya fue cancelado anteriormente.'
            ], 422);
        }

        $orden = $detalle->orden;
        $mesa  = Mesa::findOrFail($orden->mesa_id);

        // Cuántas unidades cancelar (por defecto todas)
        $cantidadCancelar = min(
            (int) ($request->cantidad_cancelar ?? $detalle->cantidad),
            $detalle->cantidad
        );
        $cancelacionParcial = $cantidadCancelar < $detalle->cantidad;

        try {
            DB::transaction(function () use ($detalle, $orden, $mesa, $autorizador, $request, $cantidadCancelar, $cancelacionParcial) {

                // Proporción del descuento a revertir
                $proporcion = $cantidadCancelar / $detalle->cantidad;
                $subtotalCancelado = $cantidadCancelar * $detalle->precio_unitario;
                $descuentoAsociado = OrdenPromocion::where('detalle_orden_id', $detalle->id)->sum('monto_descuento');
                $descuentoCancelado = round($descuentoAsociado * $proporcion, 2);
                $montoNeto = $subtotalCancelado - $descuentoCancelado;

                // Reflejar en los totales acumulados de orden/mesa
                if (Schema::hasColumn('ordenes', 'total')) {
                    $orden->decrement('total', $montoNeto);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    $mesa->update(['total_consumo' => max(0, ($mesa->total_consumo ?? 0) - $montoNeto)]);
                }

                if ($cancelacionParcial) {
                    // Cancelación parcial: reducir cantidad, no marcar toda la línea
                    $detalle->update([
                        'cantidad' => $detalle->cantidad - $cantidadCancelar,
                    ]);
                    // Ajustar el descuento de promoción proporcionalmente
                    if ($descuentoAsociado > 0) {
                        OrdenPromocion::where('detalle_orden_id', $detalle->id)
                            ->update(['monto_descuento' => DB::raw("monto_descuento * " . (1 - $proporcion))]);
                    }
                } else {
                    // Cancelación total: marcar como cancelado y limpiar descuentos
                    OrdenPromocion::where('detalle_orden_id', $detalle->id)->delete();
                    $detalle->update([
                        'estado'             => 'cancelado',
                        'estado_preparacion' => 'cancelado',
                        'cancelado_motivo'   => $request->motivo,
                        'cancelado_por'      => $autorizador->id,
                        'cancelado_en'       => now(),
                    ]);
                }
            });

            $msg = $cancelacionParcial
                ? "{$cantidadCancelar} unidad(es) cancelada(s) correctamente."
                : 'Producto cancelado correctamente.';

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Persiste el número de personas de la cuenta activa al instante,
     * sin esperar a "Enviar Orden". Si aún no hay orden activa para la
     * mesa, crea una en estado pendiente para poder guardarlo desde ya.
     */
    public function actualizarPersonas(Request $request, $mesaId)
    {
        $request->validate(['personas' => 'required|integer|min:1']);

        $mesa = Mesa::findOrFail($mesaId);

        $orden = Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', Orden::getEstadosActivos())
            ->latest()
            ->first();

        if (!$orden) {
            $orden = Orden::create([
                'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id'      => $mesa->id,
                'mesero_id'    => auth()->id(),
                'estado'       => Orden::ESTADO_PENDIENTE,
                'abierta_el'   => now(),
                'personas'     => $request->personas,
            ]);
        } else {
            $orden->update(['personas' => $request->personas]);
        }

        return response()->json(['success' => true, 'personas' => $orden->personas]);
    }

    /**
     * Devuelve, en JSON, las promociones activas hoy (filtradas por
     * fecha_inicio/fecha_fin y por día de la semana actual usando
     * dias_semana). La usa el modal de Promociones del mesero para
     * pintar las tarjetas y aplicar el descuento automático al ticket.
     */
    public function promocionesActivas(): JsonResponse
    {
        try {
            $diaSemana = now()->dayOfWeekIso; // 1 = lunes ... 7 = domingo
            $hoy = now()->toDateString();

            $promos = Promocion::activas()
                ->with('productos:id')
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy);
                })
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
                })
                ->get()
                ->filter(function ($promo) use ($diaSemana) {
                    $dias = $promo->dias_semana;

                    if (is_string($dias)) {
                        $decoded = json_decode($dias, true);
                        if (is_string($decoded)) {
                            $decoded = json_decode($decoded, true);
                        }
                        $dias = $decoded;
                    }

                    $dias = is_array($dias) ? $dias : [];

                    return empty($dias) || in_array($diaSemana, $dias);
                })
                ->map(function ($promo) {
                    return [
                        'id'              => $promo->id,
                        'nombre'          => $promo->nombre,
                        'descripcion'     => $promo->descripcion,
                        'tipo_promocion'  => $promo->tipo_promocion,
                        'valor_descuento' => $promo->valor_descuento,
                        'dias_semana'     => $promo->dias_semana,
                        'producto_ids'    => $promo->productos->pluck('id')->values(),
                    ];
                })
                ->values();

            return response()->json(['success' => true, 'promociones' => $promos]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al obtener promociones activas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener promociones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function abrirMesa(Request $request): JsonResponse
    {
        return $this->mesaService->abrirMesa($request);
    }

    public function store(Request $request): JsonResponse
    {
        // Admite 'seccion' o 'zona' para evitar discrepancias con el frontend
        if (!$request->filled('seccion') && $request->filled('zona')) {
            $request->merge(['seccion' => $request->zona]);
        }

        $validated = $request->validate([
            'numero'    => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado'    => 'nullable|string|in:disponible,ocupada,reservada,limpieza',
            'seccion'   => 'nullable|string|max:50',
            'forma'     => 'nullable|string|in:redonda,cuadrada'
        ]);

        if (empty($validated['seccion'])) {
            $validated['seccion'] = 'Salón';
        }

        $mesa = Mesa::create(array_merge($validated, [
            'posicion_x' => $request->input('posicion_x', 20),
            'posicion_y' => $request->input('posicion_y', 20),
            'ancho'      => 60,
            'alto'       => 60
        ]));

        return response()->json(['success' => true, 'message' => 'Mesa creada', 'mesa' => $mesa], 201);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);

        if (!$request->filled('seccion') && $request->filled('zona')) {
            $request->merge(['seccion' => $request->zona]);
        }

        $validated = $request->validate([
            'numero'     => 'sometimes|string|max:20|unique:mesas,numero,' . $mesa->id,
            'capacidad'  => 'sometimes|integer|min:1',
            'seccion'    => 'sometimes|string|max:50',
            'estado'     => 'sometimes|string|in:disponible,ocupada,reservada,limpieza',
            'forma'      => 'sometimes|string',
            'posicion_x' => 'sometimes|integer',
            'posicion_y' => 'sometimes|integer',
        ]);

        $mesa->update($validated);
        return response()->json(['success' => true, 'mesa' => $mesa]);
    }

    public function destroy($id)
    {
        Mesa::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Mesa eliminada']);
    }

    public function apiIndex(): JsonResponse
    {
        return response()->json(Mesa::all());
    }

    public function guardarPlano(Request $request): JsonResponse
    {
        $request->validate([
            'mesas' => 'required|array'
        ]);

        try {
            foreach ($request->mesas as $mesaData) {
                Mesa::where('id', $mesaData['id'])->update([
                    'posicion_x' => $mesaData['posicion_x'],
                    'posicion_y' => $mesaData['posicion_y']
                ]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function actualizarPropina(Request $request, $mesaId)
    {
        $request->validate([
            'propina' => 'required|numeric|min:0'
        ]);

        $mesa = Mesa::findOrFail($mesaId);

        $orden = Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', Orden::getEstadosActivos())
            ->latest()
            ->first();

        if (!$orden) {
            $orden = Orden::create([
                'numero_orden'   => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id'        => $mesa->id,
                'mesero_id'      => auth()->id(),
                'estado'         => Orden::ESTADO_PENDIENTE,
                'abierta_el'     => now(),
                'personas'       => $mesa->capacidad ?? 1,
                'propina'        => $request->propina,
            ]);
        } else {
            $orden->update(['propina' => $request->propina]);
        }

        return response()->json([
            'success' => true, 
            'propina' => $orden->propina
        ]);
    }
}