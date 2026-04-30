<?php

namespace App\Listeners;

use App\Events\PagoCreated;
use App\Services\ContabilidadService;
use App\Models\Pago;
use App\Models\ConsultaHonorario;

class GenerarAsientoContable
{
    protected $contabilidadService;

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->contabilidadService = $contabilidadService;
    }

    public function handle(PagoCreated $event)
    {
        $pago = $event->pago;

        // Calcular honorarios si es una consulta
        if ($pago->consulta_id && $pago->estado === Pago::ESTADO_APROBADO) {
            try {
                ConsultaHonorario::calcularPorPago($pago);
            } catch (\Exception $e) {
                \Log::error('Error al calcular honorarios: ' . $e->getMessage(), [
                    'pago_id' => $pago->id,
                    'consulta_id' => $pago->consulta_id
                ]);
            }
        }

        // Verificar si ya existe un asiento contable para este pago
        $asientoExistente = \App\Models\AsientoContable::where('referencia_tipo', $this->getReferenciaTipo($pago->tipo_pago))
            ->where('referencia_id', $pago->id)
            ->exists();

        if ($asientoExistente) {
            \Log::info('Asiento contable ya existe para el pago', ['pago_id' => $pago->id]);
            return;
        }

        try {
            switch ($pago->tipo_pago) {
                case 'factura':
                case 'boleta':
                case 'recibo':
                    $this->contabilidadService->generarAsientoFactura($pago);
                    break;

                case Pago::TIPO_NOTA_CREDITO:
                    $this->contabilidadService->generarAsientoNotaCredito($pago);
                    break;

                case Pago::TIPO_NOTA_DEBITO:
                    $this->contabilidadService->generarAsientoNotaDebito($pago);
                    break;

                default:
                    // Otros tipos de pago (efectivo, transferencia, etc.)
                    if ($pago->metodo_pago) {
                        $this->contabilidadService->generarAsientoPago($pago);
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Error al generar asiento contable: ' . $e->getMessage(), [
                'pago_id' => $pago->id,
                'tipo_pago' => $pago->tipo_pago
            ]);
        }
    }

    private function getReferenciaTipo($tipoPago)
    {
        $map = [
            'factura' => 'factura',
            'boleta' => 'factura',
            'recibo' => 'factura',
            Pago::TIPO_NOTA_CREDITO => 'nota_credito',
            Pago::TIPO_NOTA_DEBITO => 'nota_debito',
        ];

        return $map[$tipoPago] ?? 'pago';
    }
}
