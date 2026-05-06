<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Multitenantable;

class GastoCaja extends Model
{
    use Multitenantable;

    protected $fillable = [
        'caja_id',
        'empresa_id',
        'sucursal_id',
        'user_id',
        'concepto',
        'observaciones',
        'monto',
        'monto_bs',
        'tasa_cambio',
        'metodo_pago',
        'numero_referencia',
        'categoria',
        'estado',
        'fecha_gasto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_bs' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
        'fecha_gasto' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gasto) {
            if (!$gasto->empresa_id) {
                $gasto->empresa_id = auth()->user()->empresa_id ?? 1;
            }
            if (!$gasto->sucursal_id) {
                $gasto->sucursal_id = auth()->user()->sucursal_id ?? 1;
            }
            if (!$gasto->user_id) {
                $gasto->user_id = auth()->id() ?? 1;
            }
            if (!$gasto->fecha_gasto) {
                $gasto->fecha_gasto = now();
            }
            if (!$gasto->tasa_cambio) {
                $gasto->tasa_cambio = ExchangeRate::getTodayRate()?->usd_rate ?? 1;
            }
            // Calcular monto en Bs si no está definido
            if (!$gasto->monto_bs && $gasto->monto) {
                $gasto->monto_bs = $gasto->monto * $gasto->tasa_cambio;
            }
        });

        // Actualizar totales de la caja cuando se crea un gasto
        static::created(function ($gasto) {
            if ($gasto->caja_id && $gasto->estado === 'aprobado') {
                $caja = Caja::find($gasto->caja_id);
                if ($caja) {
                    $caja->actualizarTotalesEgresos();
                }
            }
        });

        // Actualizar totales cuando se cambia el estado
        static::updated(function ($gasto) {
            if ($gasto->caja_id) {
                $caja = Caja::find($gasto->caja_id);
                if ($caja) {
                    $caja->actualizarTotalesEgresos();
                }
            }
        });

        // Actualizar totales cuando se elimina
        static::deleted(function ($gasto) {
            if ($gasto->caja_id) {
                $caja = Caja::find($gasto->caja_id);
                if ($caja) {
                    $caja->actualizarTotalesEgresos();
                }
            }
        });
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_gasto', today());
    }

    public function getMontoFormateadoAttribute(): string
    {
        return '$' . number_format($this->monto, 2);
    }

    public function getMontoBsFormateadoAttribute(): string
    {
        return 'Bs ' . number_format($this->monto_bs, 2);
    }
}
