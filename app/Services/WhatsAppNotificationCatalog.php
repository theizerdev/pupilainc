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
                        'actions' => [
                            'carnet_menor' => self::action('Carnet de menor por WhatsApp', ['tutor']),
                        ],
                    ],
                    'medicos' => [
                        'label' => 'Medicos',
                        'actions' => [
                            'bienvenida' => self::action('Bienvenida de cuenta', ['medico']),
                        ],
                    ],
                    'enfermeros' => [
                        'label' => 'Enfermeria',
                        'actions' => [
                            'bienvenida' => self::action('Bienvenida de cuenta', ['enfermero']),
                        ],
                    ],
                ],
            ],
            'recepcion' => [
                'label' => 'Recepcion',
                'icon' => 'ri-service-line',
                'modules' => [
                    'consulta_apertura' => [
                        'label' => 'Apertura de consultas',
                        'actions' => [
                            'preconsulta' => self::action('Enviar cuestionario de preconsulta', ['paciente']),
                        ],
                    ],
                ],
            ],
            'administracion' => [
                'label' => 'Administracion',
                'icon' => 'ri-money-dollar-circle-line',
                'modules' => [
                    'cajas' => [
                        'label' => 'Cajas',
                        'actions' => [
                            'cierre_reporte' => self::action('Cierre de caja con reporte', ['administrador']),
                        ],
                    ],
                    'pedidos' => [
                        'label' => 'Pedidos',
                        'actions' => [
                            'pago_confirmado' => self::action('Pago confirmado', ['cliente']),
                            'empleado_asignado' => self::action('Asignacion de pedido', ['cliente', 'empleado']),
                            'pedido_cancelado' => self::action('Pedido cancelado', ['cliente']),
                            'entrega_confirmada' => self::action('Entrega confirmada', ['cliente']),
                            'pedido_reversion' => self::action('Cambio, devolucion o revision', ['cliente', 'empleado']),
                        ],
                    ],
                ],
            ],
            'configuracion' => [
                'label' => 'Configuracion',
                'icon' => 'ri-settings-3-line',
                'modules' => [
                    'usuarios' => [
                        'label' => 'Usuarios',
                        'actions' => [
                            'bienvenida' => self::action('Bienvenida de cuenta', ['usuario']),
                            'restablecer_password' => self::action('Restablecimiento de clave', ['usuario']),
                            'codigo_verificacion' => self::action('Codigo de verificacion', ['usuario']),
                            'cuenta_desbloqueada' => self::action('Cuenta desbloqueada', ['usuario']),
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
            'nueva_cita' => self::action('Nueva cita agendada', ['paciente', 'doctor']),
            'preconsulta' => self::action('Cuestionario de preconsulta', ['paciente']),
            'recordatorio_12h' => self::action('Recordatorio 12 horas antes', ['paciente']),
            'recordatorio_6h' => self::action('Recordatorio 6 horas antes', ['paciente']),
            'recordatorio_1h' => self::action('Recordatorio 1 hora antes', ['paciente']),
            'recordatorio_manual' => self::action('Recordatorio manual', ['paciente']),
            'confirmacion_inicial' => self::action('Solicitud de confirmacion', ['paciente']),
            'confirmacion_clarificacion' => self::action('Clarificacion de confirmacion', ['paciente']),
        ];

        foreach (Cita::ESTADO_LABELS as $estado => $label) {
            $actions[self::actionKeyForAppointmentState($estado)] = self::action(
                'Cambio a '.$label,
                ['paciente', 'doctor'],
                null
            );
        }

        return $actions;
    }

    private static function action(
        string $label,
        array $recipients,
        ?bool $defaultEnabled = true
    ): array {
        return [
            'label' => $label,
            'recipients' => $recipients,
            'connected' => true,
            'default_enabled' => $defaultEnabled ?? true,
        ];
    }
}
