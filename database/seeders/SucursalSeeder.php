<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SucursalSeeder extends Seeder
{
    public function run()
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $sucursal = Sucursal::create([
                'empresa_id' => $empresa->id,
                'nombre' => 'Sucursal ' . $empresa->razon_social,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
            ]);

            // Crear Super Administrador
            $superAdmin = User::create([
                'name' => 'Super Admin ' . $empresa->razon_social,
                'email' => 'admin@pupilainc.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'empresa_id' => $empresa->id,
                'sucursal_id' => 1,
                'status' => true,
            ]);

            $superAdmin->assignRole('Super Administrador');

            // Crear Administrador
            $admin = User::create([
                'name' => 'Admin ' . $empresa->razon_social,
                'email' => 'staff@pupilainc.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'empresa_id' => $empresa->id,
                'sucursal_id' => 1,
                'status' => true,
            ]);
            $admin->assignRole('Administrador');
        }
    }
}
