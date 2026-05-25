<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ConfiguracionNotificacion extends Model
{
    use HasFactory;

    protected $table = 'configuracion_notificaciones';

    protected $fillable = [
        'empresa_id',
        'tipo_destinatario',
        'estado_cita',
        'enviar_notificacion',
    ];

    protected $casts = [
        'enviar_notificacion' => 'boolean',
    ];

    /**
     * Relación con la empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Scope para filtrar por tipo de destinatario
     */
    public function scopePorDestinatario($query, $tipo)
    {
        return $query->where('tipo_destinatario', $tipo);
    }

    /**
     * Verificar si se debe enviar notificación para un estado específico
     */
    public static function debeEnviar(int $empresaId, string $tipoDestinatario, string $estadoCita): bool
    {
        if (Schema::hasTable('whatsapp_notification_settings')) {
            $setting = WhatsAppNotificationSetting::where('empresa_id', $empresaId)
                ->where('module_key', 'citas')
                ->where('action_key', 'estado_'.$estadoCita)
                ->where('recipient_key', $tipoDestinatario)
                ->first();

            if ($setting) {
                return $setting->enabled;
            }
        }

        $config = self::where('empresa_id', $empresaId)
            ->where('tipo_destinatario', $tipoDestinatario)
            ->where('estado_cita', $estadoCita)
            ->first();

        // Si no hay configuración, usar valores por defecto
        if (!$config) {
            return self::valorPorDefecto($tipoDestinatario, $estadoCita);
        }

        return $config->enviar_notificacion;
    }

    /**
     * Valores por defecto según mejores prácticas
     */
    private static function valorPorDefecto(string $tipoDestinatario, string $estadoCita): bool
    {
        // Configuración recomendada para pacientes
        $configPaciente = [
            'confirmada' => true,           // ✅ Enviar confirmación
            'sala_espera' => false,         // ❌ No enviar (interno)
            'en_enfermeria' => false,       // ❌ No enviar (interno)
            'en_consultorio' => false,      // ❌ No enviar (interno)
            'en_consultorio_optometrista' => false, // ❌ No enviar (interno)
            'en_gotas' => false,            // ❌ No enviar (interno)
            'dilatado' => false,            // ❌ No enviar (interno)
            'en_optica' => false,           // ❌ No enviar (interno)
            'en_estudio' => false,          // ❌ No enviar (interno)
            'finalizada' => false,          // ❌ No enviar
            'pagada' => true,               // ✅ Enviar confirmación de pago
            'cancelada' => true,            // ✅ Enviar cancelación
            'no_asistio' => false,          // ❌ No enviar
        ];

        // Configuración recomendada para doctores
        $configDoctor = [
            'confirmada' => true,           // ✅ Agenda confirmada
            'sala_espera' => true,          // ⚠️ Paciente llegó (opcional)
            'en_enfermeria' => false,       // ❌ No enviar
            'en_consultorio' => false,      // ❌ No enviar (ya está presente)
            'en_consultorio_optometrista' => false, // ❌ No enviar
            'en_gotas' => false,            // ❌ No enviar
            'dilatado' => false,            // ❌ No enviar
            'en_optica' => false,           // ❌ No enviar
            'en_estudio' => false,          // ❌ No enviar
            'finalizada' => false,          // ❌ No enviar
            'pagada' => false,              // ❌ No enviar (administrativo)
            'cancelada' => true,            // ✅ Liberar agenda
            'no_asistio' => false,          // ❌ No enviar
        ];

        if ($tipoDestinatario === 'paciente') {
            return $configPaciente[$estadoCita] ?? false;
        } elseif ($tipoDestinatario === 'doctor') {
            return $configDoctor[$estadoCita] ?? false;
        }

        return false;
    }

    /**
     * Obtener toda la configuración para una empresa
     */
    public static function obtenerConfiguracionEmpresa(int $empresaId): array
    {
        $config = self::porEmpresa($empresaId)->get();

        $resultado = [
            'paciente' => [],
            'doctor' => [],
        ];

        foreach ($config as $item) {
            $resultado[$item->tipo_destinatario][$item->estado_cita] = $item->enviar_notificacion;
        }

        if (Schema::hasTable('whatsapp_notification_settings')) {
            WhatsAppNotificationSetting::where('empresa_id', $empresaId)
                ->where('module_key', 'citas')
                ->where('action_key', 'like', 'estado_%')
                ->get()
                ->each(function ($item) use (&$resultado) {
                    $estado = substr($item->action_key, strlen('estado_'));
                    $resultado[$item->recipient_key][$estado] = $item->enabled;
                });
        }

        // Rellenar con valores por defecto los que faltan
        $estados = array_keys((new \App\Models\Cita)::ESTADO_LABELS);

        foreach ($estados as $estado) {
            if (!isset($resultado['paciente'][$estado])) {
                $resultado['paciente'][$estado] = self::valorPorDefecto('paciente', $estado);
            }
            if (!isset($resultado['doctor'][$estado])) {
                $resultado['doctor'][$estado] = self::valorPorDefecto('doctor', $estado);
            }
        }

        return $resultado;
    }

    /**
     * Guardar configuración para una empresa
     */
    public static function guardarConfiguracion(int $empresaId, array $configuracion): void
    {
        foreach ($configuracion as $tipoDestinatario => $estados) {
            foreach ($estados as $estado => $enviar) {
                self::updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'tipo_destinatario' => $tipoDestinatario,
                        'estado_cita' => $estado,
                    ],
                    [
                        'enviar_notificacion' => $enviar,
                    ]
                );

                if (Schema::hasTable('whatsapp_notification_settings')) {
                    WhatsAppNotificationSetting::setValue(
                        $empresaId,
                        'citas',
                        'estado_'.$estado,
                        $tipoDestinatario,
                        (bool) $enviar
                    );
                }
            }
        }
    }
}
