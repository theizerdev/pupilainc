<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'programada' => true,           // ✅ Enviar confirmación inicial (con mensaje específico)
            'confirmada' => true,           // ✅ Enviar confirmación explícita
            'sala_espera' => true,          // ✅ Enviar bienvenida al llegar
            'en_enfermeria' => false,       // ❌ No enviar (interno)
            'en_consultorio' => false,      // ❌ No enviar (interno)
            'en_consultorio_optometrista' => false, // ❌ No enviar (interno)
            'en_gotas' => false,            // ❌ No enviar (interno)
            'dilatado' => false,            // ❌ No enviar (interno)
            'en_optica' => false,           // ❌ No enviar (interno)
            'en_estudio' => false,          // ❌ No enviar (interno)
            'finalizada' => true,           // ✅ Enviar agradecimiento
            'pagada' => true,               // ✅ Enviar comprobante de pago
            'cancelada' => true,            // ✅ Enviar cancelación
            'no_asistio' => true,           // ✅ Enviar seguimiento post-no-asistencia
        ];

        // Configuración recomendada para doctores
        $configDoctor = [
            'programada' => true,           // ✅ Nueva cita agendada
            'confirmada' => true,           // ✅ Paciente confirmó asistencia
            'sala_espera' => true,          // ✅ Paciente llegó (importante)
            'en_enfermeria' => false,       // ❌ No enviar
            'en_consultorio' => false,      //  No enviar (ya está presente)
            'en_consultorio_optometrista' => false, // ❌ No enviar
            'en_gotas' => false,            // ❌ No enviar
            'dilatado' => false,            // ❌ No enviar
            'en_optica' => false,           // ❌ No enviar
            'en_estudio' => false,          // ❌ No enviar
            'finalizada' => true,           // ✅ Consulta finalizada
            'pagada' => true,               // ✅ Pago registrado
            'cancelada' => true,            // ✅ Liberar agenda
            'no_asistio' => true,           // ✅ Paciente no asistió
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
            }
        }
    }
}
