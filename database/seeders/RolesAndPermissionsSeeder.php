<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir módulos y sus permisos
        $modules = [
            'empresas' => [
                'access empresas',
                'create empresas',
                'edit empresas',
                'delete empresas',
            ],
            'paises' => [
                'access paises',
                'create paises',
                'edit paises',
                'delete paises',
            ],
            'sucursales' => [
                'access sucursales',
                'create sucursales',
                'edit sucursales',
                'delete sucursales',
            ],
            'users' => [
                'access users',
                'create users',
                'edit users',
                'delete users',
            ],
            'roles' => [
                'access roles',
                'create roles',
                'edit roles',
                'delete roles',
                'assign roles',
            ],
            'permissions' => [
                'access permissions',
                'create permissions',
                'edit permissions',
                'delete permissions',
                'assign permissions',
            ],
            'active_sessions' => [
                'view active sessions',
                'delete active sessions',
            ],
            'dashboard' => [
                'access dashboard',
                'dashboard.alerts',
                'dashboard.financial',
                'dashboard.academic',
                'dashboard.access',
                'dashboard.charts',
            ],
            'monitoreo' => [
                'access monitoreo',
                'view monitoreo servidor',
                'view monitoreo base-datos',
                'view monitoreo estudiantes',
                'view monitoreo accesos',
                'export monitoreo accesos',
            ],
            // Módulo de conceptos de pago
            'conceptos_pago' => [
                'access conceptos pago',
                'create conceptos pago',
                'edit conceptos pago',
                'delete conceptos pago',
                'view conceptos pago',
            ],
            // Módulo de actividad
            'activity_log' => [
                'access activity log',
                'view activity log',
                'delete activity log',
                'export activity log',
            ],
            // Módulo de exportación de base de datos
            'database_export' => [
                'access database export',
                'export database',
            ],
            // Módulo de series de documentos
            'series' => [
                'access series',
                'create series',
                'edit series',
                'delete series',
            ],
            // Módulo de cajas
            'cajas' => [
                'access cajas',
                'create cajas',
                'edit cajas',
                'delete cajas',
                'view cajas',
            ],
            // Módulo de tasas de cambio
            'exchange_rates' => [
                'view exchange-rates',
                'fetch exchange-rates',
                'edit exchange-rates',
                'manage exchange-rates',
            ],
            // Módulo de WhatsApp
            'whatsapp' => [
                'access whatsapp',
                'create whatsapp templates',
                'edit whatsapp templates',
                'delete whatsapp templates',
                'send whatsapp messages',
                'schedule whatsapp messages',
                'view whatsapp statistics',
                'export whatsapp reports',
                'retry failed whatsapp messages',
                'view whatsapp retry statistics',
                'manage whatsapp auto retry',
            ],

           'especialidades' => [
                'access especialidades',
                'create especialidades',
                'edit especialidades',
                'delete especialidades',
            ],

            'tipo-consultas' => [
                'access tipo-consultas',
                'create tipo-consultas',
                'edit tipo-consultas',
                'delete tipo-consultas',
            ],

            'subespecialidades' => [
            'access subespecialidades',
            'create subespecialidades',
            'edit subespecialidades',
            'delete subespecialidades',
            ],
            // Módulo de médicos
            'medicos' => [
                'access medicos',
                'create medicos',
                'edit medicos',
                'delete medicos',
                'view medicos',
                'activate medicos',
                'deactivate medicos',
                'assign especialidades',
                'assign subespecialidades',
                'assign tipo-consultas',
                'view medicos schedule',
                'edit medicos schedule',
            ],

            // Módulo de citas
       
            'citas' => [
                'access citas',
                'create citas',
                'edit citas',
                'delete citas',
            ],
          
        ];

        // Crear permisos organizados por módulos
        foreach ($modules as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission],
                    ['module' => $module]
                );
            }
        }

        // Crear roles y asignar permisos
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Administrador']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $doc = Role::firstOrCreate(['name' => 'Médico']);
        $enf = Role::firstOrCreate(['name' => 'Enfermeria']);
        $sec = Role::firstOrCreate(['name' => 'Recepcion']);

        // Asignar todos los permisos al Super Administrador
        $superAdminRole->syncPermissions(Permission::all());

        // Asignar permisos al Administrador (todos menos los de super admin)
        $adminPermissions = Permission::whereNotIn('name', [
            'assign roles',
            'assign permissions'
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Asignar permisos al rol de Médico
        $medicoPermissions = Permission::whereIn('name', [
            'access dashboard',
            'dashboard.alerts',
            'access especialidades',
            'access subespecialidades',
            'access tipo-consultas',
            'access medicos',
            'view medicos',
            'view medicos schedule',
            'access citas',
            'create citas',
            'edit citas',
            'delete citas',
            'access activity log',
            'view activity log',
        ])->get();
        $doc->syncPermissions($medicoPermissions);

        // Asignar permisos al rol de Enfermería
        $enfermeriaPermissions = Permission::whereIn('name', [
            'access dashboard',
            'dashboard.alerts',
            'access especialidades',
            'access subespecialidades',
            'access tipo-consultas',
            'access medicos',
            'view medicos',
            'access citas',
            'create citas',
            'edit citas',
            'access activity log',
            'view activity log',
            'access tipo consultas',
            'create tipo consultas',
            'edit tipo consultas',
            'delete tipo consultas',
        ])->get();
        $enf->syncPermissions($enfermeriaPermissions);

        // Asignar permisos al rol de Recepción
        $recepcionPermissions = Permission::whereIn('name', [
            'access dashboard',
            'dashboard.alerts',
            'access especialidades',
            'access subespecialidades',
            'access tipo-consultas',
            'access medicos',
            'view medicos',
            'access citas',
            'create citas',
            'edit citas',
            'access activity log',
            'view activity log',
            'access conceptos pago',
            'view conceptos pago',
            'access series',
            'access cajas',
            'view cajas',
            'access tipo consultas',
            'create tipo consultas',
            'edit tipo consultas',
            'delete tipo consultas',
        ])->get();
        $sec->syncPermissions($recepcionPermissions);

    }
}