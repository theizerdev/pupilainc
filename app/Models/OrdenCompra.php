<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;
use Illuminate\Support\Facades\DB;

class OrdenCompra extends Model
{
    use HasFactory, Multitenantable;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'numero', 'proveedor_id', 'almacen_id', 'estado',
        'fecha_emision', 'fecha_esperada', 'fecha_recepcion',
        'total', 'observaciones', 'generada_automaticamente',
        'user_id', 'empresa_id', 'sucursal_id',
    ];

    protected $casts = [
        'fecha_emision'           => 'date',
        'fecha_esperada'          => 'date',
        'fecha_recepcion'         => 'date',
        'total'                   => 'decimal:2',
        'generada_automaticamente'=> 'boolean',
    ];

    const ESTADOS = [
        'borrador'         => ['label' => 'Borrador',           'color' => 'secondary'],
        'enviada'          => ['label' => 'Enviada',            'color' => 'primary'],
        'recibida_parcial' => ['label' => 'Recibida parcial',   'color' => 'warning'],
        'recibida'         => ['label' => 'Recibida',           'color' => 'success'],
        'cancelada'        => ['label' => 'Cancelada',          'color' => 'danger'],
    ];

    public function proveedor()  { return $this->belongsTo(Proveedor::class); }
    public function almacen()    { return $this->belongsTo(Almacen::class); }
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
    public function empresa()    { return $this->belongsTo(Empresa::class); }
    public function sucursal()   { return $this->belongsTo(Sucursal::class); }

    public function detalles()
    {
        return $this->hasMany(OrdenCompraDetalle::class);
    }

    public function getEstadoInfoAttribute(): array
    {
        return self::ESTADOS[$this->estado] ?? ['label' => $this->estado, 'color' => 'secondary'];
    }

    public function recalcularTotal(): void
    {
        $this->update(['total' => $this->detalles()->sum('subtotal')]);
    }

    /**
     * Recibe la orden completa: genera movimientos de entrada para cada detalle.
     */
    public function recibirCompleta(): void
    {
        DB::transaction(function () {
            foreach ($this->detalles as $detalle) {
                $pendiente = $detalle->cantidad_solicitada - $detalle->cantidad_recibida;
                if ($pendiente <= 0) continue;

                InventarioMovimiento::registrar(
                    productoId:    $detalle->producto_id,
                    almacenId:     $this->almacen_id,
                    tipo:          'entrada',
                    cantidad:      $pendiente,
                    costoUnitario: $detalle->precio_unitario,
                    referencia:    "OC-{$this->numero}",
                    observacion:   "Recepción orden de compra #{$this->numero}",
                );

                $detalle->update(['cantidad_recibida' => $detalle->cantidad_solicitada]);
            }

            $this->update([
                'estado'          => 'recibida',
                'fecha_recepcion' => now(),
            ]);
        });
    }

    public static function generarNumero(): string
    {
        $ultimo = static::withoutGlobalScopes()->max('id') ?? 0;
        return 'OC-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            $query->where('empresa_id', auth()->user()->empresa_id);
        }
        return $query;
    }

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estado', ['cancelada', 'recibida']);
    }
}
