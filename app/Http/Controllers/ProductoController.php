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
     * Registra un nuevo platillo y guarda sus variantes o receta.
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'categoria_id'         => 'required|exists:categorias,id',
            'tiene_variantes'      => 'sometimes|boolean',
            'se_vende_por_peso'    => 'sometimes|boolean',
            
            // El precio base es obligatorio solo si no tiene variantes ni se vende por peso
            'precio'               => 'nullable|required_unless:tiene_variantes,1|numeric|min:0',
            'precio_por_100g'      => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            
            // Validación de las variantes (Bistec, Cecina, etc.)
            'variantes'            => 'nullable|required_if:tiene_variantes,1|array',
            'variantes.*.nombre'   => 'required_with:variantes|string|max:255',
            'variantes.*.precio'   => 'required_with:variantes|numeric|min:0',

            // Insumos / Receta
            'insumos'              => 'nullable|array',
            'insumos.*'            => 'exists:insumos,id',
            'cantidades'           => 'nullable|array',
            'cantidades.*'         => 'required_with:insumos|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $tieneVariantes = $request->boolean('tiene_variantes');
            $sePorPeso = $request->boolean('se_vende_por_peso');

            $producto = new Producto([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => ($tieneVariantes || $sePorPeso) ? 0 : ($request->precio ?? 0),
                'tiene_variantes'   => $tieneVariantes,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? $request->precio_por_100g : null,
                'esta_disponible'   => $request->boolean('esta_disponible', true),
            ]);

            $producto->save();

            // Guardar variantes si el switch fue activado
            if ($tieneVariantes && $request->filled('variantes')) {
                foreach ($request->variantes as $varianteData) {
                    if (!empty($varianteData['nombre']) && isset($varianteData['precio'])) {
                        $producto->variantes()->create([
                            'nombre'          => trim($varianteData['nombre']),
                            'precio'          => (float)$varianteData['precio'],
                            'esta_disponible' => true
                        ]);
                    }
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
            return response()->json(['message' => 'Error inesperado al guardar el producto.'], 500);
        }
    }

    /**
     * Actualiza un platillo, sus variantes y su receta.
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'categoria_id'         => 'required|exists:categorias,id',
            'tiene_variantes'      => 'sometimes|boolean',
            'se_vende_por_peso'    => 'sometimes|boolean',
            'precio'               => 'nullable|required_unless:tiene_variantes,1|numeric|min:0',
            'precio_por_100g'      => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            
            'variantes'            => 'nullable|required_if:tiene_variantes,1|array',
            'variantes.*.nombre'   => 'required_with:variantes|string|max:255',
            'variantes.*.precio'   => 'required_with:variantes|numeric|min:0',

            'insumos'              => 'nullable|array',
            'insumos.*'            => 'exists:insumos,id',
            'cantidades'           => 'nullable|array',
            'cantidades.*'         => 'required_with:insumos|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            $tieneVariantes = $request->boolean('tiene_variantes');
            $sePorPeso = $request->boolean('se_vende_por_peso');

            $producto->update([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => ($tieneVariantes || $sePorPeso) ? 0 : ($request->precio ?? 0),
                'tiene_variantes'   => $tieneVariantes,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? $request->precio_por_100g : null,
                'esta_disponible'   => $request->boolean('esta_disponible'),
            ]);

            // Sincronizar variantes: si se desactivó el switch se limpian; si sigue activo se reconstruyen
            $producto->variantes()->delete();
            if ($tieneVariantes && $request->filled('variantes')) {
                foreach ($request->variantes as $varianteData) {
                    if (!empty($varianteData['nombre']) && isset($varianteData['precio'])) {
                        $producto->variantes()->create([
                            'nombre'          => trim($varianteData['nombre']),
                            'precio'          => (float)$varianteData['precio'],
                            'esta_disponible' => true
                        ]);
                    }
                }
            }

            // Sincronizar receta / insumos
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
            return response()->json(['message' => 'Error inesperado al actualizar el producto.'], 500);
        }
    }

    /**
     * Elimina un platillo del menú (Soporta Soft Delete nativo).
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $nombre = $producto->nombre;
        $producto->delete();

        return response()->json(['message' => "El producto ({$nombre}) fue eliminado correctamente."]);
    }

    /**
     * Alterna la disponibilidad instantánea del platillo (Switch de operaciones).
     */
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

    /**
     * Devuelve los productos agrupados por categoría, para renderizar las tarjetas.
     */
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