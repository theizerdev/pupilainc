<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoBancario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_bancarios';

    protected $fillable = [
        'cuenta_bancaria_id',
        'fecha',
        'referencia',
        'descripcion',
        'monto',
        'tipo_movimiento',
        'conciliado',
        'asiento_detalle_id',
        'observaciones',
        'empresa_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'conciliado' => 'boolean',
    ];

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_bancaria_id');
    }

    public function asientoDetalle(): BelongsTo
    {
        return $this->belongsTo(AsientoDetalle::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getTipoMovimientoBadgeAttribute(): string
    {
        return match($this->tipo_movimiento) {
            'debito' => 'success',
            'credito' => 'danger',
            default => 'secondary'
        };
    }

    public function getTipoMovimientoTextoAttribute(): string
    {
        return match($this->tipo_movimiento) {
            'debito' => 'Débito',
            'credito' => 'Crédito',
            default => 'Desconocido'
        };
    }

    public function getMontoFormateadoAttribute(): string
    {
        $signo = $this->tipo_movimiento === 'credito' ? '-' : '+';
        return $signo . number_format($this->monto, 2, ',', '.');
    }
}