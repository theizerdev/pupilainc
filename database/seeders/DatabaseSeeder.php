<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Nuevo sistema de roles y permisos por sectores
            SectorRolesAndPermissionsSeeder::class,
            PaisSeeder::class, // Agregar países antes que empresas
            EmpresaSeeder::class,
            SucursalSeeder::class,
            ConsultoriosSeeder::class,
            UsersTableSeeder::class,
            SerieSeeder::class,
            CuestionarioSeeder::class,
            EspecialidadesSubespecialidadesSeeder::class, // Agregar especialidades y subespecialidades médicas
        ]);
    }
}
