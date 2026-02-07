<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SectorRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir sectores y sus permisos organizados por categorías
        $sectors = [
            // 🏥 SECTOR MÉDICO
            'medico' => [
                'tipo_consultas' => [
                    'name' => 'Tipo de Consultas',
                    'permissions' => [
                        'access tipo-consultas',
                        'create tipo-consultas',
                        'edit tipo-consultas',
                        'delete tipo-consultas',
                        'assign tipo-consultas',
                    ]
                ],
                'pacientes' => [
                    'name' => 'Pacientes',
                    'permissions' => [
                        'access pacientes',
                        'create pacientes',
                        'edit pacientes',
                        'delete pacientes',
                        'view pacientes',
                        'export pacientes',
                    ]
                ],
                'medicos' => [
                    'name' => 'Médicos',
                    'permissions' => [
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
                    ]
                ],
                'citas' => [
                    'name' => 'Citas',
                    'permissions' => [
                        'access citas',
                        'create citas',
                        'edit citas',
                        'delete citas',
                        'confirm citas',
                        'cancel citas',
                        'complete citas',
                        'reschedule citas',
                        'view citas analytics',
                        'access citas recordatorios',
                        'send citas recordatorios',
                    ]
                ],
                'especialidades' => [
                    'name' => 'Especialidades',
                    'permissions' => [
                        'access especialidades',
                        'create especialidades',
                        'edit especialidades',
                        'delete especialidades',
                        'assign especialidades',
                    ]
                ],
                'subespecialidades' => [
                    'name' => 'Subespecialidades',
                    'permissions' => [
                        'access subespecialidades',
                        'create subespecialidades',
                        'edit subespecialidades',
                        'delete subespecialidades',
                        'assign subespecialidades',
                    ]
                ],
            ],

            // 💰 SECTOR ADMINISTRACIÓN
            'administracion' => [
                'cajas' => [
                    'name' => 'Cajas',
                    'permissions' => [
                        'access cajas',
                        'create cajas',
                        'edit cajas',
                        'delete cajas',
                        'view cajas',
                        'open cajas',
                        'close cajas',
                        'manage cajas',
                        'export cajas',
                    ]
                ],
                'pagos' => [
                    'name' => 'Pagos',
                    'permissions' => [
                        'access pagos',
                        'create pagos',
                        'edit pagos',
                        'delete pagos',
                        'view pagos',
                        'process pagos',
                        'cancel pagos',
                        'refund pagos',
                        'export pagos',
                        'access pagos comprobantes',
                        'generate pagos comprobantes',
                    ]
                ],
                'conceptos_pago' => [
                    'name' => 'Conceptos de Pago',
                    'permissions' => [
                        'access conceptos pago',
                        'create conceptos pago',
                        'edit conceptos pago',
                        'delete conceptos pago',
                        'view conceptos pago',
                        'activate conceptos pago',
                        'deactivate conceptos pago',
                    ]
                ],
                'tasas_cambio' => [
                    'name' => 'Tasas de Cambio (BCV)',
                    'permissions' => [
                        'view exchange-rates',
                        'fetch exchange-rates',
                        'edit exchange-rates',
                        'manage exchange-rates',
                        'update exchange-rates',
                        'export exchange-rates',
                    ]
                ],
                'series' => [
                    'name' => 'Series de Documentos',
                    'permissions' => [
                        'access series',
                        'create series',
                        'edit series',
                        'delete series',
                        'assign series',
                        'manage series',
                    ]
                ],
                'reglas_mora' => [
                    'name' => 'Reglas de Mora',
                    'permissions' => [
                        'access reglas mora',
                        'create reglas mora',
                        'edit reglas mora',
                        'delete reglas mora',
                        'apply reglas mora',
                    ]
                ],
            ],

            // ⚙️ SECTOR CONFIGURACIÓN
            'configuracion' => [
                'empresas' => [
                    'name' => 'Empresas',
                    'permissions' => [
                        'access empresas',
                        'create empresas',
                        'edit empresas',
                        'delete empresas',
                        'view empresas',
                        'activate empresas',
                        'deactivate empresas',
                        'assign empresas',
                        'configure empresas',
                        'export empresas',
                    ]
                ],
                'sucursales' => [
                    'name' => 'Sucursales',
                    'permissions' => [
                        'access sucursales',
                        'create sucursales',
                        'edit sucursales',
                        'delete sucursales',
                        'view sucursales',
                        'assign sucursales',
                        'configure sucursales',
                    ]
                ],
                'paises' => [
                    'name' => 'Países',
                    'permissions' => [
                        'access paises',
                        'create paises',
                        'edit paises',
                        'delete paises',
                        'view paises',
                        'activate paises',
                        'configure paises',
                    ]
                ],
                'usuarios' => [
                    'name' => 'Usuarios',
                    'permissions' => [
                        'access users',
                        'create users',
                        'edit users',
                        'delete users',
                        'view users',
                        'activate users',
                        'deactivate users',
                        'reset users password',
                        'manage users profile',
                        'assign users roles',
                        'export users',
                        'view users history',
                    ]
                ],
                'roles' => [
                    'name' => 'Roles',
                    'permissions' => [
                        'access roles',
                        'create roles',
                        'edit roles',
                        'delete roles',
                        'view roles',
                        'assign roles',
                        'manage roles',
                    ]
                ],
                'permisos' => [
                    'name' => 'Permisos',
                    'permissions' => [
                        'access permissions',
                        'create permissions',
                        'edit permissions',
                        'delete permissions',
                        'view permissions',
                        'assign permissions',
                        'manage permissions',
                    ]
                ],
                'personalizacion' => [
                    'name' => 'Personalización',
                    'permissions' => [
                        'access template customization',
                        'edit template customization',
                        'configure template customization',
                        'manage template customization',
                    ]
                ],
            ],

            // 📊 SECTOR MONITOREO
            'monitoreo' => [
                'sesiones' => [
                    'name' => 'Sesiones Activas',
                    'permissions' => [
                        'view active sessions',
                        'delete active sessions',
                        'monitor active sessions',
                        'export active sessions',
                        'manage active sessions',
                    ]
                ],
                'actividad' => [
                    'name' => 'Actividad del Sistema',
                    'permissions' => [
                        'access activity log',
                        'view activity log',
                        'delete activity log',
                        'export activity log',
                        'filter activity log',
                        'monitor activity log',
                    ]
                ],
                'respaldo' => [
                    'name' => 'Respaldo de Base de Datos',
                    'permissions' => [
                        'access database export',
                        'export database',
                        'schedule database export',
                        'download database export',
                        'manage database export',
                    ]
                ],
                'monitoreo_sistema' => [
                    'name' => 'Monitoreo del Sistema',
                    'permissions' => [
                        'access monitoreo',
                        'view monitoreo servidor',
                        'view monitoreo base-datos',
                        'view monitoreo estudiantes',
                        'view monitoreo accesos',
                        'export monitoreo accesos',
                        'view system status',
                        'view system performance',
                    ]
                ],
                'notificaciones' => [
                    'name' => 'Notificaciones',
                    'permissions' => [
                        'access notifications',
                        'view notifications',
                        'create notifications',
                        'send notifications',
                        'schedule notifications',
                        'manage notifications',
                    ]
                ],
                'whatsapp_monitoreo' => [
                    'name' => 'WhatsApp (Monitoreo)',
                    'permissions' => [
                        'view whatsapp statistics',
                        'export whatsapp reports',
                        'view whatsapp retry statistics',
                        'monitor whatsapp status',
                    ]
                ],
            ],

            // 📱 SECTOR COMUNICACIONES (Adicional)
            'comunicaciones' => [
                'whatsapp' => [
                    'name' => 'WhatsApp',
                    'permissions' => [
                        'access whatsapp',
                        'create whatsapp templates',
                        'edit whatsapp templates',
                        'delete whatsapp templates',
                        'send whatsapp messages',
                        'schedule whatsapp messages',
                        'retry failed whatsapp messages',
                        'manage whatsapp auto retry',
                        'configure whatsapp',
                    ]
                ],
            ],
        ];

        // Crear permisos organizados por sectores y módulos
        foreach ($sectors as $sector => $modules) {
            foreach ($modules as $module => $moduleData) {
                foreach ($moduleData['permissions'] as $permission) {
                    Permission::firstOrCreate(
                        ['name' => $permission],
                        [
                            'module' => $module,
                            'sector' => $sector
                        ]
                    );
                }
            }
        }

        // Crear roles base con la nueva estructura
        $this->createRoles();
    }

    /**
     * Crear roles y asignar permisos por sectores
     */
    private function createRoles(): void
    {
        // Rol Super Administrador - Acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador']);
        $superAdmin->syncPermissions(Permission::all());

        // Rol Administrador - Todos los sectores excepto asignación de roles/permisos
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $adminPermissions = Permission::whereNotIn('name', [
            'assign roles',
            'assign permissions',
            'manage permissions',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Rol Director Médico - Sector Médico completo + Monitoreo
        $directorMedico = Role::firstOrCreate(['name' => 'Director Médico']);
        $directorMedicoPermissions = Permission::whereIn('sector', ['medico', 'monitoreo'])
            ->orWhereIn('name', [
                'access dashboard',
                'dashboard.alerts',
                'view activity log',
            ])->get();
        $directorMedico->syncPermissions($directorMedicoPermissions);

        // Rol Médico - Solo sector médico
        $medico = Role::firstOrCreate(['name' => 'Médico']);
        $medicoPermissions = Permission::where('sector', 'medico')
            ->whereNotIn('name', [
                'delete medicos',
                'delete tipo-consultas',
                'delete especialidades',
                'delete subespecialidades',
            ])->get();
        $medico->syncPermissions($medicoPermissions);

        // Rol Enfermería - Sector médico limitado
        $enfermeria = Role::firstOrCreate(['name' => 'Enfermería']);
        $enfermeriaPermissions = Permission::where('sector', 'medico')
            ->whereIn('name', [
                'access tipo-consultas',
                'create tipo-consultas',
                'edit tipo-consultas',
                'access pacientes',
                'create pacientes',
                'edit pacientes',
                'access medicos',
                'view medicos',
                'access citas',
                'create citas',
                'edit citas',
                'confirm citas',
                'cancel citas',
            ])->get();
        $enfermeria->syncPermissions($enfermeriaPermissions);

        // Rol Recepción - Sector médico + administración limitada
        $recepcion = Role::firstOrCreate(['name' => 'Recepción']);
        $recepcionPermissions = Permission::whereIn('sector', ['medico', 'administracion'])
            ->whereIn('name', [
                // Médico limitado
                'access tipo-consultas',
                'create tipo-consultas',
                'edit tipo-consultas',
                'access pacientes',
                'create pacientes',
                'edit pacientes',
                'access medicos',
                'view medicos',
                'access citas',
                'create citas',
                'edit citas',
                'confirm citas',
                'cancel citas',
                // Administración limitada
                'access conceptos pago',
                'view conceptos pago',
                'access series',
                'access cajas',
                'view cajas',
                'access pagos',
                'create pagos',
                'view pagos',
            ])->get();
        $recepcion->syncPermissions($recepcionPermissions);

        // Rol Cajero - Solo sector administración
        $cajero = Role::firstOrCreate(['name' => 'Cajero']);
        $cajeroPermissions = Permission::where('sector', 'administracion')
            ->whereIn('name', [
                'access cajas',
                'view cajas',
                'open cajas',
                'close cajas',
                'access pagos',
                'create pagos',
                'view pagos',
                'process pagos',
                'access conceptos pago',
                'view conceptos pago',
                'access series',
                'view exchange-rates',
                'generate pagos comprobantes',
            ])->get();
        $cajero->syncPermissions($cajeroPermissions);

        // Rol Auditor - Solo sector monitoreo
        $auditor = Role::firstOrCreate(['name' => 'Auditor']);
        $auditorPermissions = Permission::where('sector', 'monitoreo')->get();
        $auditor->syncPermissions($auditorPermissions);

        // Rol Configurador - Solo sector configuración
        $configurador = Role::firstOrCreate(['name' => 'Configurador']);
        $configuradorPermissions = Permission::where('sector', 'configuracion')
            ->whereNotIn('name', [
                'assign roles',
                'assign permissions',
                'manage permissions',
            ])->get();
        $configurador->syncPermissions($configuradorPermissions);
    }
}