<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\MedicoHorario;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CitaReagendamientoService
{
    protected ?int $empresaId;
    protected ?int $sucursalId;
    protected int $diasMaximosBusqueda = 30;
    protected int $horasAnticipacion = 24;

    public function __construct(?int $empresaId = null, ?int $sucursalId = null)
    {
        $this->empresaId = $empresaId;
        $this->sucursalId = $sucursalId;
    }

    public static function forCompany(?int $empresaId = null, ?int $sucursalId = null): self
    {
        return new self($empresaId, $sucursalId);
    }

    /**
     * Busca horarios disponibles para re-agendar una cita
     */
    public function buscarHorariosDisponibles(Cita $cita, int $diasBusqueda = 7): array
    {
        $horariosDisponibles = [];
        $fechaInicio = now()->addHours($this->horasAnticipacion);
        $fechaFin = now()->addDays($diasBusqueda);

        // Obtener horarios del médico
        $medico = $cita->medico;
        $duracionCita = $cita->fecha_inicio->diffInMinutes($cita->fecha_fin);

        // Buscar día por día
        $fechaActual = $fechaInicio->copy();
        
        while ($fechaActual <= $fechaFin && count($horariosDisponibles) < 10) {
            $horariosDelDia = $this->buscarHorariosDia($medico, $fechaActual, $duracionCita);
            
            if (!empty($horariosDelDia)) {
                $horariosDisponibles = array_merge($horariosDisponibles, $horariosDelDia);
            }
            
            $fechaActual->addDay();
        }

        return array_slice($horariosDisponibles, 0, 10);
    }

    /**
     * Re-agenda automáticamente una cita al primer horario disponible
     */
    public function reagendarAutomaticamente(Cita $cita): ?Cita
    {
        $horariosDisponibles = $this->buscarHorariosDisponibles($cita);

        if (empty($horariosDisponibles)) {
            return null;
        }

        // Tomar el primer horario disponible
        $primerHorario = $horariosDisponibles[0];
        
        return $this->crearNuevaCita($cita, $primerHorario);
    }

    /**
     * Sugiere re-agendamiento para citas canceladas o no asistidas
     */
    public function sugerirReagendamiento(Cita $cita): array
    {
        if (!in_array($cita->estado, [Cita::ESTADO_CANCELADA, Cita::ESTADO_NO_ASISTIO])) {
            return [];
        }

        $horarios = $this->buscarHorariosDisponibles($cita);
        
        return [
            'cita_original' => [
                'id' => $cita->id,
                'fecha' => $cita->fecha_inicio->format('d/m/Y H:i'),
                'medico' => $cita->medico->nombre_completo,
                'estado' => $cita->estado,
            ],
            'horarios_disponibles' => $horarios,
            'mensaje' => $this->generarMensajeReagendamiento($cita, count($horarios)),
        ];
    }

    /**
     * Procesa re-agendamientos masivos para citas canceladas
     */
    public function procesarReagendamientosMasivos(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $citasParaReagendar = Cita::whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
            ->whereIn('estado', [Cita::ESTADO_CANCELADA, Cita::ESTADO_NO_ASISTIO])
            ->get();

        $resultados = [
            'total_procesadas' => 0,
            'exitosas' => 0,
            'fallidas' => 0,
            'detalles' => [],
        ];

        foreach ($citasParaReagendar as $cita) {
            $resultados['total_procesadas']++;
            
            try {
                $nuevaCita = $this->reagendarAutomaticamente($cita);
                
                if ($nuevaCita) {
                    $resultados['exitosas']++;
                    $resultados['detalles'][] = [
                        'cita_id' => $cita->id,
                        'nueva_cita_id' => $nuevaCita->id,
                        'nueva_fecha' => $nuevaCita->fecha_inicio->format('d/m/Y H:i'),
                        'estado' => 'exitosa',
                    ];
                } else {
                    $resultados['fallidas']++;
                    $resultados['detalles'][] = [
                        'cita_id' => $cita->id,
                        'error' => 'No se encontraron horarios disponibles',
                        'estado' => 'fallida',
                    ];
                }
            } catch (\Exception $e) {
                $resultados['fallidas']++;
                $resultados['detalles'][] = [
                    'cita_id' => $cita->id,
                    'error' => $e->getMessage(),
                    'estado' => 'error',
                ];
            }
        }

        return $resultados;
    }

    /**
     * Busca horarios disponibles en un día específico
     */
    private function buscarHorariosDia(Medico $medico, Carbon $fecha, int $duracionMinutos): array
    {
        $horariosDisponibles = [];
        
        // Obtener horario de trabajo del médico para este día
        $diaSemana = $fecha->dayOfWeekIso;
        $horarioMedico = MedicoHorario::where('medico_id', $medico->id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->first();

        if (!$horarioMedico) {
            return $horariosDisponibles;
        }

        // Convertir horas a Carbon
        $horaInicio = $fecha->copy()->setTimeFromTimeString($horarioMedico->hora_inicio);
        $horaFin = $fecha->copy()->setTimeFromTimeString($horarioMedico->hora_fin);
        
        // Generar slots de tiempo
        $intervalo = $horarioMedico->duracion_cita ?? 30; // minutos por defecto
        $horaActual = $horaInicio->copy();

        while ($horaActual < $horaFin) {
            $horaFinSlot = $horaActual->copy()->addMinutes($duracionMinutos);
            
            if ($horaFinSlot <= $horaFin) {
                // Verificar disponibilidad
                if ($this->verificarDisponibilidad($medico, $horaActual, $horaFinSlot)) {
                    $horariosDisponibles[] = [
                        'fecha_hora' => $horaActual->format('Y-m-d H:i:s'),
                        'fecha_formateada' => $horaActual->format('d/m/Y H:i'),
                        'duracion' => $duracionMinutos,
                    ];
                }
            }
            
            $horaActual->addMinutes($intervalo);
        }

        return $horariosDisponibles;
    }

    /**
     * Verifica si un horario está disponible
     */
    private function verificarDisponibilidad(Medico $medico, Carbon $inicio, Carbon $fin): bool
    {
        // Verificar que no haya citas existentes
        $conflicto = Cita::where('medico_id', $medico->id)
            ->where('fecha_inicio', '<', $fin)
            ->where('fecha_fin', '>', $inicio)
            ->whereNotIn('estado', [Cita::ESTADO_CANCELADA, Cita::ESTADO_NO_ASISTIO])
            ->exists();

        if ($conflicto) {
            return false;
        }

        // Verificar que sea en el futuro
        if ($inicio <= now()->addHours($this->horasAnticipacion)) {
            return false;
        }

        return true;
    }

    /**
     * Crea una nueva cita basada en una cita anterior
     */
    public function crearNuevaCita(Cita $citaAnterior, array $nuevoHorario): Cita
    {
        $nuevaFecha = Carbon::parse($nuevoHorario['fecha_hora']);
        $duracion = $citaAnterior->fecha_inicio->diffInMinutes($citaAnterior->fecha_fin);
        
        return Cita::create([
            'paciente_id' => $citaAnterior->paciente_id,
            'medico_id' => $citaAnterior->medico_id,
            'especialidad_id' => $citaAnterior->especialidad_id,
            'subespecialidad_id' => $citaAnterior->subespecialidad_id,
            'empresa_id' => $citaAnterior->empresa_id,
            'sucursal_id' => $citaAnterior->sucursal_id,
            'fecha_inicio' => $nuevaFecha,
            'fecha_fin' => $nuevaFecha->copy()->addMinutes($duracion),
            'motivo' => $citaAnterior->motivo . ' [Re-agendada]',
            'estado' => Cita::ESTADO_PENDIENTE,
            'notas' => "Cita re-agendada automáticamente. Cita original: {$citaAnterior->fecha_inicio->format('d/m/Y H:i')}",
            'created_by' => auth()->id() ?? 1, // Sistema o usuario autenticado
        ]);
    }

    /**
     * Genera mensaje para el re-agendamiento
     */
    private function generarMensajeReagendamiento(Cita $cita, int $totalHorarios): string
    {
        if ($totalHorarios === 0) {
            return "No se encontraron horarios disponibles para re-agendar la cita con {$cita->medico->nombre_completo}.";
        }

        return "Se encontraron {$totalHorarios} horarios disponibles para re-agendar su cita con {$cita->medico->nombre_completo}.";
    }
}