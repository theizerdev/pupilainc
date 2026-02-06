<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\WhatsAppScheduledMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CitaNotificationService
{
    protected WhatsAppService $whatsApp;
    protected ?int $empresaId;
    protected ?string $codigoPais = null;

    public function __construct(?int $empresaId = null)
    {
        $this->empresaId = $empresaId;
        $this->whatsApp = $empresaId
            ? WhatsAppService::forCompany($empresaId)
            : new WhatsAppService();
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

        $this->codigoPais = '58';

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

        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = $this->obtenerCodigoPais();

        if (!str_starts_with($limpio, $codigo) && strlen($limpio) >= 7 && strlen($limpio) <= 12) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    // ===== NOTIFICACIONES AL CREAR CITA =====

    public function notificarNuevaCita(Cita $cita): bool
    {
        $cita->loadMissing(['paciente.tutor', 'medico']);

        $telefonos = $this->obtenerTelefonosPaciente($cita->paciente);
        $mensajePaciente = $this->construirMensajeNuevaCita($cita);
        $resultado = false;

        foreach ($telefonos as $telefono) {
            if ($this->enviar($telefono, $mensajePaciente)) {
                $resultado = true;
            }
        }

        if ($cita->medico && $cita->medico->telefono) {
            $mensajeMedico = $this->construirMensajeNuevaCitaMedico($cita);
            $telefonoMedico = $this->formatearTelefono($cita->medico->telefono);
            if ($this->enviar($telefonoMedico, $mensajeMedico)) {
                $resultado = true;
            }
        }

        $this->programarRecordatorios($cita);

        return $resultado;
    }

    // ===== PROGRAMAR RECORDATORIOS =====

    public function programarRecordatorios(Cita $cita): void
    {
        $cita->loadMissing(['paciente.tutor', 'medico']);

        $telefonos = $this->obtenerTelefonosPaciente($cita->paciente);
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

            $mensaje = $this->construirMensajeRecordatorio($cita, $etiquetas[$tipo]);

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

        $telefonos = $this->obtenerTelefonosPaciente($cita->paciente);
        if (empty($telefonos)) return false;

        $mensaje = $this->construirMensajeCambioEstado($cita, $estadoAnterior);
        $resultado = false;

        foreach ($telefonos as $telefono) {
            $resultado = $this->enviar($telefono, $mensaje) || $resultado;
        }

        return $resultado;
    }

    public function notificarCancelacion(Cita $cita): bool
    {
        $cita->loadMissing(['paciente.tutor', 'medico']);

        $this->cancelarRecordatoriosPendientes($cita);

        $telefonos = $this->obtenerTelefonosPaciente($cita->paciente);
        if (empty($telefonos)) return false;

        $mensaje = $this->construirMensajeCancelacion($cita);
        $resultado = false;

        foreach ($telefonos as $telefono) {
            $resultado = $this->enviar($telefono, $mensaje) || $resultado;
        }

        return $resultado;
    }

    public function enviarRecordatorio(Cita $cita): bool
    {
        $cita->loadMissing(['paciente.tutor', 'medico']);

        $telefonos = $this->obtenerTelefonosPaciente($cita->paciente);
        if (empty($telefonos)) return false;

        $mensaje = $this->construirMensajeRecordatorio($cita);
        $resultado = false;

        foreach ($telefonos as $telefono) {
            $resultado = $this->enviar($telefono, $mensaje) || $resultado;
        }

        return $resultado;
    }

    // ===== RESOLUCIÓN DE DESTINATARIOS =====

    protected function obtenerTelefonosPaciente(Paciente $paciente): array
    {
        $telefonos = [];

        if ($paciente->es_menor) {
            if (!empty($paciente->telefono)) {
                $telefonos[] = $this->formatearTelefono($paciente->telefono);
            }

            $tutorTelefono = optional($paciente->tutor)->telefono;
            if (!empty($tutorTelefono)) {
                $telefonos[] = $this->formatearTelefono($tutorTelefono);
            }

            if (empty($telefonos)) {
                Log::warning('CitaNotificationService: Paciente menor sin teléfono ni tutor', [
                    'paciente_id' => $paciente->id
                ]);
            }
        } else {
            if (!empty($paciente->telefono)) {
                $telefonos[] = $this->formatearTelefono($paciente->telefono);
            }
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
                    'recipient_name' => $cita->paciente->nombre_completo,
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

    // ===== ENVÍO DIRECTO =====

    protected function enviar(string $telefono, string $mensaje): bool
    {
        try {
            if (!$this->whatsApp->isConfigured()) {
                Log::warning('CitaNotificationService: WhatsApp no configurado');
                return false;
            }

            $telefonoFormateado = $this->formatearTelefono($telefono);
            $result = $this->whatsApp->sendMessage($telefonoFormateado, $mensaje);
            return $result !== null;
        } catch (\Exception $e) {
            Log::error('CitaNotificationService: Error enviando mensaje', [
                'telefono' => $telefono,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ===== CONSTRUCCIÓN DE MENSAJES =====

    protected function construirMensajeNuevaCita(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $esMenor = $cita->paciente->es_menor;
        $saludo = $esMenor ? "Estimado representante de *{$cita->paciente->nombre_completo}*" : "Estimado(a) *{$cita->paciente->nombre_completo}*";

        return "🏥 *Nueva Cita Médica Agendada*\n\n"
            . "{$saludo},\n\n"
            . "Se ha agendado la siguiente cita:\n\n"
            . "👤 Paciente: {$cita->paciente->nombre_completo}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
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

        return "🏥 *Nueva Cita Agendada*\n\n"
            . "Dr(a). *{$cita->medico->nombre_completo}*, se le informa que tiene una nueva cita programada:\n\n"
            . "👤 Paciente: {$cita->paciente->nombre_completo}\n"
            . "📅 Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "Esta notificación queda como constancia de aviso.";
    }

    protected function construirMensajeCambioEstado(Cita $cita, string $estadoAnterior): string
    {
        $estadoLabels = Cita::ESTADO_LABELS;
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');

        return "🏥 *Actualización de Cita Médica*\n\n"
            . "👤 Paciente: {$cita->paciente->nombre_completo}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha} a las {$hora}\n\n"
            . "📌 Estado anterior: " . ($estadoLabels[$estadoAnterior] ?? $estadoAnterior) . "\n"
            . "✅ Nuevo estado: " . ($estadoLabels[$cita->estado] ?? $cita->estado);
    }

    protected function construirMensajeCancelacion(Cita $cita): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');

        return "🏥 *Cita Médica Cancelada*\n\n"
            . "👤 Paciente: {$cita->paciente->nombre_completo}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha} a las {$hora}\n\n"
            . "❌ Su cita ha sido cancelada.\n"
            . "Si desea reprogramar, por favor comuníquese con nosotros.";
    }

    protected function construirMensajeRecordatorio(Cita $cita, string $tiempoRestante = null): string
    {
        $fecha = $cita->fecha_inicio->format('d/m/Y');
        $hora = $cita->fecha_inicio->format('h:i A');
        $esMenor = $cita->paciente->es_menor;
        $saludo = $esMenor ? "Estimado representante de *{$cita->paciente->nombre_completo}*" : "Estimado(a) *{$cita->paciente->nombre_completo}*";

        $tiempoTexto = $tiempoRestante
            ? "⏰ Su cita es en aproximadamente *{$tiempoRestante}*."
            : "⏰ Le recordamos que su cita es pronto.";

        return "🏥 *Recordatorio de Cita Médica*\n\n"
            . "{$saludo},\n\n"
            . "👤 Paciente: {$cita->paciente->nombre_completo}\n"
            . "👨‍⚕️ Médico: Dr(a). {$cita->medico->nombre_completo}\n"
            . "📅 Fecha: {$fecha}\n"
            . "🕐 Hora: {$hora}\n"
            . "📋 Motivo: {$cita->motivo}\n\n"
            . "{$tiempoTexto}\n"
            . "Por favor, llegue 15 minutos antes.";
    }
}
