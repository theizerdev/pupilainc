<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaProducto extends Model
{
    use HasFactory;

    protected $table = 'ventas_productos';

    protected $fillable = [
        'pago_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'aplica_iva',
        'exento_iva',
        'iva_alicuota',
        'iva_monto',
        'costo_unitario',
        'costo_total',
        'almacen_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'aplica_iva' => 'boolean',
        'exento_iva' => 'boolean',
        'iva_alicuota' => 'decimal:2',
        'iva_monto' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($venta) {
            // Calcular subtotal
            $venta->subtotal = $venta->cantidad * $venta->precio_unitario;
            
            // Calcular IVA
            if ($venta->exento_iva || !$venta->aplica_iva) {
                $venta->iva_monto = 0;
            } else {
                $venta->iva_monto = $venta->subtotal * ($venta->iva_alicuota / 100);
            }

            // Calcular costo total
            if ($venta->costo_unitario) {
                $venta->costo_total = $venta->cantidad * $venta->costo_unitario;
            }
        });

        static::created(function ($venta) {
            // Reducir stock automáticamente
            $producto = $venta->producto;
            $almacenId = $venta->almacen_id ?? $venta->getAlmacenPrincipal();
            
            if ($almacenId && $producto) {
                InventarioMovimiento::registrar(
                    $producto->id,
                    $almacenId,
                    'salida',
                    $venta->cantidad,
                    $venta->costo_unitario,
                    "Venta - Pago #{$venta->pago->numero_completo}",
                    "Venta de producto: {$producto->nombre}"
                );
            }
        });
    }

    private function getAlmacenPrincipal(): ?int
    {
        return Almacen::where('empresa_id', auth()->user()->empresa_id ?? 1)
            ->where('status', true)
            ->where('sucursal_id', auth()->user()->sucursal_id ?? 1)
            ->first()?->id;
    }

    public function getTotalConIvaAttribute(): float
    {
        return $this->subtotal + $this->iva_monto;
    }

    public function getUtilidadAttribute(): float
    {
        if (!$this->costo_total) return 0;
        return $this->subtotal - $this->costo_total;
    }

    public function getMargenAttribute(): float
    {
        if (!$this->subtotal || $this->subtotal == 0) return 0;
        return ($this->utilidad / $this->subtotal) * 100;
    }
}