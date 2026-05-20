<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\CitaConfirmacion;
use App\Models\Paciente;
use App\Models\WhatsAppScheduledMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Http;

class CitaNotificationService
{
    // protected WhatsAppService $whatsApp; // Eliminamos dependencia directa si no se usa
    protected ?int $empresaId;
    protected ?string $codigoPais = null;
    protected ?string $apiKey = null;

    public function __construct(?int $empresaId = null)
    {
        $this->empresaId = $empresaId;
        // Resolver API Key de la empresa
        if ($this->empresaId) {
            $this->apiKey = DB::table('empresas')->where('id', $this->empresaId)->value('whatsapp_api_key');
        } elseif (auth()->check() && auth()->user()->empresa_id) {
            $this->empresaId = auth()->user()->empresa_id;
            $this->apiKey = DB::table('empresas')->where('id', $this->empresaId)->value('whatsapp_api_key');
        }
    }

    public static function forCompany($empresaId): self
    {
        return new self($empresaId);
    }

    protected function obtenerCodigoPais(): string
    {
        if ($this->codigoPais !== null) {
            return $this->codigoPais;
        }

        //$this->codigoPais = '58';

        $empId = $this->empresaId;
        if (!$empId && auth()->check() && auth()->user()->empresa_id) {
            $empId = auth()->user()->empresa_id;
        }

        if ($empId) {
            $empresa = DB::table('empresas')->where('id', $empId)->first();
            if ($empresa && $empresa->pais_id) {
                $pais = DB::table('pais')->where('id', $empresa->pais_id)->first();
                if ($pais && $pais->codigo_telefonico) {
                    $this->codigoPais = ltrim($pais->codigo_telefonico, '+');
                }
            }
        }

        return $this->codigoPais;
    }

