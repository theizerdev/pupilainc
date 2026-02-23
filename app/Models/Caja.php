<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Multitenantable;

class Caja extends Model
{
    use HasFactory,Multitenantable;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'user_id',
        'fecha',
        'numero_corte',
        'monto_inicial',
        'total_efectivo',
        'total_transferencias',
        'total_tarjetas',
        'total_ingresos',
        'monto_final',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'observaciones_apertura',
        'observaciones_cierre',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'total_efectivo' => 'decimal:2',
        'total_transferencias' => 'decimal:2',
        'total_tarjetas' => 'decimal:2',
        'total_ingresos' => 'decimal:2',
        'monto_final' => 'decimal:2',
    ];

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

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function calcularTotales(): void
    {
        $pagos = $this->pagos()->where('estado', 'aprobado')->get();

        $totalEfectivo = 0;
        $totalTransferencias = 0;
        $totalTarjetas = 0;

        foreach ($pagos as $pago) {
            // Excluir pagos anulados por nota de crédito
            if ($pago->notasCredito()->where('estado', 'aprobado')->exists()) {
                continue;
            }

            $montoUSD = $pago->total_usd ?? $pago->total;
            
            if ($pago->es_pago_mixto && $pago->detalles_pago_mixto) {
                // Procesar pago mixto
                foreach ($pago->detalles_pago_mixto as $detalle) {
                    $monto = $detalle['monto_usd'] ?? $detalle['monto'] ?? 0;
                    
                    if (in_array($detalle['metodo'], ['efectivo_bs', 'efectivo_usd'])) {
                        $totalEfectivo += $monto;
                    } elseif (in_array($detalle['metodo'], ['transferencia_bs', 'transferencia_usd', 'pago_movil', 'zelle', 'paypal'])) {
                        $totalTransferencias += $monto;
                    } elseif (in_array($detalle['metodo'], ['tarjeta', 'tarjeta_debito', 'tarjeta_credito'])) {
                        $totalTarjetas += $monto;
                    }
                }
            } else {
                // Procesar pago tradicional
                if (in_array($pago->metodo_pago, ['efectivo_bs', 'efectivo_usd', 'efectivo'])) {
                    $totalEfectivo += $montoUSD;
                } elseif (in_array($pago->metodo_pago, ['transferencia_bs', 'transferencia_usd', 'transferencia', 'pago_movil', 'zelle', 'paypal'])) {
                    $totalTransferencias += $montoUSD;
                } elseif (in_array($pago->metodo_pago, ['tarjeta', 'tarjeta_debito', 'tarjeta_credito'])) {
                    $totalTarjetas += $montoUSD;
                }
            }
        }

        $this->total_efectivo = $totalEfectivo;
        $this->total_transferencias = $totalTransferencias;
        $this->total_tarjetas = $totalTarjetas;
        $this->total_ingresos = $totalEfectivo + $totalTransferencias + $totalTarjetas;
        $this->monto_final = $this->monto_inicial + $this->total_ingresos;

        $this->save();
    }

    public function cerrar(string $observaciones = null): bool
    {
        if ($this->estado === 'cerrada') {
            return false;
        }

        $this->calcularTotales();
        $this->estado = 'cerrada';
        $this->fecha_cierre = now();
        $this->observaciones_cierre = $observaciones;

        return $this->save();
    }

    public static function obtenerCajaAbierta($empresaId, $sucursalId, $fecha = null): ?self
    {
        $fecha = $fecha ?? now()->toDateString();

        return self::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('fecha', $fecha)
            ->where('estado', 'abierta')
            ->first();
    }

    public static function crearCajaDiaria($empresaId, $sucursalId, $montoInicial = 0, $observaciones = null, $userId = null): self
    {
        return self::create([
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'user_id' => $userId ?? auth()->id() ?? 1,
            'fecha' => now()->toDateString(),
            'numero_corte' => 1,
            'monto_inicial' => $montoInicial,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
            'observaciones_apertura' => $observaciones,
        ]);
    }

    public static function crearCorte($empresaId, $sucursalId, $montoInicial = 0, $observaciones = null, $userId = null): self
    {
        $numeroCorte = self::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->whereDate('fecha', today())
            ->count() + 1;

        return self::create([
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'user_id' => $userId ?? auth()->id() ?? 1,
            'fecha' => now()->toDateString(),
            'numero_corte' => $numeroCorte,
            'monto_inicial' => $montoInicial,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
            'observaciones_apertura' => ($observaciones ?? '') . " (Corte #{$numeroCorte})",
        ]);
    }
}
