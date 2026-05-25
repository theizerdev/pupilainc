<?php

namespace App\Services;

use App\Models\Cita;

class WhatsAppNotificationCatalog
{
    public static function sectors(): array
    {
        return [
            'medico' => [
                'label' => 'Medico',
                'icon' => 'ri-heart-pulse-line',
                'modules' => [
                    'citas' => [
                        'label' => 'Citas',
                        'actions' => self::appointmentActions(),
                    ],
                    'pacientes' => [
                        'label' => 'Pacientes',
                        'actions' => array_merge(
                            [
                                'carnet_menor' => self::action('Carnet de menor por WhatsApp', ['tutor'], true, true),
                            ],
                            self::standardActions(['paciente', 'administrador'])
                        ),
                    ],
                    'medicos' => [
                        'label' => 'Medicos',
                        'actions' => array_merge(
                            [
                                'bienvenida' => self::action('Bienvenida de cuenta', ['medico'], true, true),
                            ],
                            self::standardActions(['medico', 'administrador'])
                        ),
                    ],
                    'enfermeros' => [
                        'label' => 'Enfermeria',
                        'actions' => array_merge(
                            [
                                'bienvenida' => self::action('Bienvenida de cuenta', ['enfermero'], true, true),
                            ],
                            self::standardActions(['enfermero', 'administrador'])
                        ),
                    ],
                    'consultas' => [
                        'label' => 'Consultas',
                        'actions' => self::standardActions(['paciente', 'doctor', 'administrador']),
                    ],
                    'tipo_consultas' => [
                        'label' => 'Tipos de atencion',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'especialidades' => [
                        'label' => 'Especialidades',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'subespecialidades' => [
                        'label' => 'Subespecialidades',
                        'actions' => self::standardActions(['administrador']),
                    ],
                ],
            ],
            'administracion' => [
                'label' => 'Administracion',
                'icon' => 'ri-money-dollar-circle-line',
                'modules' => [
                    'pagos' => [
                        'label' => 'Pagos',
                        'actions' => self::standardActions(['paciente', 'cliente', 'administrador']),
                    ],
                    'cajas' => [
                        'label' => 'Cajas',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'tasas_cambio' => [
                        'label' => 'Tasas de cambio',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'baremos' => [
                        'label' => 'Servicios',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'conceptos_pago' => [
                        'label' => 'Conceptos de pago',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'categorias' => [
                        'label' => 'Categorias',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'clientes_fiscales' => [
                        'label' => 'Clientes fiscales',
                        'actions' => self::standardActions(['cliente', 'administrador']),
                    ],
                    'series' => [
                        'label' => 'Series de documentos',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'anulacion_talonarios' => [
                        'label' => 'Anulacion de talonarios',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'contabilidad' => [
                        'label' => 'Contabilidad',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'notas_credito' => [
                        'label' => 'Notas de credito',
                        'actions' => self::standardActions(['cliente', 'administrador']),
                    ],
                    'notas_debito' => [
                        'label' => 'Notas de debito',
                        'actions' => self::standardActions(['cliente', 'administrador']),
                    ],
                ],
            ],
            'configuracion' => [
                'label' => 'Configuracion',
                'icon' => 'ri-settings-3-line',
                'modules' => [
                    'usuarios' => [
                        'label' => 'Usuarios',
                        'actions' => array_merge(
                            [
                                'bienvenida' => self::action('Bienvenida de cuenta', ['usuario'], true, true),
                                'restablecer_password' => self::action('Restablecimiento de clave', ['usuario'], true, true),
                                'codigo_verificacion' => self::action('Codigo de verificacion', ['usuario'], true, true),
                            ],
                            self::standardActions(['usuario', 'administrador'])
                        ),
                    ],
                    'empresas' => [
                        'label' => 'Empresas',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'sucursales' => [
                        'label' => 'Sucursales',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'consultorios' => [
                        'label' => 'Consultorios',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'paises' => [
                        'label' => 'Paises',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'roles' => [
                        'label' => 'Roles',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'permisos' => [
                        'label' => 'Permisos',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'personalizacion' => [
                        'label' => 'Personalizacion',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'impuestos' => [
                        'label' => 'Impuestos',
                        'actions' => self::standardActions(['administrador']),
                    ],
                ],
            ],
            'comunicaciones' => [
                'label' => 'Comunicaciones',
                'icon' => 'ri-whatsapp-line',
                'modules' => [
                    'whatsapp' => [
                        'label' => 'WhatsApp',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'chat_interno' => [
                        'label' => 'Chat interno',
                        'actions' => self::standardActions(['usuario', 'administrador']),
                    ],
                ],
            ],
            'monitoreo' => [
                'label' => 'Monitoreo',
                'icon' => 'ri-line-chart-line',
                'modules' => [
                    'notificaciones' => [
                        'label' => 'Notificaciones',
                        'actions' => self::standardActions(['usuario', 'administrador']),
                    ],
                    'actividad' => [
                        'label' => 'Actividad del sistema',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'sesiones' => [
                        'label' => 'Sesiones activas',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'respaldo' => [
                        'label' => 'Respaldo de base de datos',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'monitoreo_sistema' => [
                        'label' => 'Monitoreo del sistema',
                        'actions' => self::standardActions(['administrador']),
                    ],
                ],
            ],
            'inventario' => [
                'label' => 'Inventario',
                'icon' => 'ri-store-2-line',
                'modules' => [
                    'productos' => [
                        'label' => 'Productos',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'categorias_producto' => [
                        'label' => 'Categorias de producto',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'marcas' => [
                        'label' => 'Marcas',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'almacenes' => [
                        'label' => 'Almacenes',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'proveedores' => [
                        'label' => 'Proveedores',
                        'actions' => self::standardActions(['proveedor', 'administrador']),
                    ],
                    'movimientos_inventario' => [
                        'label' => 'Movimientos de inventario',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'ordenes_compra' => [
                        'label' => 'Ordenes de compra',
                        'actions' => self::standardActions(['proveedor', 'administrador']),
                    ],
                    'alertas_inventario' => [
                        'label' => 'Alertas de inventario',
                        'actions' => self::standardActions(['administrador']),
                    ],
                ],
            ],
            'recepcion' => [
                'label' => 'Recepcion',
                'icon' => 'ri-service-line',
                'modules' => [
                    'dashboard' => [
                        'label' => 'Dashboard recepcion',
                        'actions' => self::standardActions(['administrador']),
                    ],
                    'consulta_apertura' => [
                        'label' => 'Apertura de consultas',
                        'actions' => [
                            'preconsulta' => self::action('Enviar cuestionario de preconsulta', ['paciente'], true, true),
                            'create' => self::action('Crear', ['administrador']),
                            'edit' => self::action('Editar', ['administrador']),
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function recipientLabels(): array
    {
        return [
            'paciente' => 'Paciente',
            'doctor' => 'Doctor',
            'tutor' => 'Tutor',
            'usuario' => 'Usuario',
            'medico' => 'Medico',
            'enfermero' => 'Enfermero',
            'cliente' => 'Cliente',
            'empleado' => 'Empleado',
            'proveedor' => 'Proveedor',
            'administrador' => 'Administrador',
        ];
    }

    public static function event(string $module, string $action, string $recipient): ?array
    {
        foreach (self::sectors() as $sectorKey => $sector) {
            if (! isset($sector['modules'][$module]['actions'][$action])) {
                continue;
            }

            $event = $sector['modules'][$module]['actions'][$action];
            if (! in_array($recipient, $event['recipients'], true)) {
                return null;
            }

            return [
                'sector' => $sectorKey,
                'module' => $sector['modules'][$module]['label'],
                'action' => $event['label'],
                'connected' => $event['connected'],
                'default_enabled' => $event['default_enabled'],
            ];
        }

        return null;
    }

    public static function actionKeyForAppointmentState(string $estado): string
    {
        return 'estado_'.$estado;
    }

    private static function appointmentActions(): array
    {
        $actions = [
            'nueva_cita' => self::action('Nueva cita agendada', ['paciente', 'doctor'], true, true),
            'preconsulta' => self::action('Cuestionario de preconsulta', ['paciente'], true, true),
            'recordatorio_12h' => self::action('Recordatorio 12 horas antes', ['paciente'], true, true),
            'recordatorio_6h' => self::action('Recordatorio 6 horas antes', ['paciente'], true, true),
            'recordatorio_1h' => self::action('Recordatorio 1 hora antes', ['paciente'], true, true),
            'recordatorio_manual' => self::action('Recordatorio manual', ['paciente'], true, true),
            'confirmacion_inicial' => self::action('Solicitud de confirmacion', ['paciente'], true, true),
            'confirmacion_clarificacion' => self::action('Clarificacion de confirmacion', ['paciente'], true, true),
        ];

        foreach (Cita::ESTADO_LABELS as $estado => $label) {
            $actions[self::actionKeyForAppointmentState($estado)] = self::action(
                'Cambio a '.$label,
                ['paciente', 'doctor'],
                true,
                null
            );
        }

        return $actions;
    }

    private static function standardActions(array $recipients): array
    {
        return [
            'create' => self::action('Crear', $recipients),
            'edit' => self::action('Editar', $recipients),
            'delete' => self::action('Eliminar', $recipients),
            'activate' => self::action('Activar', $recipients),
            'deactivate' => self::action('Desactivar', $recipients),
        ];
    }

    private static function action(
        string $label,
        array $recipients,
        bool $connected = false,
        ?bool $defaultEnabled = false
    ): array {
        return [
            'label' => $label,
            'recipients' => $recipients,
            'connected' => $connected,
            'default_enabled' => $defaultEnabled ?? true,
        ];
    }
}
