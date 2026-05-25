<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoVariante;
use App\Models\ValorVariante;
use App\Models\Empresa;
use App\Models\Sucursal;

class TipoVarianteSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        if (!$empresa || !$sucursal) {
            $this->command->warn('No se encontró empresa o sucursal para asignar tipos de variante.');
            return;
        }

        $tipos = [
            [
                'nombre' => 'Color',
                'slug'   => 'color',
                'icono'  => 'ri-palette-line',
                'valores' => [
                    ['valor' => 'Blanco',  'codigo' => 'BL', 'color_hex' => '#FFFFFF'],
                    ['valor' => 'Negro',   'codigo' => 'NE', 'color_hex' => '#000000'],
                    ['valor' => 'Rojo',    'codigo' => 'RO', 'color_hex' => '#EF4444'],
                    ['valor' => 'Azul',    'codigo' => 'AZ', 'color_hex' => '#3B82F6'],
                    ['valor' => 'Verde',   'codigo' => 'VE', 'color_hex' => '#22C55E'],
                    ['valor' => 'Amarillo','codigo' => 'AM', 'color_hex' => '#EAB308'],
                ],
            ],
            [
                'nombre' => 'Talla',
                'slug'   => 'talla',
                'icono'  => 'ri-ruler-line',
                'valores' => [
                    ['valor' => 'Única',   'codigo' => 'UNI'],
                    ['valor' => 'S',       'codigo' => 'S'],
                    ['valor' => 'M',       'codigo' => 'M'],
                    ['valor' => 'L',       'codigo' => 'L'],
                    ['valor' => 'XL',      'codigo' => 'XL'],
                ],
            ],
            [
                'nombre' => 'Sabor',
                'slug'   => 'sabor',
                'icono'  => 'ri-cup-line',
                'valores' => [
                    ['valor' => 'Fresa',    'codigo' => 'FR'],
                    ['valor' => 'Vainilla', 'codigo' => 'VA'],
                    ['valor' => 'Chocolate','codigo' => 'CH'],
                    ['valor' => 'Menta',    'codigo' => 'ME'],
                    ['valor' => 'Naranja',  'codigo' => 'NA'],
                    ['valor' => 'Limón',    'codigo' => 'LI'],
                ],
            ],
            [
                'nombre' => 'Presentación',
                'slug'   => 'presentacion',
                'icono'  => 'ri-medicine-bottle-line',
                'valores' => [
                    ['valor' => 'Tableta',   'codigo' => 'TAB'],
                    ['valor' => 'Cápsula',   'codigo' => 'CAP'],
                    ['valor' => 'Jarabe',    'codigo' => 'JAR'],
                    ['valor' => 'Crema',     'codigo' => 'CRE'],
                    ['valor' => 'Ampolla',   'codigo' => 'AMP'],
                    ['valor' => 'Inyección', 'codigo' => 'INY'],
                    ['valor' => 'Solución',  'codigo' => 'SOL'],
                    ['valor' => 'Gotas',     'codigo' => 'GOT'],
                ],
            ],
            [
                'nombre' => 'Dosis',
                'slug'   => 'dosis',
                'icono'  => 'ri-scales-2-line',
                'valores' => [
                    ['valor' => '250 mg',  'codigo' => '250MG'],
                    ['valor' => '500 mg',  'codigo' => '500MG'],
                    ['valor' => '1000 mg', 'codigo' => '1G'],
                    ['valor' => '5 ml',    'codigo' => '5ML'],
                    ['valor' => '10 ml',   'codigo' => '10ML'],
                    ['valor' => '20 ml',   'codigo' => '20ML'],
                ],
            ],
            [
                'nombre' => 'Tamaño',
                'slug'   => 'tamano',
                'icono'  => 'ri-resize-line',
                'valores' => [
                    ['valor' => 'Pequeño', 'codigo' => 'S'],
                    ['valor' => 'Mediano', 'codigo' => 'M'],
                    ['valor' => 'Grande',  'codigo' => 'L'],
                ],
            ],
            [
                'nombre' => 'Material',
                'slug'   => 'material',
                'icono'  => 'ri-box-3-line',
                'valores' => [
                    ['valor' => 'Plástico',  'codigo' => 'PLA'],
                    ['valor' => 'Vidrio',    'codigo' => 'VID'],
                    ['valor' => 'Aluminio',  'codigo' => 'ALU'],
                    ['valor' => 'Cartón',    'codigo' => 'CAR'],
                ],
            ],
        ];

        foreach ($tipos as $ordenTipo => $tipoData) {
            $valores = $tipoData['valores'];
            unset($tipoData['valores']);

            $tipo = TipoVariante::firstOrCreate(
                [
                    'slug'       => $tipoData['slug'],
                    'empresa_id' => $empresa->id,
                ],
                array_merge($tipoData, [
                    'status'      => true,
                    'orden'       => $ordenTipo,
                    'empresa_id'  => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                ])
            );

            foreach ($valores as $ordenVal => $valData) {
                ValorVariante::firstOrCreate(
                    [
                        'tipo_variante_id' => $tipo->id,
                        'valor'            => $valData['valor'],
                        'empresa_id'       => $empresa->id,
                    ],
                    array_merge($valData, [
                        'status'       => true,
                        'orden'        => $ordenVal,
                        'empresa_id'   => $empresa->id,
                        'sucursal_id'  => $sucursal->id,
                    ])
                );
            }
        }

        $this->command->info('Tipos y valores de variantes sembrados correctamente.');
    }
}
