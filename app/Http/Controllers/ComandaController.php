<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Schema, DB, Log};
use App\Models\{Mesa, Categoria, Producto, Orden, DetalleOrden, User, Configuracion};
use App\Services\ComandaService;

class ComandaController extends Controller
{
    protected $comandaService;

    public function __construct(ComandaService $comandaService)
    {
        $this->comandaService = $comandaService;
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();
        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
        $esCapitan = $rolSlug === 'capitan';

        // Restricciones de acceso
        if ($rolSlug === 'mesero' && Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
            abort(403, 'No tienes permiso para ver esta mesa.');
        }
        if ($esCapitan && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }

        $categorias = Categoria::all();

        // Carga de productos incluyendo sus variantes activas
        $productos = Producto::with([
            'categoria', 
            'modificadores',
            'variantes' => function($q) {
                $q->where('esta_disponible', true);
            }
        ])->orderBy('nombre', 'asc')->get();

        $mesasAbiertas = $esCapitan ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get() : collect();

        // Traer TODAS las órdenes activas de la mesa (puede haber varias rondas)
        $ordenesActivas = Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', Orden::getEstadosActivos())
            ->with(['detalles.producto', 'detalles.variante'])
            ->get();

        // Para compatibilidad con el resto del código que usa $comandaActiva
        $comandaActiva = $ordenesActivas->first();

        // Aplanar los detalles de TODAS las órdenes en una sola colección
        $platillosEnviados = $ordenesActivas->flatMap(function ($orden) {
            return $orden->detalles->map(function ($detalle) {
                $nombrePlatillo = $detalle->producto->nombre ?? 'Platillo';
                if (!empty($detalle->variante?->nombre)) {
                    $nombrePlatillo .= " ({$detalle->variante->nombre})";
                }

                return (object) [
                    'id'       => $detalle->id,
                    'nombre'   => $nombrePlatillo,
                    'cantidad' => $detalle->cantidad,
                    'precio'   => $detalle->precio_unitario,
                    'estado'   => $detalle->estado,
                ];
            });
        });

        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = false; // IVA desactivado
        $ivaPorcentaje = 0;

        return view('mesero.index', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan', 'comandaActiva', 'platillosEnviados', 'ivaHabilitado', 'ivaPorcentaje'));
    }

    public function enviar(Request $request)
    {
        $request->validate([
            'mesa_id'                  => 'required|exists:mesas,id',
            'platillos'                => 'required|array|min:1',
            'platillos.*.id'           => 'required|exists:productos,id',
            'platillos.*.variante_id'  => 'nullable|exists:producto_variantes,id', // Soporte para la variante
            'platillos.*.cantidad'     => 'required|integer|min:1',
            'platillos.*.precio'       => 'required|numeric',
            'platillos.*.modificadores'=> 'nullable|array',
            'platillos.*.gramaje'      => 'nullable|string',
            'platillos.*.tiempo'       => 'nullable|string',
            'total'                    => 'required|numeric|min:0',
            'personas'                 => 'required|integer|min:1',
            'descuento_porcentaje'     => 'required|numeric|min:0|max:100',
        ]);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $usuario = auth()->user();

            if ($usuario->rol?->slug !== 'capitan') {
                if (Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
                    return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
                }
            }

            $orden = $this->comandaService->procesarEnvio(
                $mesa,
                $request->platillos,
                $usuario,
                $request->total,
                $request->personas,
                $request->descuento_porcentaje
            );

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa actualizada con éxito.',
                'orden_id' => $orden->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al procesar comanda: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verificarCapitan(Request $request)
    {
        $request->validate(['nip' => 'required|string']);

        $usuario = User::where('codigo_empleado', $request->nip)
            ->whereNull('deleted_at')
            ->with('rol')
            ->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'NIP inválido.'
            ], 403);
        }

        // Solo el Administrador puede autorizar traspasos de mesa.
        $nombreRol = strtolower(trim($usuario->rol?->nombre ?? ''));
        $esAdmin = in_array($nombreRol, ['administrador', 'admin']) || $usuario->id === 1;

