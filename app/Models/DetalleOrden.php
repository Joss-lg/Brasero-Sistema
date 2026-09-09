<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden';

    protected $fillable = [
        'orden_id',
        'lote_envio',
        'producto_id',
        'producto_variante_id', // <-- Habilitado para asignación masiva
        'cantidad',
        'precio_unitario',
        'estado',
        'estado_preparacion',
        'notas',
        'gramaje',
        'tiempo',
        'transaccion_id',
        'cancelado_motivo',
        'cancelado_por',
        'cancelado_en',
        'cuenta_division_numero',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'cancelado_en' => 'datetime',
        'cuenta_division_numero' => 'integer',
        'producto_variante_id' => 'integer',
    ];

    // Relación con Orden
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function transaccion()
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id');
    }

    // Relación con Producto base
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Relación con la Variante/Proteína seleccionada
    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    public function promocionAplicada()
    {
        return $this->hasOne(OrdenPromocion::class, 'detalle_orden_id');
    }

    // Cómo se reparte la cantidad de este producto entre personas al dividir cuenta
    public function divisiones()
    {
        return $this->hasMany(DetalleOrdenDivision::class, 'detalle_orden_id');
    }

    // Quién autorizó la cancelación (Capitán/Admin)
    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    // Scope para excluir cancelados en cualquier consulta
    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'cancelado');
    }

    // Helper de conveniencia
    public function getEstaCanceladoAttribute(): bool
    {
        return $this->estado === 'cancelado';
    }

    // Calcular subtotal del detalle
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}