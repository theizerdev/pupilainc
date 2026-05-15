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
                'razon_social' => 'Pupila Inc.',
                'direccion'    => 'Ciudad de México, México',
                'documento'    => 'PUP123456789',
                'telefono'     => '5512345678',
                'email'        => 'contacto@pupilainc.com',
                'representante_legal' => 'Director Pupila Inc.',
                'pais_id'      => 12,
            ],
        ];

        foreach ($empresas as $empresaData) {
            $empresa = Empresa::create($empresaData);
            \Artisan::call('whatsapp:sync-company 1');
        }

    }
}
