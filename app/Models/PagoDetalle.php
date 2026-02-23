<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    protected $fillable = [
        'pago_id',
        'concepto_pago_id',
        'baremo_id',
        'payment_schedule_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'aplica_iva',
        'exento_iva',
        'iva_alicuota'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'aplica_iva' => 'boolean',
        'exento_iva' => 'boolean',
        'iva_alicuota' => 'decimal:2'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function conceptoPago()
    {
        return $this->belongsTo(ConceptoPago::class);
    }

    public function baremo()
    {
        return $this->belongsTo(Baremo::class);
    }

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
            
            // Si viene de un baremo, heredar configuración de impuestos
            if ($detalle->baremo_id && !$detalle->isDirty(['aplica_iva', 'exento_iva'])) {
                $baremo = Baremo::find($detalle->baremo_id);
                if ($baremo) {
                    $detalle->aplica_iva = $baremo->aplica_iva;
                    $detalle->exento_iva = $baremo->exento_iva;
                }
            }

            // Set iva_alicuota based on exento status
            if ($detalle->exento_iva) {
                $detalle->iva_alicuota = 0;
            } elseif (!$detalle->isDirty('iva_alicuota') && !$detalle->iva_alicuota) {
                $detalle->iva_alicuota = 16.00;
            }
        });
    }
}
