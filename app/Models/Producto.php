<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'precio',
        'tiene_variantes',
        'se_vende_por_peso',
        'precio_por_100g',
        'descripcion',
        'esta_disponible'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'tiene_variantes' => 'boolean',
        'se_vende_por_peso' => 'boolean',
        'precio_por_100g' => 'decimal:2',
        'tiempo_preparacion' => 'integer',
        'esta_disponible' => 'boolean',
    ];

    // Variantes de proteína, tamaño o presentación (ej: Bistec, Cecina, etc.)
    public function variantes(): HasMany
    {
        return $this->hasMany(ProductoVariante::class, 'producto_id');
    }

    // A qué categoría del menú pertenece (Ej: Postres)
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Qué ingredientes lleva este platillo
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'recetas', 'producto_id', 'insumo_id')
                    ->withPivot('cantidad_usada')
                    ->withTimestamps();
    }

    public function modificadores(): BelongsToMany
    {
        return $this->belongsToMany(Modificador::class, 'producto_modificadores');
    }

    public function promociones(): BelongsToMany
    {
        return $this->belongsToMany(Promocion::class, 'promocion_productos');
    }
}