    protected function formatearTelefono(string $telefono): string
    {
        $limpio = preg_replace('/\D/', '', $telefono);

        // Eliminar ceros a la izquierda
        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = $this->obtenerCodigoPais();

        // Si no hay código de país configurado, retornar el número limpio
        if (empty($codigo)) {
            return '+' . $limpio;
        }

        // Verificar si el número ya tiene el código de país
        if (str_starts_with($limpio, $codigo)) {
            // El número ya tiene el código de país, retornar tal cual
            return '+' . $limpio;
        }

        // Verificar si el número tiene un código de país diferente (más de 10 dígitos)
        if (strlen($limpio) > 10) {
            // Asumimos que ya tiene código de país, retornar tal cual
            return '+' . $limpio;
        }

        // Si el número tiene entre 7 y 10 dígitos, agregar el código de país
        if (strlen($limpio) >= 7 && strlen($limpio) <= 10) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    // ===== NOTIFICACIONES AL CREAR CITA =====

    public function notificarNuevaCita(Cita $cita): array
    {
        // Cargar relaciones según tipo de cita
        if ($cita->mascota_id) {
            $cita->loadMissing(['mascota.propietario', 'medico', 'especialidad']);
            $destinatario = $cita->mascota;
        } else {
            $cita->loadMissing(['paciente.tutor', 'medico', 'especialidad']);
            $destinatario = $cita->paciente;
        }

        $telefonos = $this->obtenerTelefonosPaciente($destinatario);
        $resultado = false;
        $errores = [];

        // Crear confirmación y enviar mensaje unificado (notificación + confirmación)

            $confirmacion = $this->crearConfirmacion($cita);
            if ($confirmacion) {
                $mensajePaciente = $this->construirMensajeNuevaCitaConConfirmacion($cita, $confirmacion);
            } else {
                $mensajePaciente = $this->construirMensajeNuevaCita($cita);
            }


        foreach ($telefonos as $telefono) {
            if ($this->enviar($telefono, $mensajePaciente)) {
                $resultado = true;
            } else {
                $errores[] = "No se pudo enviar notificación al paciente: {$telefono}";
            }
        }

        if ($cita->medico && $cita->medico->telefono) {
            $mensajeMedico = $this->construirMensajeNuevaCitaMedico($cita);
            $telefonoMedico = $this->formatearTelefono($cita->medico->telefono);
            if ($this->enviar($telefonoMedico, $mensajeMedico)) {
                $resultado = true;
            } else {
                $errores[] = "No se pudo enviar notificación al médico: {$telefonoMedico}";
            }
        }

        $this->programarRecordatorios($cita);

        return [
            'success' => $resultado,
            'errors' => $errores,
            'message' => $resultado ? 'Notificaciones enviadas correctamente' : 'Error al enviar algunas notificaciones',
            'confirmacion_incluida' => !empty($confirmacion),
        ];
    }

    // ===== PROGRAMAR RECORDATORIOS =====

    public function programarRecordatorios(Cita $cita): void
    {
        // Cargar relaciones según tipo de cita
        if ($cita->mascota_id) {
            $cita->loadMissing(['mascota.propietario', 'medico']);
            $destinatario = $cita->mascota;
        } else {
            $cita->loadMissing(['paciente.tutor', 'medico']);
            $destinatario = $cita->paciente;
        }

        $telefonos = $this->obtenerTelefonosPaciente($destinatario);
        if (empty($telefonos)) return;

        $recordatorios = [
            'cita_recordatorio_12h' => $cita->fecha_inicio->copy()->subHours(12),
            'cita_recordatorio_6h'  => $cita->fecha_inicio->copy()->subHours(6),
            'cita_recordatorio_1h'  => $cita->fecha_inicio->copy()->subHour(),
        ];

        $etiquetas = [
            'cita_recordatorio_12h' => '12 horas',
            'cita_recordatorio_6h'  => '6 horas',
            'cita_recordatorio_1h'  => '1 hora',
        ];

        foreach ($recordatorios as $tipo => $fechaEnvio) {
            if ($fechaEnvio->isPast()) continue;

            // Issue 10: En el recordatorio de 12h incluir preconsulta si no fue llenada
            $incluirPreconsulta = ($tipo === 'cita_recordatorio_12h')
                && ($cita->estado_preconsulta === 'pendiente')
                && $cita->token_preconsulta;

            $mensaje = $incluirPreconsulta
                ? $this->construirMensajeRecordatorioConPreconsulta($cita, $etiquetas[$tipo])
                : $this->construirMensajeRecordatorio($cita, $etiquetas[$tipo]);

            foreach ($telefonos as $telefono) {
                $this->programarMensaje($cita, $telefono, $mensaje, $tipo, $fechaEnvio);
            }
        }
    }

    // ===== CANCELAR RECORDATORIOS PENDIENTES =====

    public function cancelarRecordatoriosPendientes(Cita $cita): void
    {
        WhatsAppScheduledMessage::pendientesDeCita($cita->id)->each(function ($msg) {
            $msg->cancelar();
        });
    }

    // ===== REPROGRAMAR (cancelar y volver a crear) =====

    public function reprogramarRecordatorios(Cita $cita): void
    {
        $this->cancelarRecordatoriosPendientes($cita);
        $this->programarRecordatorios($cita);
    }

    // ===== NOTIFICACIONES DE ESTADO =====

    public function notificarCambioEstado(Cita $cita, string $estadoAnterior): bool
    {
        $cita->loadMissing(['paciente.tutor', 'medico']);

        // Verificar si se debe enviar notificación al paciente
        if (\App\Models\ConfiguracionNotificacion::debeEnviar(
            $cita->empresa_id ?? $this->empresaId,
            'paciente',
            $cita->estado
        )) {
            // Obtener destinatario según tipo de cita
            $destinatario = $cita->mascota_id ? $cita->mascota : $cita->paciente;
            $telefonos = $this->obtenerTelefonosPaciente($destinatario);

            // Si el estado es 'confirmada', usar mensaje específico
            if ($cita->estado === Cita::ESTADO_CONFIRMADA) {
                $mensajePaciente = $this->construirMensajeConfirmacionPaciente($cita);
            } else {
                $mensajePaciente = $this->construirMensajeCambioEstado($cita, $estadoAnterior);
            }

            foreach ($telefonos as $telefono) {
                $this->enviar($telefono, $mensajePaciente);
            }
        }

        // Verificar si se debe enviar notificación al médico
        if ($cita->medico && $cita->medico->telefono) {
            if (\App\Models\ConfiguracionNotificacion::debeEnviar(
                $cita->empresa_id ?? $this->empresaId,
                'doctor',
                $cita->estado
            )) {
                // Si el estado es 'confirmada', usar mensaje específico de confirmación
                if ($cita->estado === Cita::ESTADO_CONFIRMADA) {
                    $mensajeMedico = $this->construirMensajeConfirmacionMedico($cita);
                } else {
                    $mensajeMedico = $this->construirMensajeCambioEstadoMedico($cita, $estadoAnterior);
                }

                $telefonoMedico = $this->formatearTelefono($cita->medico->telefono);
                $this->enviar($telefonoMedico, $mensajeMedico);
            }
        }

        return true;
    }

    public function notificarCancelacion(Cita $cita): bool
    {
        $cita->loadMissing(['medico']);

        $this->cancelarRecordatoriosPendientes($cita);

        // Notificar al destinatario (paciente o propietario de mascota)
        $destinatario = $cita->mascota_id ? $cita->mascota : $cita->paciente;
        $telefonos = $this->obtenerTelefonosPaciente($destinatario);
        $mensajePaciente = $this->construirMensajeCancelacion($cita);

        foreach ($telefonos as $telefono) {
            $this->enviar($telefono, $mensajePaciente);
        }

        // Notificar Médico
        if ($cita->medico && $cita->medico->telefono) {
            $mensajeMedico = $this->construirMensajeCancelacionMedico($cita);
            $telefonoMedico = $this->formatearTelefono($cita->medico->telefono);
            $this->enviar($telefonoMedico, $mensajeMedico);
        }

        return true;
    }

    public function enviarRecordatorio(Cita $cita): array
    {
        // Cargar relaciones según tipo de cita
        if ($cita->mascota_id) {
            $cita->loadMissing(['mascota.propietario', 'medico']);
            $destinatario = $cita->mascota;
        } else {
            $cita->loadMissing(['paciente.tutor', 'medico']);
            $destinatario = $cita->paciente;
        }

        $telefonos = $this->obtenerTelefonosPaciente($destinatario);
        if (empty($telefonos)) {
            return [
                'success' => false,
                'errors' => ['El paciente no tiene un número de teléfono registrado'],
                'message' => 'Error al enviar recordatorio'
            ];
        }

        $mensaje = $this->construirMensajeRecordatorio($cita);
        $resultado = false;
        $errores = [];

        foreach ($telefonos as $telefono) {
            // Guardar o buscar el mensaje en la tabla como "manual"
            $scheduledMessage = WhatsAppScheduledMessage::updateOrCreate(
                [
                    'cita_id' => $cita->id,
                    'notification_type' => 'manual',
                    'recipient_phone' => $telefono,
                ],
                [
                    'empresa_id' => $cita->empresa_id ?? $this->empresaId,
                    'recipient_name' => $this->obtenerNombreDestinatario($cita),
                    'message_content' => $mensaje,
                    'scheduled_at' => now(),
                    'status' => 'pending',
                    'attempts' => 0,
                    'max_attempts' => 3,
                    'created_by' => auth()->id(),
                ]
            );

            if ($this->enviar($telefono, $mensaje)) {
                $resultado = true;
                $scheduledMessage->markAsSent();
            } else {
                $errores[] = "No se pudo enviar mensaje al teléfono {$telefono}";
                $scheduledMessage->markAsFailed("Error al conectar con la API de WhatsApp");
            }
        }

        return [
            'success' => $resultado,
            'errors' => $errores,
            'message' => $resultado ? 'Recordatorio enviado correctamente' : 'No se pudo enviar el recordatorio'
        ];
    }

    // ===== HELPERS PARA OBTENER DATOS DEL PACIENTE/MASCOTA =====

    /**
     * Obtener nombre completo del destinatario (paciente humano o mascota)
     */
    protected function obtenerNombreDestinatario(Cita $cita): string
    {
        if ($cita->mascota_id && $cita->mascota) {
            // Cita veterinaria - nombre de la mascota con especie y raza
            $nombre = $cita->mascota->nombre;
            $especie = $cita->mascota->especie?->nombre ?? '';
            $raza = $cita->mascota->raza?->nombre ?? '';

            $detalles = [];
            if ($especie) $detalles[] = $especie;
            if ($raza) $detalles[] = $raza;

            if (!empty($detalles)) {
                return "{$nombre} - " . implode(' ', $detalles);
            }
            return $nombre;
        }

        // Cita humana
        return $cita->paciente?->nombre_completo ?? 'Sin paciente';
    }

    /**
     * Obtener saludo apropiado para el destinatario
     */
    protected function obtenerSaludoDestinatario(Cita $cita): string
    {
        if ($cita->mascota_id && $cita->mascota) {
            // Cita veterinaria - dirigirse al propietario/tutor
            $propietario = $cita->mascota->propietario;
            if ($propietario) {
                return "Estimado(a) *{$propietario->nombres}*";
            }
            return "Estimado propietario";
        }

        // Cita humana
        $esMenor = $cita->paciente?->es_menor ?? false;
        $nombre = $cita->paciente?->nombre_completo ?? 'paciente';
        return $esMenor
            ? "Estimado representante de *{$nombre}*"
            : "Estimado(a) *{$nombre}*";
    }

    // ===== RESOLUCIÓN DE DESTINATARIOS =====

    protected function obtenerTelefonosPaciente($pacienteOMascota): array
    {
        $telefonos = [];

        // Determinar si es paciente humano o mascota veterinaria
        if ($pacienteOMascota instanceof \App\Models\Mascota) {
            // Cita veterinaria - obtener teléfono del propietario (tutor/dueño)
            $propietario = $pacienteOMascota->propietario;

            if (!$propietario) {
                Log::warning('CitaNotificationService: Mascota sin propietario registrado', [
                    'mascota_id' => $pacienteOMascota->id
                ]);
                return [];
            }

            if (!empty($propietario->telefono)) {
                $telefonos[] = $this->formatearTelefono($propietario->telefono);
            }

            if (!empty($propietario->telefono_alternativo)) {
                $telefonos[] = $this->formatearTelefono($propietario->telefono_alternativo);
            }

            if (empty($telefonos)) {
                Log::warning('CitaNotificationService: Propietario sin teléfono registrado', [
                    'propietario_id' => $propietario->id,
                    'mascota_id' => $pacienteOMascota->id
                ]);
            }
        } elseif ($pacienteOMascota instanceof \App\Models\Paciente) {
            // Cita humana - usar lógica existente
            if ($pacienteOMascota->es_menor) {
                if (!empty($pacienteOMascota->telefono)) {
                    $telefonos[] = $this->formatearTelefono($pacienteOMascota->telefono);
                }

                $tutorTelefono = optional($pacienteOMascota->tutor)->telefono;
                if (!empty($tutorTelefono)) {
                    $telefonos[] = $this->formatearTelefono($tutorTelefono);
                }

                if (empty($telefonos)) {
                    Log::warning('CitaNotificationService: Paciente menor sin teléfono ni tutor', [
                        'paciente_id' => $pacienteOMascota->id
                    ]);
                }
            } else {
                if (!empty($pacienteOMascota->telefono)) {
                    $telefonos[] = $this->formatearTelefono($pacienteOMascota->telefono);
                }
            }
        } else {
            Log::warning('CitaNotificationService: Tipo de paciente/mascota no reconocido', [
                'tipo' => get_class($pacienteOMascota)
            ]);
        }

        return array_values(array_unique($telefonos));
    }

    // ===== PROGRAMAR MENSAJE EN BD =====

    protected function programarMensaje(Cita $cita, string $telefono, string $mensaje, string $tipo, Carbon $fechaEnvio): void
    {
        try {
            WhatsAppScheduledMessage::firstOrCreate(
                [
                    'cita_id' => $cita->id,
                    'notification_type' => $tipo,
                    'recipient_phone' => $telefono,
                ],
                [
                    'empresa_id' => $cita->empresa_id ?? $this->empresaId,
                    'recipient_name' => $this->obtenerNombreDestinatario($cita),
                    'message_content' => $mensaje,
                    'scheduled_at' => $fechaEnvio,
                    'status' => 'pending',
                    'attempts' => 0,
                    'max_attempts' => 3,
                    'created_by' => auth()->id(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('CitaNotificationService: Error programando mensaje', [
                'cita_id' => $cita->id,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===== ENVÍO DIRECTO (SÍNCRONO VÍA HTTP A NODE.JS) =====

    protected function enviar(string $telefono, string $mensaje): bool
    {
        try {
            if (!$this->apiKey || !$this->empresaId) {
                Log::warning('CitaNotificationService: API Key o Empresa ID no configurados', [
                    'empresa_id' => $this->empresaId
                ]);
                return false;
            }

            // El teléfono ya viene formateado de obtenerTelefonosPaciente()
            // Solo verificamos que tenga el formato correcto
            if (!str_starts_with($telefono, '+')) {
                $telefono = '+' . $telefono;
            }

            $baseUrl = config('whatsapp.api_url', 'http://82.165.213.124:8092');
            $url = "{$baseUrl}/api/whatsapp/send";

            $response = Http::timeout(5) // Timeout corto para no bloquear demasiado
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'X-Company-Id' => (string) $this->empresaId,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'to' => $telefono,
                    'message' => $mensaje,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('CitaNotificationService: Error HTTP enviando mensaje', [
                'status' => $response->status(),
                'body' => $response->body(),
                'telefono' => $telefono
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('CitaNotificationService: Excepción enviando mensaje', [
                'telefono' => $telefono,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ===== CONFIRMACIÓN INTEGRADA =====

    protected function crearConfirmacion(Cita $cita): ?CitaConfirmacion
    {
        try {
            CitaConfirmacion::where('cita_id', $cita->id)
                ->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)
                ->update(['estado' => CitaConfirmacion::ESTADO_SIN_RESPUESTA]);

            $telefono = $cita->paciente->es_menor && $cita->paciente->tutor
                ? $cita->paciente->tutor->telefono
                : $cita->paciente->telefono;

            return CitaConfirmacion::create([
                'cita_id' => $cita->id,
                'metodo' => CitaConfirmacion::METODO_WHATSAPP,
                'destinatario' => $telefono,
                'mensaje_enviado' => 'Incluido en notificación de nueva cita',
                'token_confirmacion' => bin2hex(random_bytes(20)),
                'empresa_id' => $cita->empresa_id,
                'sucursal_id' => $cita->sucursal_id,
                'created_by' => auth()->id(),
                'fecha_envio' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando confirmación integrada', [
                'cita_id' => $cita->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function construirMensajeNuevaCitaConConfirmacion(Cita $cita, CitaConfirmacion $confirmacion): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $saludo = $this->obtenerSaludoDestinatario($cita);
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);

        $urlConfirmar = URL::temporarySignedRoute(
            'citas.confirmar',
            now()->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );

        $urlCancelar = URL::temporarySignedRoute(
            'citas.cancelar',
            now()->addHours(24),
            ['token' => $confirmacion->token_confirmacion]
        );

        $prefijo = $cita->mascota_id ? "🐾 *Nueva Cita Veterinaria*" : "🏥 *Nueva Cita Médica*";

        return "{$prefijo}\n\n"
            . "{$saludo},\n\n"
            . "Se ha agendado una nueva cita:\n\n"
            . "👤 Paciente/Mascota: {$nombreDestinatario}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha}\n"
            . " Hora: {$hora}\n\n"
            . "Por favor confirme su asistencia dentro de las próximas 24 horas:\n\n"
            . "✅ Confirmar: {$urlConfirmar}\n\n"
            . "❌ Cancelar: {$urlCancelar}\n\n"
            . "⏰ Tiene 24 horas para responder.";
    }

    // ===== CONSTRUCCIÓN DE MENSAJES =====

    protected function construirMensajeNuevaCita(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $saludo = $this->obtenerSaludoDestinatario($cita);
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);

        $prefijo = $cita->mascota_id ? "🐾 *Nueva Cita Veterinaria Agendada*" : "🏥 *Nueva Cita Médica Agendada*";
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "{$prefijo}\n\n"
            . "{$saludo},\n\n"
            . "Se ha agendado la siguiente cita:\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . "👨⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "Por favor, llegue 15 minutos antes de su cita.\n"
            . "Si necesita cancelar o reprogramar, comuníquese con nosotros con anticipación.";
    }

    protected function construirMensajeNuevaCitaMedico(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "🏥 *Nueva Cita Agendada*\n\n"
            . "Dr(a). *{$cita->medico->nombre_completo}*, se le informa que tiene una nueva cita programada:\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . " Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "Esta notificación queda como constancia de aviso.";
    }

    protected function construirMensajeCambioEstado(Cita $cita, string $estadoAnterior): string
    {
        $estadoLabels = Cita::ESTADO_LABELS;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return " *Actualización de Cita*\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . "‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha} a las {$hora}\n\n"
            . "📌 Estado anterior: " . ($estadoLabels[$estadoAnterior] ?? $estadoAnterior) . "\n"
            . "✅ Nuevo estado: " . ($estadoLabels[$cita->estado] ?? $cita->estado);
    }

    protected function construirMensajeCambioEstadoMedico(Cita $cita, string $estadoAnterior): string
    {
        $estadoLabels = Cita::ESTADO_LABELS;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "🏥 *Actualización de Cita*\n\n"
            . "Dr(a). *{$cita->medico->nombre_completo}*,\n"
            . "La cita de {$etiqueta} *{$nombreDestinatario}* ha cambiado de estado.\n\n"
            . "📅 Fecha: {$fecha} a las {$hora}\n"
            . "📌 Estado anterior: " . ($estadoLabels[$estadoAnterior] ?? $estadoAnterior) . "\n"
            . "✅ Nuevo estado: " . ($estadoLabels[$cita->estado] ?? $cita->estado);
    }

    protected function construirMensajeConfirmacionPaciente(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $saludo = $this->obtenerSaludoDestinatario($cita);
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "✅ *Cita Confirmada*\n\n"
            . "{$saludo},\n\n"
            . "Su cita ha sido confirmada exitosamente:\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . "‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . " Motivo: {$cita->motivo}\n\n"
            . "Por favor, llegue 15 minutos antes de su cita.\n"
            . "Si necesita cancelar o reprogramar, comuníquese con nosotros.";
    }

    protected function construirMensajeConfirmacionMedico(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $especialidad = $cita->especialidad->nombre ?? '';
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "✅ *Cita Confirmada*\n\n"
            . "Dr(a). *{$cita->medico->nombre_completo}*,\n\n"
            . "La cita ha sido confirmada:\n\n"
            . "👤 {$etiqueta}: *{$nombreDestinatario}*\n"
            . ($especialidad ? "🏥 Especialidad: {$especialidad}\n" : "")
            . "📅 Fecha: {$fecha}\n"
            . " Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "ℹ️ Esta cita está confirmada en su agenda.\n"
            . "El paciente llegará 15 minutos antes de la hora programada.";
    }

    protected function construirMensajeCancelacionMedico(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "🏥 *Cita Cancelada*\n\n"
            . "Dr(a). *{$cita->medico->nombre_completo}*,\n"
            . "Se ha cancelado la siguiente cita:\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . " Fecha: {$fecha} a las {$hora}\n\n"
            . "El horario ha quedado disponible nuevamente.";
    }

    protected function construirMensajeCancelacion(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        return "🏥 *Cita Médica Cancelada*\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . " Fecha: {$fecha} a las {$hora}\n\n"
            . "❌ Su cita ha sido cancelada.\n"
            . "Si desea reprogramar, por favor comuníquese con nosotros.";
    }

    protected function construirMensajeRecordatorio(Cita $cita, string $tiempoRestante = null): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $saludo = $this->obtenerSaludoDestinatario($cita);
        $nombreDestinatario = $this->obtenerNombreDestinatario($cita);
        $etiqueta = $cita->mascota_id ? "Mascota" : "Paciente";

        $tiempoTexto = $tiempoRestante
            ? " Su cita es en aproximadamente *{$tiempoRestante}*."
            : "⏰ Le recordamos que su cita es pronto.";

        return "🏥 *Recordatorio de Cita*\n\n"
            . "{$saludo},\n\n"
            . "👤 {$etiqueta}: {$nombreDestinatario}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . " Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "{$tiempoTexto}\n"
            . "Por favor, llegue 15 minutos antes.";
    }


    protected function construirMensajeRecordatorioConPreconsulta(Cita $cita, string $tiempoRestante = null): string
    {
        $base = $this->construirMensajeRecordatorio($cita, $tiempoRestante);
        $link = route('preconsulta.formulario', ['token' => $cita->token_preconsulta]);

        return $base
            . "\n\n────────────────────\n"
            . "Para agilizar su atencion, complete el cuestionario de pre-consulta antes de su cita:\n\n"
            . $link . "\n\n"
            . "Es confidencial y nos ayudara a brindarle mejor atencion.";
    }



}
