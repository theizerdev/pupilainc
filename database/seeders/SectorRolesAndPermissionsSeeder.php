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
               /* 'enfermeros' => [
                    'name' => 'Enfermería',
                    'permissions' => [
                        'access enfermeros',
                        'create enfermeros',
                        'edit enfermeros',
                        'delete enfermeros',
                        'view enfermeros',
                        'activate enfermeros',
                        'deactivate enfermeros',
                        'view enfermeros schedule',
                        'edit enfermeros schedule',
                    ]
                ],*/
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
                 'consultas' => [
                    'name' => 'Consultas',
                    'permissions' => [
                        'access consultas',
                        //'access consultas calendario',
                        'access consultas en espera',
                        'access consultas en enfermeria',
                        'access consultas en consultorio',
                        'access consultas en gotas',
                        'access consultas en optica',
                        'access consultas en estudio',
                        'access consultas finalizadas',
                        'registrar signos vitales',
                    ]
                ],
            ],

            // 💰 SECTOR ADMINISTRACIÓN

            /*'administracion' => [
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

                'pagos' => [
                    'name' => 'Pagos',
                    'permissions' => [
                        'access pagos',
                        'create pagos',
                        'view pagos',
                        'process pagos',
                        'cancel pagos',
                        'refund pagos',
                        'export pagos',
                        'access pagos comprobantes',
                        'generate pagos comprobantes',
                    ]
                ],
                  /*
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

                 'categorias' => [
                    'name' => 'Categorías',
                    'permissions' => [
                        'access categorias',
                        'create categorias',
                        'edit categorias',
                        'delete categorias',
                        'view categorias',
                        'manage categorias',
                    ]
                ],
                'baremos' => [
                    'name' => 'Baremos',
                    'permissions' => [
                        'access baremos',
                        'create baremos',
                        'edit baremos',
                        'delete baremos',
                        'view baremos',
                        'manage baremos',
                    ]
                ],

                'clientes_fiscales' => [
                    'name' => 'Clientes Fiscales',
                    'permissions' => [
                        'access clientes-fiscales',
                        'create clientes-fiscales',
                        'edit clientes-fiscales',
                        'delete clientes-fiscales',
                        'view clientes-fiscales',
                    ]
                ],
                'notas_credito' => [
                    'name' => 'Notas de Crédito',
                    'permissions' => [
                        'access notas-credito',
                        'create notas-credito',
                        'view notas-credito',
                    ]
                ],
                'notas_debito' => [
                    'name' => 'Notas de Débito',
                    'permissions' => [
                        'access notas-debito',
                        'create notas-debito',
                        'view notas-debito',
                    ]
                ],
                'contabilidad' => [
                    'name' => 'Contabilidad',
                    'permissions' => [
                        'access contabilidad',
                        'view contabilidad',
                        'manage contabilidad',
                    ]
                ],
                'anulacion_talonarios' => [
                    'name' => 'Anulación de Talonarios',
                    'permissions' => [
                        'access anulacion-talonarios',
                        'create anulacion-talonarios',
                        'edit anulacion-talonarios',
                        'delete anulacion-talonarios',
                        'view anulacion-talonarios',
                    ]
                ],
            ],*/


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
                /*'consultorios' => [
                    'name' => 'Consultorios',
                    'permissions' => [
                        'access consultorios',
                        'create consultorios',
                        'edit consultorios',
                        'delete consultorios',
                        'view consultorios',
                    ]
                ],*/
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
            /*'monitoreo' => [
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

            ],*/

            // 📦 SECTOR INVENTARIO
            'inventario' => [
                'categorias_producto' => [
                    'name' => 'Categorías de Producto',
                    'permissions' => [
                        'access categorias-producto',
                        'create categorias-producto',
                        'edit categorias-producto',
                        'delete categorias-producto',
                        'view categorias-producto',
                    ]
                ],
                'marcas' => [
                    'name' => 'Marcas',
                    'permissions' => [
                        'access marcas',
                        'create marcas',
                        'edit marcas',
                        'delete marcas',
                        'view marcas',
                    ]
                ],
                'almacenes' => [
                    'name' => 'Almacenes',
                    'permissions' => [
                        'access almacenes',
                        'create almacenes',
                        'edit almacenes',
                        'delete almacenes',
                    ]
                ],
                'proveedores' => [
                    'name' => 'Proveedores',
                    'permissions' => [
                        'access proveedores',
                        'create proveedores',
                        'edit proveedores',
                        'delete proveedores',
                        'view proveedores',
                    ]
                ],
                'productos' => [
                    'name' => 'Productos',
                    'permissions' => [
                        'access productos',
                        'create productos',
                        'edit productos',
                        'delete productos',
                        'view productos',
                    ]
                ],
                'movimientos_inventario' => [
                    'name' => 'Movimientos de Inventario',
                    'permissions' => [
                        'access movimientos-inventario',
                        'create movimientos-inventario',
                        'view movimientos-inventario',
                    ]
                ],
                'ordenes_compra' => [
                    'name' => 'Órdenes de Compra',
                    'permissions' => [
                        'access ordenes-compra',
                        'create ordenes-compra',
                        'edit ordenes-compra',
                        'delete ordenes-compra',
                        'view ordenes-compra',
                    ]
                ],
                'alertas_inventario' => [
                    'name' => 'Alertas de Inventario',
                    'permissions' => [
                        'access alertas-inventario',
                    ]
                ],
            ],

            // 📱 SECTOR COMUNICACIONES (Adicional)
            'comunicaciones' => [
                'whatsapp' => [
                    'name' => 'WhatsApp',
                    'permissions' => [
                        'access whatsapp'

                    ]
                ],
            ],

            // 💬 SECTOR CHAT INTERNO
            'chat' => [
                'chat_interno' => [
                    'name' => 'Chat Interno',
                    'permissions' => [
                        'access chat interno',
                    ]
                ],
            ],

            // 🛎️ SECTOR RECEPCIÓN
           /* 'recepcion' => [
                'dashboard' => [
                    'name' => 'Dashboard Recepción',
                    'permissions' => [
                        'access recepcion dashboard',
                        'manage consultorios', // Asignar consultorios
                    ]
                ],
                'consulta_apertura' => [
                    'name' => 'Apertura de Consultas',
                    'permissions' => [
                        'access consulta apertura',
                        'iniciar consulta',
                        'enviar cuestionario whatsapp',
                        'ver respuestas preconsulta',
                    ]
                ],
            ],*/
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

        // Rol Médico - Solo sector médico
        $medico = Role::firstOrCreate(['name' => 'Médico']);
        $medicoPermissions = Permission::where('sector', 'medico')
            ->whereNotIn('name', [
                'delete medicos',
                'delete tipo-consultas',
                'delete especialidades',
                'delete subespecialidades',
            ])->get();
        $chatPermission = Permission::where('name', 'access chat interno')->get();
        $medico->syncPermissions($medicoPermissions->merge($chatPermission));



        // Rol Recepción - Sector médico + administración limitada
        $recepcion = Role::firstOrCreate(['name' => 'Recepción']);
        $recepcionPermissions = Permission::whereIn('sector', ['medico', 'administracion', 'recepcion'])
            ->whereIn('name', [
                // Recepción
                'access recepcion dashboard',
                //'manage consultorios',
                // Apertura de Consultas (nuevo módulo)
                'access consulta apertura',
                'iniciar consulta',
                'enviar cuestionario whatsapp',
                'ver respuestas preconsulta',
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
               // 'access conceptos pago',
               // 'view conceptos pago',
                //'access series',
                //'access cajas',
               // 'view cajas',
                //'access pagos',
               // 'create pagos',
               // 'view pagos',
            ])->get();
        $recepcion->syncPermissions($recepcionPermissions->merge($chatPermission));

         $this->command->info('✅ Roles y permisos procesados exitosamente');
        $this->command->info('📊 Total de permisos procesados: ' . count(Permission::all()));
    }
}
