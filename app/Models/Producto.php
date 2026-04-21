<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;
use Carbon\Carbon;

class Producto extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'productos';

    protected $fillable = [
        'nombre', 'codigo', 'sku', 'descripcion',
        'categoria_producto_id', 'marca_id', 'proveedor_id',
        'unidad_medida', 'precio_costo', 'precio_venta',
        'stock_minimo', 'stock_maximo', 'punto_reorden',
        'fecha_vencimiento', 'ubicacion_fisica',
        'requiere_receta', 'es_medicamento', 'status', 'imagen',
        'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'precio_costo'      => 'decimal:2',
        'precio_venta'      => 'decimal:2',
        'stock_minimo'      => 'integer',
        'stock_maximo'      => 'integer',
        'punto_reorden'     => 'integer',
        'fecha_vencimiento' => 'date',
        'requiere_receta'   => 'boolean',
        'es_medicamento'    => 'boolean',
        'status'            => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────
    public function categoria()  { return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id'); }
    public function marca()      { return $this->belongsTo(Marca::class); }
    public function proveedor()  { return $this->belongsTo(Proveedor::class); }
    public function empresa()    { return $this->belongsTo(Empresa::class); }
    public function sucursal()   { return $this->belongsTo(Sucursal::class); }

    public function stocks()
    {
        return $this->hasMany(InventarioStock::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class)->latest();
    }

    public function ordenesDetalle()
    {
        return $this->hasMany(OrdenCompraDetalle::class);
    }

    // ─── Stock ────────────────────────────────────────────────────
    public function stockEnAlmacen(int $almacenId): int
    {
        return $this->stocks()->where('almacen_id', $almacenId)->value('cantidad') ?? 0;
    }

    public function stockTotal(): int
    {
        return $this->stocks()->sum('cantidad');
    }

    // ─── Alertas ──────────────────────────────────────────────────
    public function getAlertaStockAttribute(): ?string
    {
        $stock = $this->stockTotal();
        if ($stock === 0)                          return 'sin_stock';
        if ($stock <= $this->stock_minimo)         return 'critico';
        if ($stock <= $this->punto_reorden)        return 'bajo';
        return null;
    }

    public function getAlertaVencimientoAttribute(): ?string
    {
        if (!$this->fecha_vencimiento) return null;
        $dias = now()->diffInDays($this->fecha_vencimiento, false);
        if ($dias < 0)   return 'vencido';
        if ($dias <= 30) return 'critico';
        if ($dias <= 90) return 'proximo';
        return null;
    }

    public function getDiasParaVencerAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) return null;
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getValorizacionAttribute(): float
    {
        return $this->stockTotal() * (float) $this->precio_costo;
    }

    // ─── Scopes ───────────────────────────────────────────────────
    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }

    public function scopeActivos($query)       { return $query->where('status', true); }

    public function scopeStockBajo($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereColumn('cantidad', '<=', 'productos.punto_reorden')
              ->where('cantidad', '>', 0);
        });
    }

    public function scopeSinStock($query)
    {
        return $query->whereDoesntHave('stocks', fn($q) => $q->where('cantidad', '>', 0));
    }

    public function scopeProximosAVencer($query, int $dias = 90)
    {
        return $query->whereNotNull('fecha_vencimiento')
                     ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    public function scopeVencidos($query)
    {
        return $query->whereNotNull('fecha_vencimiento')
                     ->where('fecha_vencimiento', '<', now());
    }

    // ─── Boot ─────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($p) {
            if (!$p->codigo) {
                $p->codigo = 'PROD-' . str_pad(static::withoutGlobalScopes()->count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
