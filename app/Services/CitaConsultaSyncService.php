<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\CitaAuditoria;
use App\Models\LogCitasSolapadas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CitaConsultaSyncService
{
    protected array $notificaciones = [];
    protected array $auditoria = [];
    protected bool $rollbackOnError = false;

    public function __construct(bool $rollbackOnError = true)
    {
        $this->rollbackOnError = $rollbackOnError;
    }

    /**
     * Reagenda una cita y sincroniza la consulta asociada
     */
    public function reagendarSincronizado(Cita $cita, Carbon $nuevaFechaHora, Carbon $nuevaFechaFin, ?string $nota = null, bool $permitirSolapamiento = false): array
    {
        $resultado = [
            'success' => false,
            'cita' => null,
            'consulta' => null,
            'notificaciones' => [],
            'auditoria' => [],
            'errores' => [],
            'solapamiento' => false,
        ];

        DB::beginTransaction();

        try {
            // 1. Obtener consulta asociada si existe
            $consulta = $cita->consulta;
            $citaOriginal = $this->cloneCitaForAudit($cita);

            // 2. Validar conflictos de horario
            $permitirSolapamiento = $permitirSolapamiento || in_array($cita->prioridad ?? 'normal', ['alta', 'emergencia']);
            $resultadoConflictos = $this->validarConflictos($cita, $nuevaFechaHora, $nuevaFechaFin, $permitirSolapamiento);
            if (!empty($resultadoConflictos['conflictos'])) {
                throw new \Exception('Conflictos de horario detectados: ' . implode(', ', $resultadoConflictos['conflictos']));
            }
            if ($resultadoConflictos['cita_conflicto']) {
                $resultado['solapamiento'] = true;
            }

            // 3. Actualizar cita
            $fechaFin = $nuevaFechaHora->copy()->addMinutes($cita->fecha_inicio->diffInMinutes($cita->fecha_fin));
            $citaAnterior = $cita->replicate();
            
            $cita->update([
                'fecha_inicio' => $nuevaFechaHora,
                'fecha_fin' => $nuevaFechaFin,
                'nota_reagendamiento' => $nota,
            ]);

            // 4. Sincronizar consulta si existe
            if ($consulta) {
                $this->sincronizarConsulta($consulta, $nuevaFechaHora, $nuevaFechaFin);
            }

            // 5. Registrar auditoría
            $this->registrarAuditoria($cita, [
                'tipo' => 'reagendamiento',
                'cita_anterior' => $citaAnterior,
                'cita_nueva' => $cita->fresh(),
                'consulta_actualizada' => $consulta ? $consulta->fresh() : null,
                'nota' => $nota,
            ]);

            // 6. Generar notificaciones
            $this->generarNotificaciones($cita, $consulta ?? null, $nuevaFechaHora);

            DB::commit();

            $resultado['success'] = true;
            $resultado['cita'] = $cita->fresh();
            $resultado['consulta'] = $consulta ? $consulta->fresh() : null;
            $resultado['notificaciones'] = $this->notificaciones;
            $resultado['auditoria'] = $this->auditoria;

        } catch (\Exception $e) {
            if ($this->rollbackOnError) {
                DB::rollBack();
            }
            
            $resultado['errores'][] = $e->getMessage();
            Log::error('Error en reagendamiento sincronizado', [
                'cita_id' => $cita->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $resultado;
    }

    /**
     * Sincroniza la fecha/hora de la consulta con la cita
     */
    protected function sincronizarConsulta(Consulta $consulta, Carbon $nuevaFechaHora): void
    {
        $consulta->update([
            'fecha_consulta' => $nuevaFechaHora,
            'estado_changed_at' => now(),
        ]);
    }

    /**
     * Valida que no haya conflictos de horario
     * Retorna array con 'conflictos' (array de strings) y 'cita_conflicto' (Cita o null)
     */
    public function validarConflictos(Cita $cita, Carbon $fechaHora, Carbon $nuevaFechaFin, bool $permitirSolapamiento = false): array
    {
        $resultado = ['conflictos' => [], 'cita_conflicto' => null];

        $fechaFin = $fechaHora->copy()->addMinutes(
            $cita->fecha_inicio->diffInMinutes($cita->fecha_fin)
        );

        // Verificar conflictos con otras citas del mismo médico
        $citasConflicto = Cita::where('medico_id', $cita->medico_id)
            ->where('id', '!=', $cita->id)
            ->whereIn('estado', [Cita::ESTADO_CONFIRMADA, Cita::ESTADO_PENDIENTE])
            ->where(function ($query) use ($fechaHora, $nuevaFechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaHora, $nuevaFechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaHora, $nuevaFechaFin])
                    ->orWhere(function ($q) use ($fechaHora, $nuevaFechaFin) {
                        $q->where('fecha_inicio', '<=', $fechaHora)
                          ->where('fecha_fin', '>=', $nuevaFechaFin);
                    });
            })
            ->first();

        if ($citasConflicto) {
            if ($permitirSolapamiento) {
                $this->registrarSolapamiento($cita, $citasConflicto, $cita->prioridad ?? 'normal');
                $resultado['cita_conflicto'] = $citasConflicto;
            } else {
                $resultado['conflictos'][] = "El médico tiene otra cita en ese horario";
            }
        }

        // Verificar conflictos con consultas del paciente
        $consultasConflicto = Consulta::where('paciente_id', $cita->paciente_id)
            ->where('id', '!=', $cita->consulta?->id)
            ->whereIn('estado', [
                Consulta::ESTADO_SALA_ESPERA,
                Consulta::ESTADO_EN_ENFERMERIA,
                Consulta::ESTADO_EN_CONSULTORIO,
                Consulta::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
                Consulta::ESTADO_EN_GOTAS,
                Consulta::ESTADO_EN_OPTICA,
                Consulta::ESTADO_EN_ESTUDIO,
            ])
            ->whereBetween('fecha_consulta', [$fechaHora, $fechaFin])
            ->exists();

        if ($consultasConflicto) {
            $resultado['conflictos'][] = "El paciente tiene otra consulta en ese horario";
        }

        return $resultado;
    }

    /**
     * Registra solapamiento entre citas
     */
    protected function registrarSolapamiento(Cita $citaNueva, Cita $citaOriginal, string $prioridad): void
    {
        LogCitasSolapadas::create([
            'usuario_id' => auth()->id(),
            'cita_original_id' => $citaOriginal->id,
            'cita_nueva_id' => $citaNueva->id,
            'tipo_prioridad' => $prioridad,
            'observacion' => "Cita {$prioridad} reagendada solapando con cita #{$citaOriginal->id}",
            'created_at' => now(),
        ]);
    }

    /**
     * Clona la cita para auditoría
     */
    protected function cloneCitaForAudit(Cita $cita): array
    {
        return [
            'id' => $cita->id,
            'fecha_inicio' => $cita->fecha_inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $cita->fecha_fin->format('Y-m-d H:i:s'),
            'estado' => $cita->estado,
            'paciente_id' => $cita->paciente_id,
            'medico_id' => $cita->medico_id,
            'created_at' => $cita->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Registra la auditoría del cambio
     */
    protected function registrarAuditoria(Cita $cita, array $datos): void
    {
        $auditoria = [
            'cita_id' => $cita->id,
            'empresa_id' => $cita->empresa_id,
            'sucursal_id' => $cita->sucursal_id,
            'usuario_id' => auth()->id(),
            'tipo' => $datos['tipo'],
            'datos_anteriores' => json_encode($datos['cita_anterior']),
            'datos_nuevos' => json_encode([
                'fecha_inicio' => $datos['cita_nueva']->fecha_inicio->format('Y-m-d H:i:s'),
                'fecha_fin' => $datos['cita_nueva']->fecha_fin->format('Y-m-d H:i:s'),
            ]),
            'nota' => $datos['nota'] ?? null,
            'created_at' => now(),
        ];

        CitaAuditoria::create($auditoria);
        
        $this->auditoria[] = $auditoria;
    }

    /**
     * Genera las notificaciones para el reagendamiento
     */
    protected function generarNotificaciones(Cita $cita, ?Consulta $consulta, Carbon $nuevaFecha): void
    {
        // Notificación para el paciente
        $this->notificaciones[] = [
            'tipo' => 'paciente',
            'canal' => 'whatsapp',
            'destinatario' => $cita->paciente?->telefono,
            'mensaje' => "Su cita ha sido reagendada para el {$nuevaFecha->format('d/m/Y')} a las {$nuevaFecha->format('H:i')}.",
        ];

        // Notificación para el médico
        $this->notificaciones[] = [
            'tipo' => 'medico',
            'canal' => 'sistema',
            'destinatario' => $cita->medico?->user?->email,
            'mensaje' => "Cita del paciente {$cita->paciente?->nombre_completo} ha sido reagendada para {$nuevaFecha->format('d/m/Y H:i')}.",
        ];

        // Notificación si hay consulta asociada
        if ($consulta) {
            $this->notificaciones[] = [
                'tipo' => 'consulta_sync',
                'canal' => 'sistema',
                'mensaje' => "Consulta #{$consulta->id} sincronizada con cita re-agendada.",
            ];
        }
    }

    /**
     * Obtiene el estado de sincronización entre cita y consulta
     */
    public function getEstadoSincronizacion(Cita $cita): array
    {
        $consulta = $cita->consulta;
        
        if (!$consulta) {
            return [
                'sincronizado' => true,
                'tiene_consulta' => false,
                'mensaje' => 'La cita no tiene consulta asociada',
            ];
        }

        $fechaCita = $cita->fecha_inicio->format('Y-m-d H:i:s');
        $fechaConsulta = $consulta->fecha_consulta->format('Y-m-d H:i:s');
        
        $sincronizado = $fechaCita === $fechaConsulta;

        return [
            'sincronizado' => $sincronizado,
            'tiene_consulta' => true,
            'cita_fecha' => $fechaCita,
            'consulta_fecha' => $fechaConsulta,
            'diferencia_minutos' => $sincronizado ? 0 : abs($cita->fecha_inicio->diffInMinutes($consulta->fecha_consulta)),
            'mensaje' => $sincronizado 
                ? 'Citas sincronizadas correctamente'
                : 'Las fechas no están sincronizadas',
        ];
    }
}