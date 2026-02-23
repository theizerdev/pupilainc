<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotaDebito;

class TipoNotaDebitoSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            ['codigo' => '01', 'descripcion' => 'Intereses moratorios', 'activo' => true],
            ['codigo' => '02', 'descripcion' => 'Aumento en el valor de la operación', 'activo' => true],
            ['codigo' => '03', 'descripcion' => 'Anulación de Nota de Crédito', 'activo' => true],
            ['codigo' => '04', 'descripcion' => 'Ajuste de precio', 'activo' => true],
        ];

        foreach ($tipos as $tipo) {
            TipoNotaDebito::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