        if (!$esAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el Administrador puede autorizar traspasos de mesa.'
            ], 403);
        }

        $mesas = Mesa::orderBy('numero', 'asc')->get(['id', 'numero', 'estado']);

        return response()->json(['success' => true, 'mesas' => $mesas]);
    }

    public function transferirProductos(Request $request)
    {
        $request->validate([
            'mesa_origen_id'              => 'required|exists:mesas,id',
            'mesa_destino_id'             => 'required|exists:mesas,id',
            'mesero_destino_id'           => 'required|exists:users,id',
            'productos_nuevos'            => 'nullable|array',
            'productos_nuevos.*.id'       => 'required_with:productos_nuevos|exists:productos,id',
            'productos_nuevos.*.cantidad' => 'required_with:productos_nuevos|integer|min:1',
            'productos_nuevos.*.precio'   => 'required_with:productos_nuevos|numeric',
            'productos_enviados_ids'      => 'nullable|array',
            'productos_enviados_ids.*'    => 'integer|exists:detalles_orden,id',
        ]);

        if (empty($request->productos_nuevos) && empty($request->productos_enviados_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No seleccionaste ningún producto para traspasar.'
            ], 422);
        }

        try {
            $mesaOrigen  = Mesa::findOrFail($request->mesa_origen_id);
            $mesaDestino = Mesa::findOrFail($request->mesa_destino_id);
            $usuario = auth()->user();

            $meseroDestino = \App\Models\User::findOrFail($request->mesero_destino_id);

            $resultado = $this->comandaService->transferirProductos(
                $mesaOrigen,
                $mesaDestino,
                $request->productos_nuevos ?? [],
                $request->productos_enviados_ids ?? [],
                $usuario,
                $meseroDestino
            );

            $mesaOrigen->update(['mesero_id' => $meseroDestino->id]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa traspasada a ' . $meseroDestino->nombre . ' correctamente.',
                'orden_destino_id' => $resultado['orden_destino']->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al transferir productos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiMesasAbiertas()
    {
        $mesas = Mesa::all();
        return response()->json([
            'success' => true,
            'mesas_abiertas' => $mesas->where('estado', 'ocupada')->values(),
            'mesas_libres' => $mesas->where('estado', 'disponible')->values(),
        ]);
    }

    public function apiMeserosActivos()
    {
        $meseros = \App\Models\User::whereNull('deleted_at')
            ->where('id', '!=', auth()->id())
            ->with('rol:id,nombre')
            ->get(['id', 'nombre', 'rol_id']);

        return response()->json([
            'success' => true,
            'meseros' => $meseros->map(fn($u) => [
                'id'    => $u->id,
                'nombre' => $u->nombre,
                'rol'   => optional($u->rol)->nombre ?? 'Sin rol',
            ])->values(),
        ]);
    }

    public function storeMesa(Request $request)
    {
        $request->validate(['numero' => 'required', 'capacidad' => 'required|integer']);

        $mesa = Mesa::updateOrCreate(['numero' => $request->numero], [
            'estado' => 'ocupada',
            'capacidad' => $request->capacidad,
            'mesero_id' => auth()->user()->id
        ]);

        return response()->json(['success' => true, 'mesa' => $mesa]);
    }

    public function reabrir(Request $request)
    {
        $mesa = Mesa::findOrFail($request->mesa_id);
        $mesa->update(['estado' => 'ocupada', 'mesero_id' => auth()->user()->id]);

        return response()->json(['success' => true]);
    }

    public function precuenta($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);

        $ordenesActivas = Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', Orden::getEstadosActivos())
            ->with(['detalles.producto', 'detalles.variante', 'mesero:id,nombre'])
            ->get();

        $orden = $ordenesActivas->first();
        $detalles = $ordenesActivas->flatMap(fn($o) => $o->detalles);

        $subtotal = $detalles->sum(fn ($d) => $d->cantidad * $d->precio_unitario);

        $descuentoPorcentaje = 0;
        if ($orden && Schema::hasColumn('ordenes', 'descuento_porcentaje')) {
            $descuentoPorcentaje = (float) ($orden->descuento_porcentaje ?? 0);
        }

        $descuento = $subtotal * ($descuentoPorcentaje / 100);
        $subtotalConDescuento = max(0, $subtotal - $descuento);

        $iva = 0;
        $ivaHabilitado = false;
        $ivaPorcentaje = 0;

        $propina = 0;
        if ($orden && Schema::hasColumn('ordenes', 'propina')) {
            $propina = (float) ($orden->propina ?? 0);
        }

        $total = $subtotalConDescuento + $iva + $propina;

        return view('mesero.precuenta', [
            'mesa'      => $mesa,
            'orden'     => $orden,
            'detalles'  => $detalles,
            'subtotal'  => $subtotal,
            'descuento' => $descuento,
            'iva'       => $iva,
            'propina'   => $propina,
            'total'     => $total,
            'fecha'     => now(),
        ]);
    }
}