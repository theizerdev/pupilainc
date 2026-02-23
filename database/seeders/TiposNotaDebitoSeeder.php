<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotaDebito;

class TiposNotaDebitoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['codigo' => '01', 'descripcion' => 'INTERESES POR MORA'],
            ['codigo' => '02', 'descripcion' => 'AUMENTO EN EL VALOR'],
            ['codigo' => '03', 'descripcion' => 'PENALIDADES'],
            ['codigo' => '04', 'descripcion' => 'AJUSTE DE PRECIO'],
            ['codigo' => '05', 'descripcion' => 'GASTOS FINANCIEROS'],
            ['codigo' => '06', 'descripcion' => 'OTROS CONCEPTOS'],
        ];

        foreach ($tipos as $tipo) {
            TipoNotaDebito::firstOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }
    }
}
