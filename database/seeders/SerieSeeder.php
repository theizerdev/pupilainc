<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Serie;
use App\Models\Empresa;
use App\Models\Sucursal;

class SerieSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::with('sucursales')->get();

        foreach ($empresas as $empresa) {
            foreach ($empresa->sucursales as $sucursal) {
                $series = [
                    ['tipo_documento' => 'factura fiscal', 'serie' => 'F001'],
                    ['tipo_documento' => 'boleta', 'serie' => 'B001'],
                    ['tipo_documento' => 'nota_credito', 'serie' => 'NC01'],
                    ['tipo_documento' => 'nota_debito', 'serie' => 'ND01'],
                    ['tipo_documento' => 'recibo', 'serie' => 'R001'],
                ];

                foreach ($series as $serie) {
                    Serie::firstOrCreate(
                        [
                            'serie' => $serie['serie'],
                            'empresa_id' => $empresa->id,
                            'sucursal_id' => $sucursal->id
                        ],
                        [
                            'tipo_documento' => $serie['tipo_documento'],
                            'correlativo_actual' => 0,
                            'longitud_correlativo' => 8,
                            'activo' => true
                        ]
                    );
                }
            }
        }
    }
}
