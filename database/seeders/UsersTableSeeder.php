<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
          $superUser =  User::create([
                    'name' => 'Super Administrador Pupila',
                    'username' => 'superadmin',
                    'email' => 'admin@pupilainc.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
        ]);


        $superUser->assignRole('Super Administrador');

         $adminUser =  User::create([
                    'name' => 'Admin Pupila',
                    'username' => 'admin',
                    'email' => 'staff@pupilainc.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
        ]);


             $adminUser->assignRole('Administrador');
    }
}
