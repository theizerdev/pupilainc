<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConciliacionBancaria extends Model
{
    use HasFactory;

    protected $table = 'conciliaciones_bancarias';

    protected $fillable = [
        'cuenta_bancaria_id',
        'fecha_corte',
        'saldo_estado_cuenta',
        'saldo_contable',
        'diferencia_final',
        'estado',
        'observaciones',
        'fecha_finalizacion',
        'user_id',
        'empresa_id',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
        'fecha_finalizacion' => 'datetime',
        'saldo_estado_cuenta' => 'decimal:2',
        'saldo_contable' => 'decimal:2',
        'diferencia_final' => 'decimal:2',
    ];

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_bancaria_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'en_proceso' => 'warning',
            'finalizada' => 'success',
            'cancelada' => 'danger',
            default => 'secondary'
        };
    }

    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado) {
            'en_proceso' => 'En Proceso',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
            default => 'Desconocido'
        };
    }
}