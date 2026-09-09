<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Insumo;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    /**
     * Muestra el catálogo de Alimentos (Menú) y sus recetas.
     */
    public function index()
    {
        $productos = Producto::with([
                                'categoria:id,nombre', 
                                'insumos:id,nombre,unidad_medida',
                                'variantes:id,producto_id,nombre,precio,esta_disponible'
                             ])
                             ->select([
                                 'id', 'categoria_id', 'nombre', 'descripcion', 'precio',
                                 'tiene_variantes', 'se_vende_por_peso', 'precio_por_100g', 'esta_disponible',
                                 'created_at', 'updated_at', 'deleted_at',
                             ])
                             ->orderBy('nombre')
                             ->get();

        $categorias = Categoria::orderBy('nombre')->select(['id', 'nombre'])->get();

        $insumosDisponibles = Insumo::where('esta_activo', true)
                                    ->orderBy('nombre')
                                    ->select(['id', 'nombre', 'unidad_medida', 'stock_actual'])
                                    ->get();

        return view('admin.productos.index', compact('productos', 'categorias'), ['insumos' => $insumosDisponibles]);
    }

    /**
     * Normaliza las variantes recibidas desde cualquier formato de formulario.
     */
    private function normalizarVariantes(Request $request): array
    {
        $variantes = [];

        // Caso 1: Vienen como array asociativo directo [['nombre' => ..., 'precio' => ...]]
        if ($request->has('variantes') && is_array($request->variantes)) {
            foreach ($request->variantes as $v) {
                if (is_array($v) && !empty($v['nombre'])) {
                    $variantes[] = [
                        'nombre' => trim($v['nombre']),
                        'precio' => (float)($v['precio'] ?? 0),
                    ];
                }
            }
        }

        // Caso 2: Vienen en arrays paralelos (ej: variantes_nombres[] y variantes_precios[])
        $nombres = $request->input('variantes_nombres', $request->input('variante_nombre', []));
        $precios = $request->input('variantes_precios', $request->input('variante_precio', []));

        if (empty($variantes) && is_array($nombres)) {
            foreach ($nombres as $idx => $nombre) {
                if (!empty(trim($nombre))) {
                    $variantes[] = [
                        'nombre' => trim($nombre),
                        'precio' => isset($precios[$idx]) ? (float)$precios[$idx] : 0,
                    ];
                }
            }
        }

        return $variantes;
    }

    /**
     * Registra un nuevo platillo y guarda sus variantes o receta.
     */
    public function store(Request $request)
    {
        $tieneVariantes = filter_var($request->input('tiene_variantes'), FILTER_VALIDATE_BOOLEAN);
        $sePorPeso = filter_var($request->input('se_vende_por_peso'), FILTER_VALIDATE_BOOLEAN);
        $variantesNormalizadas = $this->normalizarVariantes($request);

        $request->merge([
            'nombre' => trim($request->nombre ?? ''),
            'tiene_variantes' => $tieneVariantes,
            'se_vende_por_peso' => $sePorPeso,
            'variantes' => $variantesNormalizadas,
        ]);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'categoria_id'         => 'required|exists:categorias,id',
            'tiene_variantes'      => 'boolean',
            'se_vende_por_peso'    => 'boolean',
            'precio'               => 'nullable|numeric|min:0',
            'precio_por_100g'      => 'nullable|numeric|min:0',
            'variantes'            => 'nullable|array',
            'insumos'              => 'nullable|array',
            'insumos.*'            => 'exists:insumos,id',
            'cantidades'           => 'nullable|array',
        ]);

        // Validación condicional de negocio
        if (!$tieneVariantes && !$sePorPeso && (!isset($request->precio) || $request->precio === '')) {
            return response()->json(['message' => 'El precio base es obligatorio para productos sin variantes.'], 422);
        }

        if ($tieneVariantes && empty($variantesNormalizadas)) {
            return response()->json(['message' => 'Debes registrar al menos una variante con nombre y precio.'], 422);
        }

        try {
            DB::beginTransaction();

            $precioBase = ($tieneVariantes || $sePorPeso) ? 0 : (float)($request->precio ?? 0);

            $producto = Producto::create([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => $precioBase,
                'tiene_variantes'   => $tieneVariantes,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? (float)$request->precio_por_100g : null,
                'esta_disponible'   => filter_var($request->input('esta_disponible', true), FILTER_VALIDATE_BOOLEAN),
            ]);

            // Guardar variantes
            if ($tieneVariantes && !empty($variantesNormalizadas)) {
                foreach ($variantesNormalizadas as $vData) {
                    $producto->variantes()->create([
                        'nombre'          => $vData['nombre'],
                        'precio'          => $vData['precio'],
                        'esta_disponible' => true,
                    ]);
                }
            }

            // Guardar receta / insumos
            if ($request->filled('insumos') && $request->filled('cantidades')) {
                $receta = [];
                foreach ($request->insumos as $index => $insumoId) {
                    if (isset($request->cantidades[$index]) && (float)$request->cantidades[$index] > 0) {
                        $receta[$insumoId] = [
                            'cantidad_usada' => (float)$request->cantidades[$index]
                        ];
                    }
                }
                if (!empty($receta)) {
                    $producto->insumos()->sync($receta);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Producto guardado correctamente.'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un platillo, sus variantes y su receta.
     */
    public function update(Request $request, $id)
    {
        $tieneVariantes = filter_var($request->input('tiene_variantes'), FILTER_VALIDATE_BOOLEAN);
        $sePorPeso = filter_var($request->input('se_vende_por_peso'), FILTER_VALIDATE_BOOLEAN);
        $variantesNormalizadas = $this->normalizarVariantes($request);

        $request->merge([
            'nombre' => trim($request->nombre ?? ''),
            'tiene_variantes' => $tieneVariantes,
            'se_vende_por_peso' => $sePorPeso,
            'variantes' => $variantesNormalizadas,
        ]);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'categoria_id'         => 'required|exists:categorias,id',
            'tiene_variantes'      => 'boolean',
            'se_vende_por_peso'    => 'boolean',
            'precio'               => 'nullable|numeric|min:0',
            'precio_por_100g'      => 'nullable|numeric|min:0',
            'variantes'            => 'nullable|array',
            'insumos'              => 'nullable|array',
            'insumos.*'            => 'exists:insumos,id',
            'cantidades'           => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            $precioBase = ($tieneVariantes || $sePorPeso) ? 0 : (float)($request->precio ?? 0);

            $producto->update([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => $precioBase,
                'tiene_variantes'   => $tieneVariantes,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? (float)$request->precio_por_100g : null,
                'esta_disponible'   => filter_var($request->input('esta_disponible', true), FILTER_VALIDATE_BOOLEAN),
            ]);

            // Sincronizar variantes
            $producto->variantes()->delete();
            if ($tieneVariantes && !empty($variantesNormalizadas)) {
                foreach ($variantesNormalizadas as $vData) {
                    $producto->variantes()->create([
                        'nombre'          => $vData['nombre'],
                        'precio'          => $vData['precio'],
                        'esta_disponible' => true,
                    ]);
                }
            }

            // Sincronizar receta
            $receta = [];
            if ($request->filled('insumos') && $request->filled('cantidades')) {
                foreach ($request->insumos as $index => $insumoId) {
                    if (isset($request->cantidades[$index]) && (float)$request->cantidades[$index] > 0) {
                        $receta[$insumoId] = [
                            'cantidad_usada' => (float)$request->cantidades[$index]
                        ];
                    }
                }
            }
            $producto->insumos()->sync($receta);

            DB::commit();
            return response()->json(['message' => 'Producto actualizado correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $nombre = $producto->nombre;
        $producto->delete();

        return response()->json(['message' => "El producto ({$nombre}) fue eliminado correctamente."]);
    }

    public function toggleDisponibilidad($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->esta_disponible = !$producto->esta_disponible;
        $producto->save();

        $estadoStr = $producto->esta_disponible ? 'habilitado' : 'deshabilitado';

        return response()->json([
            'message'         => "El producto ({$producto->nombre}) ha sido {$estadoStr}.",
            'esta_disponible' => $producto->esta_disponible,
        ]);
    }

    public function getProductos(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'insumos', 'modificadores', 'variantes'])
            ->select([
                'id', 'categoria_id', 'nombre', 'descripcion', 'precio',
                'tiene_variantes', 'se_vende_por_peso', 'precio_por_100g', 'esta_disponible',
                'updated_at',
            ])
            ->get()
            ->groupBy(function ($producto) {
                return $producto->categoria->nombre ?? 'Sin Categoría';
            });

        return response()->json($productos);
    }

    public function getEstadisticas(): JsonResponse
    {
        return response()->json([
            'total'       => Producto::count(),
            'disponibles' => Producto::where('esta_disponible', true)->count(),
            'categorias'  => Categoria::count(),
        ]);
    }
}