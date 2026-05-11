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
                'razon_social' => 'Devtechvnzla C.A',
                'direccion'    => 'Av. Principal 123',
                'documento'    => '1234567890',
                'telefono'     => '1234567890',
                'email'        => '1234567890',
                'representante_legal' => 'Theizer Gonzalez',
                'pais_id'      => 20,
            ],
        ];

        foreach ($empresas as $empresaData) {
            $empresa = Empresa::create($empresaData);
            \Artisan::call('whatsapp:sync-company 1');
        }

    }
}
