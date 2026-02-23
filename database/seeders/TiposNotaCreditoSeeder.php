<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotaCredito;

class TiposNotaCreditoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['codigo' => '01', 'descripcion' => 'ANULACIÓN DE LA OPERACIÓN'],
            ['codigo' => '02', 'descripcion' => 'ANULACIÓN POR ERROR EN EL RIF'],
            ['codigo' => '03', 'descripcion' => 'CORRECCIÓN POR ERROR EN LA DESCRIPCIÓN'],
            ['codigo' => '04', 'descripcion' => 'DESCUENTO GLOBAL'],
            ['codigo' => '05', 'descripcion' => 'DESCUENTO POR ÍTEM'],
            ['codigo' => '06', 'descripcion' => 'DEVOLUCIÓN TOTAL'],
            ['codigo' => '07', 'descripcion' => 'DEVOLUCIÓN POR ÍTEM'],
            ['codigo' => '08', 'descripcion' => 'BONIFICACIÓN'],
            ['codigo' => '09', 'descripcion' => 'DISMINUCIÓN EN EL VALOR'],
            ['codigo' => '10', 'descripcion' => 'OTROS CONCEPTOS'],
            ['codigo' => '11', 'descripcion' => 'AJUSTES DE OPERACIONES DE EXPORTACIÓN'],
            ['codigo' => '12', 'descripcion' => 'AJUSTES AFECTOS AL IVAP'],
        ];

        foreach ($tipos as $tipo) {
            TipoNotaCredito::firstOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }
    }
}
