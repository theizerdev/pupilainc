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
            ExchangeRateConfigSeeder::class, // Configuración de tasas de cambio por país
            EmpresaSeeder::class,
            SucursalSeeder::class,
            ConsultoriosSeeder::class,
            // UsersTableSeeder::class,
            SerieSeeder::class,
            CuestionarioSeeder::class,
            EspecialidadesSubespecialidadesSeeder::class, // Agregar especialidades y subespecialidades médicas
            TipoConsultaSeeder::class, // Tipos de consultas médicas
            CategoriaSeeder::class, // Categorías para clasificación de elementos
            CategoriaProductoSeeder::class, // Categorías de producto para inventario
            MarcaSeeder::class, // Marcas para inventario
            //InventarioBaseSeeder::class, // Almacenes y proveedores base
            TipoVarianteSeeder::class, // Tipos y valores de variantes para productos
            PlantillasClinicasSeeder::class, // Especialidades y plantillas clínicas dinámicas
            EstadoFormulariosSeeder::class,
            PlanCuentasSeeder::class,
            CuestionarioOftalmologiaSeeder::class,
            CuestionarioSubsecuenteOftalmologiaSeeder::class,
            //PacienteSeeder::class, // 20 pacientes de ejemplo (10 femeninos, 10 masculinos)
            //BaremoSeeder::class, // Servicios médicos por especialidad
            //ProductosSeeder::class, // Productos e insumos médicos
            //MedicosSeeder::class, // Médicos y sus usuarios

        ]);
    }
}
