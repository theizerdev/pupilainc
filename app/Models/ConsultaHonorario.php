<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaHonorario extends Model
{
    protected $table = 'consulta_honorarios';

    protected $fillable = [
        'consulta_id',
        'pago_id',
        'servicios_detalle',
        'total_facturado_usd',
        'total_facturado_bs',
        'total_honorarios_medico_usd',
        'total_honorarios_medico_bs',
        'total_ingresos_clinica_usd',
        'total_ingresos_clinica_bs',
        'estado',
        'fecha_calculo'
    ];

    protected $casts = [
        'servicios_detalle' => 'array',
        'total_facturado_usd' => 'decimal:2',
        'total_facturado_bs' => 'decimal:2',
        'total_honorarios_medico_usd' => 'decimal:2',
        'total_honorarios_medico_bs' => 'decimal:2',
        'total_ingresos_clinica_usd' => 'decimal:2',
        'total_ingresos_clinica_bs' => 'decimal:2',
        'fecha_calculo' => 'datetime'
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    /**
     * Calcular honorarios basado en los servicios de PagoDetalle
     */
    public static function calcularPorPago(Pago $pago)
    {
        if (!$pago->consulta_id) {
            return null;
        }

        $serviciosDetalle = [];
        $totalHonorariosMedico = 0;
        $totalIngresosClinica = 0;

        // Obtener servicios del pago
        foreach ($pago->detalles as $detalle) {
            $conceptoPago = $detalle->conceptoPago;
            
            // Buscar si el concepto tiene un baremo asociado
            $baremo = Baremo::where('codigo', $conceptoPago->codigo)
                ->orWhere('nombre_servicio', $conceptoPago->nombre)
                ->first();

            if ($baremo && $baremo->porcentaje_medico) {
                $subtotalUsd = $detalle->subtotal / $pago->tasa_cambio_usd;
                $honorarioMedico = $subtotalUsd * ($baremo->porcentaje_medico / 100);
                $ingresoClinica = $subtotalUsd * ($baremo->porcentaje_clinica / 100);

                $serviciosDetalle[] = [
                    'concepto_id' => $conceptoPago->id,
                    'baremo_id' => $baremo->id,
                    'nombre_servicio' => $baremo->nombre_servicio,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal_usd' => $subtotalUsd,
                    'subtotal_bs' => $detalle->subtotal,
                    'porcentaje_medico' => $baremo->porcentaje_medico,
                    'porcentaje_clinica' => $baremo->porcentaje_clinica,
                    'honorario_medico_usd' => $honorarioMedico,
                    'honorario_medico_bs' => $honorarioMedico * $pago->tasa_cambio_usd,
                    'ingreso_clinica_usd' => $ingresoClinica,
                    'ingreso_clinica_bs' => $ingresoClinica * $pago->tasa_cambio_usd
                ];

                $totalHonorariosMedico += $honorarioMedico;
                $totalIngresosClinica += $ingresoClinica;
            }
        }

        if (empty($serviciosDetalle)) {
            return null;
        }

        return self::updateOrCreate([
            'consulta_id' => $pago->consulta_id,
            'pago_id' => $pago->id
        ], [
            'servicios_detalle' => $serviciosDetalle,
            'total_facturado_usd' => $pago->total_usd,
            'total_facturado_bs' => $pago->total_bs,
            'total_honorarios_medico_usd' => $totalHonorariosMedico,
            'total_honorarios_medico_bs' => $totalHonorariosMedico * $pago->tasa_cambio_usd,
            'total_ingresos_clinica_usd' => $totalIngresosClinica,
            'total_ingresos_clinica_bs' => $totalIngresosClinica * $pago->tasa_cambio_usd,
            'estado' => 'calculado',
            'fecha_calculo' => now()
        ]);
    }
}