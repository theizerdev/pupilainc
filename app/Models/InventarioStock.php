<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioStock extends Model
{
    protected $table = 'inventario_stock';

    protected $fillable = ['producto_id', 'almacen_id', 'cantidad'];

    protected $casts = ['cantidad' => 'integer'];

    public function producto() { return $this->belongsTo(Producto::class); }
    public function almacen()  { return $this->belongsTo(Almacen::class); }
}
