<?php

if (!function_exists('getPermissionSectors')) {
    /**
     * Obtener todos los sectores disponibles
     */
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
                'modules' => ['empresas', 'sucursales', 'paises', 'users', 'roles', 'permissions', 'personalizacion']
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
                'modules' => ['whatsapp', 'whatsapp templates', 'whatsapp messages']
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
    /**
     * Obtener permisos por sector
     */
    function getSectorPermissions(string $sector): \Illuminate\Support\Collection
    {
        return \Spatie\Permission\Models\Permission::where('sector', $sector)
            ->orderBy('module')
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('getUserSectors')) {
    /**
     * Obtener sectores a los que tiene acceso un usuario
     */
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
    /**
     * Verificar si un usuario tiene acceso a un sector específico
     */
    function hasSectorAccess($user, string $sector): bool
    {
        return $user->permissions()->where('sector', $sector)->exists();
    }
}

if (!function_exists('getSectorColor')) {
    /**
     * Obtener el color asociado a un sector
     */
    function getSectorColor(string $sector): string
    {
        $sectors = getPermissionSectors();
        return $sectors[$sector]['color'] ?? 'gray';
    }
}

if (!function_exists('getSectorIcon')) {
    /**
     * Obtener el icono/emoji asociado a un sector
     */
    function getSectorIcon(string $sector): string
    {
        $sectors = getPermissionSectors();
        return explode(' ', $sectors[$sector]['name'])[0] ?? '📋';
    }
}

if (!function_exists('formatSectorName')) {
    /**
     * Formatear el nombre de un sector para mostrar
     */
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
    /**
     * Obtener estadísticas de permisos por sector
     */
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
    /**
     * Obtener los items del menú organizados por sector
     * Cada sector agrupa sus items de menú con permisos, rutas e iconos
     */
    function getSectorMenuItems(): array
    {
        return [
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
                            ['label' => 'Gestión de Citas', 'route' => 'admin.citas.index', 'active' => 'admin.citas.index'],
                            ['label' => 'Analytics', 'route' => 'admin.citas.analytics', 'active' => 'admin.citas.analytics'],
                            ['label' => 'Recordatorios', 'route' => 'admin.citas.recordatorios', 'active' => 'admin.citas.recordatorios'],
                            ['label' => 'Re-agendamiento', 'route' => 'admin.citas.reagendamiento', 'active' => 'admin.citas.reagendamiento'],
                        ]
                    ],
                    [
                        'label' => 'Tipos de Consultas',
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
                        'permissions' => ['access conceptos pago', 'access cajas'],
                        'active' => 'admin.pagos.*|admin.conceptos-pago.*|admin.cajas.*',
                        'children' => [
                            ['label' => 'Conceptos de Pago', 'permission' => 'access conceptos pago', 'route' => 'admin.conceptos-pago.index', 'active' => 'admin.conceptos-pago.index'],
                            ['label' => 'Caja Chica', 'permission' => 'access cajas', 'route' => 'admin.cajas.index', 'active' => 'admin.cajas.index'],
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
                        'label' => 'Tasas BCV',
                        'icon' => 'ri-exchange-dollar-line',
                        'permission' => 'view exchange-rates',
                        'route' => 'admin.exchange-rates',
                        'active' => 'admin.exchange-rates',
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
                        'permissions' => ['access empresas', 'access sucursales', 'access paises'],
                        'active' => 'admin.empresas.*|admin.sucursales.*|admin.paises.*',
                        'children' => [
                            ['label' => 'Empresas', 'permission' => 'access empresas', 'route' => 'admin.empresas.index', 'active' => 'admin.empresas.index'],
                            ['label' => 'Países', 'permission' => 'access paises', 'route' => 'admin.paises.index', 'active' => 'admin.paises.index'],
                            ['label' => 'Sucursales', 'permission' => 'access sucursales', 'route' => 'admin.sucursales.index', 'active' => 'admin.sucursales.index'],
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
                ]
            ],
        ];
    }
}

if (!function_exists('isMenuItemActive')) {
    /**
     * Verificar si un item de menú está activo basado en la ruta actual
     */
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
    /**
     * Verificar si algún item dentro de un sector está activo
     */
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