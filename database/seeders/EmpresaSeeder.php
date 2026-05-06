<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmpresaSeeder extends Seeder
{
    public function run()
    {
        $empresas = [
            [
                'razon_social' => 'Pupila INC',
                'direccion'    => 'Calle Baluarte 469, frente a Mercado San Juan, Col. Santa Selena, Lagos de Moreno, Jalisco',
                'documento'    => '1234567890',
                'telefono'     => '3318039390',
                'email'        => 'contacto@pupila.com',
                'representante_legal' => 'Victor Medina',
                'latitud' => '21.3526',
                'longitud' => '-101.9286',
                'pais_id'      => 20,
            ],
        ];

        foreach ($empresas as $empresaData) {
            $empresa = Empresa::create($empresaData);
            \Artisan::call('whatsapp:sync-company 1');
        }

    }
}
