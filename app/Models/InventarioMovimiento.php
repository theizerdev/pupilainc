<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = [
        'producto_id', 'almacen_id', 'tipo', 'cantidad',
        'stock_anterior', 'stock_nuevo', 'costo_unitario',
        'referencia', 'observacion', 'user_id',
        'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'cantidad'       => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo'    => 'integer',
        'costo_unitario' => 'decimal:2',
    ];

    const TIPOS = [
        'entrada'               => ['label' => 'Entrada',               'color' => 'success', 'icon' => 'ri-arrow-down-circle-line',   'signo' => '+'],
        'salida'                => ['label' => 'Salida',                'color' => 'danger',  'icon' => 'ri-arrow-up-circle-line',     'signo' => '-'],
        'ajuste_positivo'       => ['label' => 'Ajuste +',              'color' => 'info',    'icon' => 'ri-add-circle-line',          'signo' => '+'],
        'ajuste_negativo'       => ['label' => 'Ajuste -',              'color' => 'warning', 'icon' => 'ri-indeterminate-circle-line','signo' => '-'],
        'transferencia_entrada' => ['label' => 'Transferencia entrada', 'color' => 'primary', 'icon' => 'ri-swap-box-line',            'signo' => '+'],
        'transferencia_salida'  => ['label' => 'Transferencia salida',  'color' => 'primary', 'icon' => 'ri-swap-box-line',            'signo' => '-'],
        'devolucion'            => ['label' => 'Devolución',            'color' => 'secondary','icon' => 'ri-reply-line',              'signo' => '+'],
        'vencimiento'           => ['label' => 'Baja por vencimiento',  'color' => 'dark',    'icon' => 'ri-time-line',               'signo' => '-'],
    ];

    public function producto() { return $this->belongsTo(Producto::class); }
    public function almacen()  { return $this->belongsTo(Almacen::class); }
    public function user()     { return $this->belongsTo(\App\Models\User::class); }
    public function empresa()  { return $this->belongsTo(Empresa::class); }

    public function getTipoInfoAttribute(): array
    {
        return self::TIPOS[$this->tipo] ?? ['label' => $this->tipo, 'color' => 'secondary', 'icon' => 'ri-question-line', 'signo' => ''];
    }

    // Scopes for filtering by type
    public function scopeEntrada($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalida($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeAjuste($query)
    {
        return $query->whereIn('tipo', ['ajuste_positivo', 'ajuste_negativo']);
    }

    /**
     * Registra un movimiento y actualiza el stock atómicamente.
     */
    public static function registrar(
        int    $productoId,
        int    $almacenId,
        string $tipo,
        int    $cantidad,
        ?float $costoUnitario = null,
        ?string $referencia   = null,
        ?string $observacion  = null
    ): self {
        return DB::transaction(function () use ($productoId, $almacenId, $tipo, $cantidad, $costoUnitario, $referencia, $observacion) {
            $stock = InventarioStock::firstOrCreate(
                ['producto_id' => $productoId, 'almacen_id' => $almacenId],
                ['cantidad' => 0]
            );

            $stockAnterior = $stock->cantidad;
            $esSalida = in_array($tipo, ['salida', 'ajuste_negativo', 'transferencia_salida', 'vencimiento']);
            $stockNuevo = $esSalida
                ? max(0, $stockAnterior - $cantidad)
                : $stockAnterior + $cantidad;

            $stock->update(['cantidad' => $stockNuevo]);

            $user = auth()->user();

            return self::create([
                'producto_id'    => $productoId,
                'almacen_id'     => $almacenId,
                'tipo'           => $tipo,
                'cantidad'       => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
                'costo_unitario' => $costoUnitario,
                'referencia'     => $referencia,
                'observacion'    => $observacion,
                'user_id'        => $user?->id,
                'empresa_id'     => $user?->empresa_id,
                'sucursal_id'    => $user?->sucursal_id,
            ]);
        });
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }
}
