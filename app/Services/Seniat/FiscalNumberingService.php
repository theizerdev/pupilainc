<?php

namespace App\Services\Seniat;

use App\Models\Serie;
use Illuminate\Support\Facades\DB;

class FiscalNumberingService
{
    public static function generarNumeroControl(int $empresaId, int $sucursalId, string $tipoDocumento = 'factura'): string
    {
        return DB::transaction(function () use ($empresaId, $sucursalId, $tipoDocumento) {
            $serie = Serie::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('tipo_documento', $tipoDocumento)
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            if (!$serie) {
                $serie = Serie::create([
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId,
                    'tipo_documento' => $tipoDocumento,
                    'serie' => '001',
                    'correlativo_actual' => 0,
                    'longitud_correlativo' => 8,
                    'activo' => true,
                ]);

                $serie = Serie::where('id', $serie->id)
                    ->lockForUpdate()
                    ->first();
            }

            return $serie->obtenerSiguienteNumero();
        });
    }
}
