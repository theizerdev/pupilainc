<?php

if (!function_exists('getPermissionSectors')) {
    function getPermissionSectors(): array
    {
        return [
            'medico' => [
                'name' => '🏥 Médico',
                'description' => 'Gestión de pacientes, médicos, citas y especialidades',
                'color' => 'blue',
                'icon' => 'ri-heart-pulse-line',
                'modules' => ['tipo-consultas', 'pacientes', 'medicos', 'citas', 'especialidades', 'subespecialidades']
            ],
            'recepcion' => [
                'name' => '🛎️ Recepción',
                'description' => 'Gestión de recepción, citas y consultorios',
                'color' => 'pink',
                'icon' => 'ri-service-line',
                'modules' => ['dashboard', 'control-consultorios']
            ],
            'administracion' => [
                'name' => '💰 Administración',
                'description' => 'Gestión financiera, cajas, pagos y tasas de cambio',
                'color' => 'green',
                'icon' => 'ri-money-dollar-circle-line',
                'modules' => ['cajas', 'pagos', 'conceptos_pago', 'exchange-rates', 'series', 'reglas_mora']
            ],
            'configuracion' => [
                'name' => '⚙️ Configuración',
                'description' => 'Configuración del sistema, empresas, usuarios y roles',
                'color' => 'purple',
                'icon' => 'ri-settings-3-line',
                'modules' => ['empresas', 'consultorios', 'sucursales', 'paises', 'users', 'roles', 'permissions', 'personalizacion']
            ],
            'monitoreo' => [
                'name' => '📊 Monitoreo',
                'description' => 'Monitoreo del sistema, actividad y respaldos',
                'color' => 'orange',
                'icon' => 'ri-line-chart-line',
                'modules' => ['sesiones', 'actividad', 'respaldo', 'monitoreo_sistema', 'notificaciones']
            ],
            'comunicaciones' => [
                'name' => '📱 Comunicaciones',
                'description' => 'WhatsApp, mensajes y plantillas',
                'color' => 'teal',
                'icon' => 'ri-whatsapp-line',
                'modules' => ['whatsapp', 'whatsapp templates', 'whatsapp messages', 'chat interno']
            ],
             'medico' => [
                'name' => '🏥 Médico',
                'description' => 'Gestión de pacientes, médicos, citas y especialidades',
                'color' => 'blue',
                'icon' => 'ri-heart-pulse-line',
                'modules' => ['tipo-consultas', 'pacientes', 'medicos', 'citas', 'especialidades', 'subespecialidades']
            ],
            'sistema' => [
                'name' => '🔧 Sistema',
                'description' => 'Configuraciones del sistema y API',
                'color' => 'gray',
                'icon' => 'ri-tools-line',
                'modules' => ['system', 'api', 'jwt']
            ]
        ];
    }
}

