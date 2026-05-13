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
            \Log::info('Observer GastoCaja created ejecutado', [
                'gasto_id' => $gasto->id,
                'caja_id' => $gasto->caja_id,
                'estado' => $gasto->estado,
                'monto' => $gasto->monto
            ]);

            if ($gasto->caja_id && $gasto->estado === 'aprobado') {
                $caja = Caja::find($gasto->caja_id);
                if ($caja) {
                    \Log::info('Actualizando totales de caja', [
                        'caja_id' => $caja->id,
                        'total_egresos_antes' => $caja->total_egresos
                    ]);

                    $caja->actualizarTotalesEgresos();

                    $caja->refresh();
                    \Log::info('Totales actualizados', [
                        'caja_id' => $caja->id,
                        'total_egresos_despues' => $caja->total_egresos,
                        'monto_final_ajustado' => $caja->monto_final_ajustado
                    ]);
                } else {
                    \Log::warning('No se encontró la caja', ['caja_id' => $gasto->caja_id]);
                }
            } else {
                \Log::warning('Condiciones no cumplidas para actualizar totales', [
                    'caja_id' => $gasto->caja_id,
                    'estado' => $gasto->estado
                ]);
            }

            // Disparar evento para generar asiento contable
            event(new \App\Events\GastoCajaCreated($gasto));
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
