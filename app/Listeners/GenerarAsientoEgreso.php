<?php

namespace App\Listeners;

use App\Events\GastoCajaCreated;
use App\Services\ContabilidadService;
use Illuminate\Support\Facades\Log;

class GenerarAsientoEgreso
{
    protected $contabilidadService;

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->contabilidadService = $contabilidadService;
    }

    public function handle(GastoCajaCreated $event)
    {
        $gasto = $event->gastoCaja;

        // Solo procesar si el estado es aprobado
        if ($gasto->estado !== 'aprobado') {
            return;
        }

        // Verificar si ya existe un asiento contable para este gasto
        $asientoExistente = \App\Models\AsientoContable::where('referencia_tipo', 'egreso_caja')
            ->where('referencia_id', $gasto->id)
            ->exists();

        if ($asientoExistente) {
            Log::info('Asiento contable ya existe para el egreso', ['gasto_id' => $gasto->id]);
            return;
        }

        try {
            $this->contabilidadService->generarAsientoEgresoCaja($gasto);
        } catch (\Exception $e) {
            Log::error('Error al generar asiento contable de egreso: ' . $e->getMessage(), [
                'gasto_id' => $gasto->id,
                'concepto' => $gasto->concepto
            ]);
        }
    }
}