if (!function_exists('getSectorPermissions')) {
    function getSectorPermissions(string $sector): \Illuminate\Support\Collection
    {
        return \Spatie\Permission\Models\Permission::where('sector', $sector)
            ->orderBy('module')
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('getUserSectors')) {
    function getUserSectors($user): array
    {
        $permissions = $user->permissions()->pluck('sector')->unique()->filter()->values();
        $sectors = getPermissionSectors();

        return $permissions->mapWithKeys(function ($sector) use ($sectors) {
            return [$sector => $sectors[$sector] ?? ['name' => ucfirst($sector)]];
        })->toArray();
    }
}

if (!function_exists('hasSectorAccess')) {
    function hasSectorAccess($user, string $sector): bool
    {
        return $user->permissions()->where('sector', $sector)->exists();
    }
}

if (!function_exists('getSectorColor')) {
    function getSectorColor(string $sector): string
    {
        $sectors = getPermissionSectors();
        return $sectors[$sector]['color'] ?? 'gray';
    }
}

if (!function_exists('getSectorIcon')) {
    function getSectorIcon(string $sector): string
    {
        $sectors = getPermissionSectors();
        return explode(' ', $sectors[$sector]['name'])[0] ?? '📋';
    }
}

if (!function_exists('formatSectorName')) {
    function formatSectorName(string $sector, bool $withIcon = true): string
    {
        $sectors = getPermissionSectors();
        $name = $sectors[$sector]['name'] ?? ucfirst($sector);

        if (!$withIcon) {
            return str_replace(['🏥', '💰', '⚙️', '📊', '📱', '🔧'], '', $name);
        }

        return $name;
    }
}

if (!function_exists('getSectorStats')) {
    function getSectorStats(): array
    {
        $sectors = getPermissionSectors();
        $stats = [];

        foreach ($sectors as $key => $sector) {
            $totalPermissions = \Spatie\Permission\Models\Permission::where('sector', $key)->count();
            $totalRoles = \Spatie\Permission\Models\Role::whereHas('permissions', function ($query) use ($key) {
                $query->where('sector', $key);
            })->count();

            $stats[$key] = [
                'name' => $sector['name'],
                'total_permissions' => $totalPermissions,
                'total_roles' => $totalRoles,
                'color' => $sector['color'],
                'description' => $sector['description']
            ];
        }

        return $stats;
    }
}

if (!function_exists('getSectorMenuItems')) {
    function getSectorMenuItems(): array
    {
        return [
            'recepcion' => [
                'label' => 'Recepción',
                'icon' => 'ri-service-line',
                'items' => [
                    [
                        'label' => 'Panel Recepción',
                        'icon' => 'ri-dashboard-line',
                        'permission' => 'access recepcion dashboard',
                        'route' => 'admin.recepcion.dashboard',
                        'route_horizontal' => 'admin.recepcion.dashboard',
                        'active' => 'admin.recepcion.dashboard',
                    ],
                    [
                        'label' => 'Control Consultorios',
                        'icon' => 'ri-hospital-line',
                        'permission' => 'access consultorios',
                        'route' => 'admin.recepcion.control-consultorios',
                        'route_horizontal' => 'admin.recepcion.control-consultorios',
                        'active' => 'admin.recepcion.control-consultorios',
                    ],
                     [
                        'label' => 'Calendario General',
                        'icon' => 'ri-calendar-2-line',
                        'permission' => 'access citas',
                        'route' => 'admin.calendario',
                        'route_horizontal' => 'admin.calendario',
                        'active' => 'admin.calendario',
                    ],

                ]
            ],
            'medico' => [
                'label' => 'Médico',
                'icon' => 'ri-heart-pulse-line',
                'items' => [
                    [
                        'label' => 'Especialidades M.',
                        'icon' => 'ri-file-line',
                        'permissions' => ['access especialidades', 'access subespecialidades'],
                        'active' => 'admin.especialidades.*|admin.subespecialidades.*',
                        'children' => [
                            ['label' => 'Especialidades', 'permission' => 'access especialidades', 'route' => 'admin.especialidades.index', 'active' => 'admin.especialidades.*'],
                            ['label' => 'Subespecialidades', 'permission' => 'access subespecialidades', 'route' => 'admin.subespecialidades.index', 'active' => 'admin.subespecialidades.*'],
                        ]
                    ],
                    [
                        'label' => 'Médicos',
                        'icon' => 'ri-health-book-line',
                        'permission' => 'access medicos',
                        'route' => 'admin.medicos.index',
                        'active' => 'admin.medicos.*',
                    ],
                    [
                        'label' => 'Enfermeros',
                        'icon' => 'ri-nurse-line',
                        'permission' => 'access enfermeros',
                        'route' => 'admin.enfermeros.index',
                        'active' => 'admin.enfermeros.*',
                    ],
                    [
                        'label' => 'Pacientes',
                        'icon' => 'ri-user-heart-line',
                        'permission' => 'access pacientes',
                        'route' => 'admin.pacientes.index',
                        'active' => 'admin.pacientes.*',
                    ],
                    [
                        'label' => 'Citas',
                        'icon' => 'ri-calendar-line',
                        'permission' => 'access citas',
                        'active' => 'admin.citas.*',
                        'children' => [
                            ['label' => 'Calendario', 'route' => 'admin.calendario', 'active' => 'admin.calendario'],
                            ['label' => 'Gestión de Citas', 'route' => 'admin.citas.index', 'active' => 'admin.citas.index'],
                            ['label' => 'Analytics', 'route' => 'admin.citas.analytics', 'active' => 'admin.citas.analytics'],
                            ['label' => 'Recordatorios', 'route' => 'admin.citas.recordatorios', 'active' => 'admin.citas.recordatorios'],
                            ['label' => 'Re-agendamiento', 'route' => 'admin.citas.reagendamiento', 'active' => 'admin.citas.reagendamiento'],
                        ]
                    ],
                    [
                        'label' => 'Gestión Consultas',
                        'icon' => 'ri-stethoscope-line',
                        'permission' => 'access consultas',
                        'active' => 'admin.gestion.consultas.*',
                        'children' => [
                            ['label' => 'Calendario', 'route' => 'admin.gestion.consultas.index', 'active' => 'admin.gestion.consultas.index', 'permission' => 'access consultas calendario'],
                            ['label' => 'Sala de Espera', 'route' => 'admin.gestion.consultas.sala-espera', 'active' => 'admin.gestion.consultas.sala-espera', 'permission' => 'access consultas en espera'],
                            ['label' => 'En Enfermería', 'route' => 'admin.gestion.consultas.en-enfermeria', 'active' => 'admin.gestion.consultas.en-enfermeria', 'permission' => 'access consultas en enfermeria'],
                            ['label' => 'En Consultorio', 'route' => 'admin.gestion.consultas.en-consultorio', 'active' => 'admin.gestion.consultas.en-consultorio', 'permission' => 'access consultas en consultorio'],
                            ['label' => 'En Gotas', 'route' => 'admin.gestion.consultas.en-gotas', 'active' => 'admin.gestion.consultas.en-gotas', 'permission' => 'access consultas en gotas'],
                            ['label' => 'En Óptica', 'route' => 'admin.gestion.consultas.en-optica', 'active' => 'admin.gestion.consultas.en-optica', 'permission' => 'access consultas en optica'],
                            ['label' => 'En Estudio', 'route' => 'admin.gestion.consultas.en-estudio', 'active' => 'admin.gestion.consultas.en-estudio', 'permission' => 'access consultas en estudio'],
                            ['label' => 'Finalizadas', 'route' => 'admin.gestion.consultas.finalizadas', 'active' => 'admin.gestion.consultas.finalizadas', 'permission' => 'access consultas finalizadas'],
                        ]
                    ],
                    [
                        'label' => 'Tipos de Atención',
                        'icon' => 'ri-building-line',
                        'permission' => 'access tipo-consultas',
                        'route' => 'admin.tipo-consultas.index',
                        'active' => 'admin.tipo-consultas*',
                    ],
                ]
            ],
            'administracion' => [
                'label' => 'Administración',
                'icon' => 'ri-money-dollar-circle-line',
                'items' => [
                    [
                        'label' => 'Pagos y Finanzas',
                        'icon' => 'ri-money-dollar-circle-line',
                        'permissions' => ['access baremos', 'access categorias', 'access conceptos pago', 'access cajas', 'access pagos', 'access baremos'],
                        'active' => 'admin.categorias.*|admin.pagos.*|admin.conceptos-pago.*|admin.cajas.*|admin.baremos.*|admin.clientes-fiscales.*',
                        'children' => [
                            ['label' => 'Categorías', 'permission' => 'access categorias', 'route' => 'admin.categorias.index', 'active' => 'admin.categorias.*'],
                            ['label' => 'Pagos', 'permission' => 'access pagos', 'route' => 'admin.pagos.index', 'active' => 'admin.pagos.*'],
                            ['label' => 'Notas de Crédito', 'permission' => 'access notas-credito', 'route' => 'admin.notas-credito.index', 'active' => 'admin.notas-credito.*'],
                            ['label' => 'Notas de Débito', 'permission' => 'access notas-debito', 'route' => 'admin.notas-debito.index', 'active' => 'admin.notas-debito.*'],
                            ['label' => 'Servicios', 'permission' => 'access baremos', 'route' => 'admin.baremos.index', 'active' => 'admin.baremos.*'],
                            ['label' => 'Clientes Fiscales', 'permission' => 'access clientes-fiscales', 'route' => 'admin.clientes-fiscales.index', 'active' => 'admin.clientes-fiscales.*'],
                            ['label' => 'Conceptos de Pago', 'permission' => 'access conceptos pago', 'route' => 'admin.conceptos-pago.index', 'active' => 'admin.conceptos-pago.*'],
                            ['label' => 'Caja Chica', 'permission' => 'access cajas', 'route' => 'admin.cajas.index', 'active' => 'admin.cajas.*'],
                            ['label' => 'Anulación Talonarios', 'permission' => 'access series', 'route' => 'admin.anulacion-talonarios.index', 'active' => 'admin.anulacion-talonarios.*'],
                        ]
                    ],
                    [
                        'label' => 'Series',
                        'icon' => 'ri-file-list-3-line',
                        'permission' => 'access series',
                        'route' => 'admin.series.index',
                        'active' => 'admin.series.*',
                    ],
                    [
                        'label' => 'Configuración Impuestos',
                        'icon' => 'ri-percent-line',
                        'permission' => 'access impuestos',
                        'route' => 'admin.impuestos.index',
                        'active' => 'admin.impuestos.*',
                    ],
                    [
                        'label' => 'Tasa de cambio',
                        'icon' => 'ri-exchange-dollar-line',
                        'permission' => 'view exchange-rates',
                        'route' => 'admin.exchange-rates',
                        'active' => 'admin.exchange-rates',
                    ],
                    [
                        'label' => 'Contabilidad',
                        'icon' => 'ri-calculator-line',
                        'permissions' => ['access contabilidad', 'view contabilidad'],
                        'active' => 'admin.contabilidad.*|admin.seniat.*',
                        'children' => [
                            ['label' => 'Plan de Cuentas', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.plan-cuentas', 'active' => 'admin.contabilidad.plan-cuentas'],
                            ['label' => 'Asientos Contables', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.asientos', 'active' => 'admin.contabilidad.asientos'],
                            ['label' => 'Libro Diario', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.libro-diario', 'active' => 'admin.contabilidad.libro-diario'],
                            ['label' => 'Libro Mayor', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.libro-mayor', 'active' => 'admin.contabilidad.libro-mayor'],
                            ['label' => 'Balance Comprobación', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.balance-comprobacion', 'active' => 'admin.contabilidad.balance-comprobacion'],
                            ['label' => 'Balance General', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.balance-general', 'active' => 'admin.contabilidad.balance-general'],
                            ['label' => 'Estado de Resultados', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.estado-resultados', 'active' => 'admin.contabilidad.estado-resultados'],
                            ['label' => 'Cierre Contable', 'permission' => 'access contabilidad', 'route' => 'admin.contabilidad.cierre-contable', 'active' => 'admin.contabilidad.cierre-contable'],
                            ['label' => 'Libro de Ventas', 'permission' => 'access contabilidad', 'route' => 'admin.seniat.libro-ventas', 'active' => 'admin.seniat.libro-ventas'],
                        ]
                    ],
                    [
                        'label' => 'Anulación de Talonarios',
                        'icon' => 'ri-file-damage-line',
                        'permission' => 'access anulacion-talonarios',
                        'route' => 'admin.anulacion-talonarios.index',
                        'active' => 'admin.anulacion-talonarios.*',
                    ],
                ]
            ],
            'configuracion' => [
                'label' => 'Configuración',
                'icon' => 'ri-settings-3-line',
                'items' => [
                    [
                        'label' => 'Institucional',
                        'icon' => 'ri-building-4-line',
                        'permissions' => ['access empresas', 'access sucursales', 'access paises', 'access consultorios'],
                        'active' => 'admin.empresas.*|admin.sucursales.*|admin.paises.*|admin.consultorios.*',
                        'children' => [
                            ['label' => 'Empresas', 'permission' => 'access empresas', 'route' => 'admin.empresas.index', 'active' => 'admin.empresas.index'],
                            ['label' => 'Sucursales', 'permission' => 'access sucursales', 'route' => 'admin.sucursales.index', 'active' => 'admin.sucursales.index'],
                            ['label' => 'Consultorios', 'permission' => 'access consultorios', 'route' => 'admin.consultorios.index', 'active' => 'admin.consultorios.index'],
                            ['label' => 'Países', 'permission' => 'access paises', 'route' => 'admin.paises.index', 'active' => 'admin.paises.index'],
                        ]
                    ],
                    [
                        'label' => 'Usuarios y Acceso',
                        'icon' => 'ri-group-line',
                        'permissions' => ['access users', 'access roles', 'access permissions'],
                        'active' => 'admin.users.*|admin.roles.*|admin.permissions.*',
                        'children' => [
                            ['label' => 'Usuarios', 'permission' => 'access users', 'route' => 'admin.users.index', 'active' => 'admin.users.index'],
                            ['label' => 'Roles', 'permission' => 'access roles', 'route' => 'admin.roles.index', 'active' => 'admin.roles.index'],
                            ['label' => 'Permisos', 'permission' => 'access permissions', 'route' => 'admin.permissions.index', 'active' => 'admin.permissions.index'],
                        ]
                    ],
                    [
                        'label' => 'Personalización',
                        'icon' => 'ri-palette-line',
                        'permission' => 'access template customization',
                        'route' => 'admin.template-customization',
                        'active' => 'admin.template-customization',
                    ],

                ]
            ],
            'comunicaciones' => [
                'label' => 'Comunicaciones',
                'icon' => 'ri-whatsapp-line',
                'items' => [
                    [
                        'label' => 'WhatsApp',
                        'icon' => 'ri-whatsapp-line',
                        'permission' => 'access whatsapp',
                        'route' => 'admin.whatsapp.index',
                        'route_horizontal' => 'admin.whatsapp.index',
                        'active' => 'admin.whatsapp.*',
                    ],
                ]
            ],
            'monitoreo' => [
                'label' => 'Monitoreo',
                'icon' => 'ri-line-chart-line',
                'items' => [
                    [
                        'label' => 'Sesiones',
                        'icon' => 'ri-user-settings-line',
                        'permission' => 'view active sessions',
                        'route' => 'admin.active-sessions.index',
                        'active' => 'admin.active-sessions*',
                    ],
                    [
                        'label' => 'Actividad',
                        'icon' => 'ri-history-line',
                        'permission' => 'access activity log',
                        'route' => 'admin.activity-log',
                        'active_path' => 'admin/activity-log*',
                    ],
                    [
                        'label' => 'Exportar Base de Datos',
                        'icon' => 'ri-database-2-line',
                        'permission' => 'access database export',
                        'route' => 'admin.database-export',
                        'active' => 'admin.database-export',
                    ],
                ],
            ],
        ];
    }
}

if (!function_exists('isMenuItemActive')) {
    function isMenuItemActive(array $item): bool
    {
        if (isset($item['active'])) {
            $patterns = explode('|', $item['active']);
            foreach ($patterns as $pattern) {
                if (request()->routeIs(trim($pattern))) {
                    return true;
                }
            }
        }
        if (isset($item['active_path'])) {
            if (request()->is($item['active_path'])) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('isSectorActive')) {
    function isSectorActive(array $sectorItems): bool
    {
        foreach ($sectorItems as $item) {
            if (isMenuItemActive($item)) {
                return true;
            }
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isMenuItemActive($child)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
