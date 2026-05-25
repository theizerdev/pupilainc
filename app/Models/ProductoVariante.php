<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class ProductoVariante extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'producto_variantes';

    protected $fillable = [
        'producto_id',
        'tipo_variante_id',
        'valor_variante_id',
        'sku_variante',
        'atributo',
        'valor',
        'codigo_barras',
        'precio_costo',
        'precio_venta',
        'stock',
        'imagen',
        'imagen',
        'alt',
        'tamano',
        'peso',
        'presentacion',
        'unidad_medida',
        'orden',
        'status',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'peso'         => 'decimal:3',
        'status'       => 'boolean',
        'stock'        => 'integer',
        'orden'        => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tipoVariante()
    {
        return $this->belongsTo(TipoVariante::class);
    }

    public function valorVariante()
    {
        return $this->belongsTo(ValorVariante::class);
    }

    public function getLabelAttribute(): string
    {
        $tipo = $this->tipoVariante?->nombre ?? $this->atributo ?? 'Variante';
        $val  = $this->valorVariante?->valor ?? $this->valor ?? '';
        return trim("{$tipo}: {$val}");
    }

    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }
}
