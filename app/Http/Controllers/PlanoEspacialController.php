<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanoEspacialController extends Controller
{
    /**
     * Renderiza la vista del mapa
     */
    public function index()
    {
        $mesas = Mesa::soloLocales()->orderBy('numero', 'asc')->get();
        $plataformasDelivery = \App\Models\PlataformaDelivery::activas()->orderBy('nombre')->get();
        $cajaAbierta = \App\Models\CajaMovimiento::where('estado', 'abierta')->exists();

        return view('admin.mesas.plano-espacial', compact('mesas', 'plataformasDelivery', 'cajaAbierta'));
    }

    /**
     * API que el JS lee constantemente (polling cada 5s).
     * Devuelve las mesas locales con soporte de filtro por sección.
     */
    public function getMesas(Request $request): JsonResponse
    {
        // El parámetro puede llamarse 'zona' (legacy) o 'seccion' (nuevo)
        $seccion = $request->query('seccion') ?? $request->query('zona');

        $query = Mesa::soloLocales()
            ->with(['mesero:id,nombre'])
            ->withCount(['ordenes as ordenes_activas_count' => function ($q) {
                $q->where('estado', '!=', 'pagada');
            }])
            ->orderBy('numero', 'asc');

        // Filtrar por sección si viene y no es "todas"
        if ($seccion && strtolower($seccion) !== 'todas') {
            $query->where('seccion', $seccion);
        }

        $mesas = $query->get();

        $mesasFormateadas = $mesas->map(function ($mesa) {
            return [
                'id'             => $mesa->id,
                'numero'         => $mesa->numero,
                'capacidad'      => (int) $mesa->capacidad,
                'estado'         => $mesa->estado,
                'seccion'        => $mesa->seccion ?? 'Salón',
                'forma'          => $mesa->forma ?? 'redonda',
                'posicion_x'     => $mesa->posicion_x !== null ? (float) $mesa->posicion_x : 20,
                'posicion_y'     => $mesa->posicion_y !== null ? (float) $mesa->posicion_y : 20,
                'ancho'          => (int) ($mesa->ancho ?? 60),
                'alto'           => (int) ($mesa->alto ?? 60),
                'estadoVisual'   => $mesa->estado,
                'mesero'         => $mesa->mesero?->nombre ?? 'Sin asignar',
                'mesero_id'      => $mesa->mesero_id ? (int) $mesa->mesero_id : null,
                'totalConsumo'   => (float) ($mesa->total_consumo ?? 0),
                'ordenesActivas' => (int) $mesa->ordenes_activas_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mesasFormateadas,
        ]);
    }

    /**
     * Guarda posiciones tras arrastrar mesas en el plano.
     */
    public function guardarPlano(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mesas'              => 'required|array|min:1',
                'mesas.*.id'         => 'required|integer|exists:mesas,id',
                'mesas.*.posicion_x' => 'required|numeric|min:0',
                'mesas.*.posicion_y' => 'required|numeric|min:0',
                'mesas.*.ancho'      => 'nullable|integer|min:30|max:200',
                'mesas.*.alto'       => 'nullable|integer|min:30|max:200',
                'mesas.*.forma'      => 'nullable|in:redonda,cuadrada',
                'mesas.*.seccion'    => 'nullable|string|max:50',
            ]);

            DB::transaction(function () use ($validated) {
                foreach ($validated['mesas'] as $mesaData) {
                    $updateData = [
                        'posicion_x' => $mesaData['posicion_x'],
                        'posicion_y' => $mesaData['posicion_y'],
                    ];

                    if (isset($mesaData['ancho']))   $updateData['ancho']   = $mesaData['ancho'];
                    if (isset($mesaData['alto']))    $updateData['alto']    = $mesaData['alto'];
                    if (isset($mesaData['forma']))   $updateData['forma']   = $mesaData['forma'];
                    if (isset($mesaData['seccion'])) $updateData['seccion'] = $mesaData['seccion'];

                    Mesa::where('id', $mesaData['id'])->update($updateData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Plano espacial guardado correctamente.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error en guardarPlano: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al guardar la disposición.',
            ], 500);
        }
    }

    /**
     * Elimina la mesa permanentemente de la base de datos.
     */
    public function eliminarDelPlano($id): JsonResponse
    {
        try {
            $mesa = Mesa::findOrFail($id);

            $ordenesActivas = $mesa->ordenes()->where('estado', '!=', 'pagada')->count();

            if ($ordenesActivas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar: la mesa tiene órdenes activas sin cerrar.',
                ], 422);
            }

            $mesa->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no existe o ya fue eliminada.',
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de BD al eliminar mesa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la mesa tiene registros relacionados.',
            ], 409);

        } catch (\Exception $e) {
            Log::error('Error al eliminar mesa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la mesa.',
            ], 500);
        }
    }

    /**
     * Crea una nueva mesa desde el plano espacial.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'numero'    => 'required|string|max:50|unique:mesas,numero',
                'capacidad' => 'required|integer|min:1|max:20',
                'estado'    => 'required|in:disponible,reservada,limpieza',
                'seccion'   => 'nullable|string|max:50',
            ]);

            $mesa = Mesa::create([
                'numero'     => $validated['numero'],
                'capacidad'  => $validated['capacidad'],
                'estado'     => $validated['estado'],
                'seccion'    => $validated['seccion'] ?? 'Salón',
                'forma'      => 'redonda',
                'posicion_x' => 20,
                'posicion_y' => 20,
                'ancho'      => 60,
                'alto'       => 60,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa creada correctamente.',
                'data'    => $mesa,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['numero'][0] ?? $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al crear mesa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la mesa.',
            ], 500);
        }
    }

    /**
     * Actualiza número, capacidad y sección desde el panel de propiedades.
     */
    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'numero'    => 'required|string|max:50|unique:mesas,numero,' . $mesa->id,
            'capacidad' => 'required|integer|min:1',
            'seccion'   => 'nullable|string|max:50',
        ]);

        $mesa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
        ]);
    }
}