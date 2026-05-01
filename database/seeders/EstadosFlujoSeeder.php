<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoFlujo;
use App\Models\Empresa;

class EstadosFlujoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        $estados = [
            ['codigo' => 'por_llegar',                    'nombre' => 'Por Llegar',                    'color' => '#9E9E9E'],
            ['codigo' => 'sala_espera',                   'nombre' => 'Sala de Espera',               'color' => '#FFA726'],
            ['codigo' => 'en_enfermeria',                 'nombre' => 'En Enfermería',                'color' => '#EF5350'],
            ['codigo' => 'en_consultorio',                'nombre' => 'En Consultorio',               'color' => '#42A5F5'],
            ['codigo' => 'en_consultorio_optometrista',   'nombre' => 'En Consultorio Optometrista',  'color' => '#7E57C2'],
            ['codigo' => 'en_gotas',                      'nombre' => 'En Gotas',                     'color' => '#26C6DA'],
            ['codigo' => 'dilatado',                      'nombre' => 'Dilatado',                     'color' => '#00BCD4'],
            ['codigo' => 'en_optica',                     'nombre' => 'En Óptica',                    'color' => '#AB47BC'],
            ['codigo' => 'en_estudio',                    'nombre' => 'En Estudio',                   'color' => '#EC407A'],
            ['codigo' => 'finalizada',                    'nombre' => 'Finalizada',                   'color' => '#66BB6A'],
            ['codigo' => 'pagada',                        'nombre' => 'Pagada',                       'color' => '#4CAF50'],
        ];

        foreach ($empresas as $empresa) {
            foreach ($estados as $estado) {
                EstadoFlujo::firstOrCreate(
                    ['codigo' => $estado['codigo'], 'empresa_id' => $empresa->id],
                    array_merge($estado, ['activo' => true, 'empresa_id' => $empresa->id])
                );
            }
        }
    }
}
