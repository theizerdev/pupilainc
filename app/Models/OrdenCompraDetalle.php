<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraDetalle extends Model
{
    protected $table = 'ordenes_compra_detalle';

    protected $fillable = [
        'orden_compra_id', 'producto_id',
        'cantidad_solicitada', 'cantidad_recibida',
        'precio_unitario', 'subtotal',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
        'cantidad_recibida'   => 'integer',
        'precio_unitario'     => 'decimal:2',
        'subtotal'            => 'decimal:2',
    ];

    public function ordenCompra() { return $this->belongsTo(OrdenCompra::class); }
    public function producto()    { return $this->belongsTo(Producto::class); }

    public function getPendienteAttribute(): int
    {
        return $this->cantidad_solicitada - $this->cantidad_recibida;
    }
}
