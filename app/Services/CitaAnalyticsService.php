<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CitaAnalyticsService
{
    protected ?int $empresaId;
    protected ?int $sucursalId;

    public function __construct(?int $empresaId = null, ?int $sucursalId = null)
    {
        $this->empresaId = $empresaId;
        $this->sucursalId = $sucursalId;
    }

    public static function forCompany(?int $empresaId = null, ?int $sucursalId = null): self
    {
        return new self($empresaId, $sucursalId);
    }

    public function getDashboardData(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return [
            'resumen' => $this->getResumenCitas($fechaInicio, $fechaFin),
            'estados' => $this->getDistribucionEstados($fechaInicio, $fechaFin),
            'medicos' => $this->getTopMedicos($fechaInicio, $fechaFin),
            'especialidades' => $this->getTopEspecialidades($fechaInicio, $fechaFin),
            'tendencias' => $this->getTendenciasSemanales($fechaInicio, $fechaFin),
            'cancelaciones' => $this->getAnalisisCancelaciones($fechaInicio, $fechaFin),
            'asistencia' => $this->getTasaAsistencia($fechaInicio, $fechaFin),
            'tiempo_promedio' => $this->getTiempoPromedioEspera($fechaInicio, $fechaFin),
        ];
    }

    public function getResumenCitas(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $query = $this->getBaseQuery($fechaInicio, $fechaFin);

        $total = $query->count();
        $confirmadas = (clone $query)->where('estado', Cita::ESTADO_CONFIRMADA)->count();
        $completadas = (clone $query)->where('estado', Cita::ESTADO_COMPLETADA)->count();
        $canceladas = (clone $query)->where('estado', Cita::ESTADO_CANCELADA)->count();
        $noAsistidas = (clone $query)->where('estado', Cita::ESTADO_NO_ASISTIO)->count();

        // Calcular crecimiento vs período anterior
        $diasPeriodo = $fechaInicio->diffInDays($fechaFin);
        $periodoAnteriorInicio = (clone $fechaInicio)->subDays($diasPeriodo + 1);
        $periodoAnteriorFin = (clone $fechaInicio)->subDay();
        
        $totalAnterior = $this->getBaseQuery($periodoAnteriorInicio, $periodoAnteriorFin)->count();
        $porcentajeCrecimiento = $totalAnterior > 0 ? (($total - $totalAnterior) / $totalAnterior) * 100 : 0;

        return [
            'total' => $total,
            'confirmadas' => $confirmadas,
            'completadas' => $completadas,
            'canceladas' => $canceladas,
            'no_asistidas' => $noAsistidas,
            'tasa_confirmacion' => $total > 0 ? round(($confirmadas / $total) * 100, 2) : 0,
            'tasa_completitud' => $total > 0 ? round(($completadas / $total) * 100, 2) : 0,
            'tasa_cancelacion' => $total > 0 ? round(($canceladas / $total) * 100, 2) : 0,
            'porcentaje_crecimiento' => round($porcentajeCrecimiento, 1),
        ];
    }

    public function getDistribucionEstados(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return $this->getBaseQuery($fechaInicio, $fechaFin)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'estado' => $item->estado,
                    'label' => Cita::ESTADO_LABELS[$item->estado] ?? $item->estado,
                    'total' => $item->total,
                    'color' => Cita::ESTADO_COLORES[$item->estado] ?? 'secondary',
                ];
            })
            ->toArray();
    }

    public function getTopMedicos(Carbon $fechaInicio, Carbon $fechaFin, int $limit = 10): array
    {
        return $this->getBaseQuery($fechaInicio, $fechaFin)
            ->select('medico_id', DB::raw('COUNT(*) as total_citas'))
            ->groupBy('medico_id')
            ->orderBy('total_citas', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($fechaInicio, $fechaFin) {
                $medico = Medico::find($item->medico_id);
                $especialidad = $medico && $medico->especialidad ? $medico->especialidad->nombre : 'Sin especialidad';
                
                return [
                    'medico_id' => $item->medico_id,
                    'nombre' => $medico ? $medico->nombre_completo : 'Médico Desconocido',
                    'especialidad' => $especialidad,
                    'total_citas' => $item->total_citas,
                    'tasa_completitud' => $this->getTasaCompletitudMedico($item->medico_id, $fechaInicio, $fechaFin),
                    'tasa_asistencia' => $this->getTasaAsistenciaMedico($item->medico_id, $fechaInicio, $fechaFin),
                ];
            })
            ->toArray();
    }

    public function getTopEspecialidades(Carbon $fechaInicio, Carbon $fechaFin, int $limit = 10): array
    {
        $totalCitas = $this->getBaseQuery($fechaInicio, $fechaFin)->count();
        
        return $this->getBaseQuery($fechaInicio, $fechaFin)
            ->select('especialidad_id', DB::raw('COUNT(*) as total_citas'))
            ->groupBy('especialidad_id')
            ->orderBy('total_citas', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($totalCitas, $fechaInicio, $fechaFin) {
                $especialidad = \App\Models\Especialidad::find($item->especialidad_id);
                $porcentaje = $totalCitas > 0 ? ($item->total_citas / $totalCitas) * 100 : 0;
                
                return [
                    'id' => $item->especialidad_id,
                    'nombre' => $especialidad ? $especialidad->nombre : 'Sin Especialidad',
                    'descripcion' => $especialidad ? $especialidad->descripcion : null,
                    'total_citas' => $item->total_citas,
                    'porcentaje' => $porcentaje,
                    'tendencia' => $this->getTendenciaEspecialidad($item->especialidad_id, $fechaInicio, $fechaFin),
                ];
            })
            ->toArray();
    }

    public function getTendenciasSemanales(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return $this->getBaseQuery($fechaInicio, $fechaFin)
            ->select(
                DB::raw('DAYOFWEEK(fecha_inicio) as dia_semana'),
                DB::raw('COUNT(*) as total_citas')
            )
            ->groupBy('dia_semana')
            ->orderBy('dia_semana')
            ->get()
            ->map(function ($item) {
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                return [
                    'dia' => $dias[$item->dia_semana - 1] ?? 'Desconocido',
                    'total_citas' => $item->total_citas,
                ];
            })
            ->toArray();
    }

    public function getAnalisisCancelaciones(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $cancelaciones = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('estado', Cita::ESTADO_CANCELADA)
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return [
            'total_cancelaciones' => $cancelaciones->sum('total'),
            'promedio_diario' => round($cancelaciones->avg('total') ?? 0, 2),
            'tendencia' => $this->calcularTendencia($cancelaciones->pluck('total')->toArray()),
            'por_dia' => $cancelaciones->toArray(),
        ];
    }

    public function getTasaAsistencia(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $query = $this->getBaseQuery($fechaInicio, $fechaFin);
        
        $total = $query->count();
        $asistidas = (clone $query)->where('estado', Cita::ESTADO_COMPLETADA)->count();
        $noAsistidas = (clone $query)->where('estado', Cita::ESTADO_NO_ASISTIO)->count();

        return [
            'total_programadas' => $total,
            'asistidas' => $asistidas,
            'no_asistidas' => $noAsistidas,
            'tasa_asistencia' => $total > 0 ? round(($asistidas / $total) * 100, 2) : 0,
            'tasa_no_asistencia' => $total > 0 ? round(($noAsistidas / $total) * 100, 2) : 0,
        ];
    }

    public function getTiempoPromedioEspera(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        // Esta función requeriría un campo adicional para registrar la hora real de atención
        // Por ahora retornamos datos de ejemplo
        return [
            'promedio_minutos' => 15,
            'maximo_minutos' => 45,
            'minimo_minutos' => 5,
            'total_citas_medicion' => 0,
        ];
    }

    private function getTendenciaEspecialidad(?int $especialidadId, Carbon $fechaInicio, Carbon $fechaFin): float
    {
        if (!$especialidadId) return 0;

        // Obtener datos del período actual y anterior
        $periodoActual = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('especialidad_id', $especialidadId)
            ->count();

        $periodoAnteriorInicio = (clone $fechaInicio)->subDays($fechaInicio->diffInDays($fechaFin));
        $periodoAnteriorFin = (clone $fechaInicio)->subDay();

        $periodoAnterior = $this->getBaseQuery($periodoAnteriorInicio, $periodoAnteriorFin)
            ->where('especialidad_id', $especialidadId)
            ->count();

        if ($periodoAnterior === 0) return $periodoActual > 0 ? 100 : 0;

        return (($periodoActual - $periodoAnterior) / $periodoAnterior) * 100;
    }

    private function getBaseQuery(Carbon $fechaInicio, Carbon $fechaFin)
    {
        $query = Cita::query()
            ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);

        if ($this->empresaId) {
            $query->where('empresa_id', $this->empresaId);
        }

        if ($this->sucursalId) {
            $query->where('sucursal_id', $this->sucursalId);
        }

        return $query;
    }

    private function getTasaCompletitudMedico(int $medicoId, Carbon $fechaInicio, Carbon $fechaFin): float
    {
        $total = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('medico_id', $medicoId)
            ->count();
            
        $completadas = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('medico_id', $medicoId)
            ->where('estado', Cita::ESTADO_COMPLETADA)
            ->count();

        return $total > 0 ? round(($completadas / $total) * 100, 1) : 0;
    }

    private function getTasaAsistenciaMedico(int $medicoId, Carbon $fechaInicio, Carbon $fechaFin): float
    {
        $total = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('medico_id', $medicoId)
            ->count();
            
        $asistidas = $this->getBaseQuery($fechaInicio, $fechaFin)
            ->where('medico_id', $medicoId)
            ->whereIn('estado', [Cita::ESTADO_COMPLETADA, Cita::ESTADO_CONFIRMADA])
            ->count();

        return $total > 0 ? round(($asistidas / $total) * 100, 1) : 0;
    }

    private function calcularTendencia(array $datos): string
    {
        if (count($datos) < 2) {
            return 'estable';
        }

        $primeros = array_slice($datos, 0, count($datos) / 2);
        $segundos = array_slice($datos, count($datos) / 2);

        $promedioPrimeros = array_sum($primeros) / count($primeros);
        $promedioSegundos = array_sum($segundos) / count($segundos);

        $diferencia = $promedioSegundos - $promedioPrimeros;
        $porcentaje = ($diferencia / $promedioPrimeros) * 100;

        if ($porcentaje > 10) {
            return 'aumentando';
        } elseif ($porcentaje < -10) {
            return 'disminuyendo';
        } else {
            return 'estable';
        }
    }
}