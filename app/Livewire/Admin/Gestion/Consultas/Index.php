<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;

class Index extends Component
{
    use HasDynamicLayout;

    public $filtroEstados = [];
    public $filtroMedico = '';
    public $filtroPaciente = '';

    public function mount()
    {
        $this->filtroEstados = [
            Consulta::ESTADO_POR_LLEGAR,
            Consulta::ESTADO_SALA_ESPERA,
            Consulta::ESTADO_EN_ENFERMERIA,
            Consulta::ESTADO_EN_CONSULTORIO,
            Consulta::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
            Consulta::ESTADO_EN_GOTAS,
            Consulta::ESTADO_DILATADO,
            Consulta::ESTADO_EN_OPTICA,
            Consulta::ESTADO_EN_ESTUDIO,
            Consulta::ESTADO_FINALIZADA,
        ];
    }

    public function getEventosProperty()
    {
        return $this->fetchEventos();
    }

    public function getEventosFresh()
    {
        return $this->fetchEventos();
    }

    protected function buildBaseQuery()
    {
        $query = Consulta::with(['paciente', 'medico', 'especialidad']);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = \App\Models\Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        return $query;
    }

    protected function mapConsultaToEvent($consulta)
    {
        $nickname = $consulta->paciente->nickname ?? '';
        $nombreCompleto = $consulta->paciente->nombre_completo;
        $edad = $consulta->paciente->edad;
        $edadTexto = $edad !== null ? (int) $edad . ' años' : '';

        // Formato título: (nickname) Nombre Apellido (sin edad, la edad se muestra solo en vista semana/día vía JS)
        $title = $nombreCompleto;
        if (!empty($nickname)) {
            $title = "({$nickname}) {$title}";
        }
        
        // Si está en sala de espera, agregar tiempo de espera
        if ($consulta->estado === Consulta::ESTADO_SALA_ESPERA && $consulta->tiempo_sala_espera !== null) {
            $title .= ' [' . $consulta->tiempo_espera_formateado . ']';
        }
        
        // Si está en gotas o dilatado, agregar tiempo en gotas
        if (in_array($consulta->estado, [Consulta::ESTADO_EN_GOTAS, Consulta::ESTADO_DILATADO]) && $consulta->tiempo_gotas_formateado !== null) {
            $title .= ' [' . $consulta->tiempo_gotas_formateado . ']';
        }
        
        return [
            'id' => $consulta->id,
            'title' => $title,
            'start' => $consulta->fecha_consulta->toIso8601String(),
            'end' => $consulta->fecha_consulta->copy()->addMinutes(30)->toIso8601String(),
            'backgroundColor' => $this->getEstadoColor($consulta->estado),
            'borderColor' => $this->getEstadoColor($consulta->estado),
            'extendedProps' => [
                'calendar' => $consulta->estado,
                'codigo' => $consulta->codigo,
                'paciente' => $consulta->paciente->nombre_completo,
                'nickname' => $consulta->paciente->nickname ?? '',
                'edad' => $consulta->paciente->edad !== null ? $consulta->paciente->edad . ' años' : '',
                'medico' => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_full' => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_id' => $consulta->medico_id,
                'especialidad' => $consulta->especialidad->nombre ?? 'Sin especialidad',
                'estado' => $consulta->estado,
                'estado_label' => $this->getEstadoLabel($consulta->estado),
                'estadoLabel' => $this->getEstadoLabel($consulta->estado),
                'estado_changed_at' => $consulta->estado_changed_at?->toIso8601String(),
                'motivo' => $consulta->motivo_consulta,
                'preconsulta' => $consulta->preconsulta,
                'tiempo_espera' => $consulta->tiempo_sala_espera,
                'tiempo_espera_formateado' => $consulta->tiempo_espera_formateado,
                'tiempo_gotas_formateado' => $consulta->tiempo_gotas_formateado,
            ],
        ];
    }

    protected function fetchEventos()
    {
        $consultas = $this->buildBaseQuery()
            ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico))
            ->when($this->filtroPaciente, fn($q) => $q->where('paciente_id', $this->filtroPaciente))
            ->when(!empty($this->filtroEstados), fn($q) => $q->whereIn('estado', $this->filtroEstados))
            ->orderBy('fecha_consulta', 'desc')
            ->get();

        return $consultas->map(fn($c) => $this->mapConsultaToEvent($c))->toArray();
    }

    public function fetchEventosRango($inicio, $fin)
    {
        try {
            $inicioCarbon = Carbon::parse($inicio);
            $finCarbon = Carbon::parse($fin);
        } catch (\Exception $e) {
            return [];
        }

        $consultas = $this->buildBaseQuery()
            ->whereBetween('fecha_consulta', [$inicioCarbon, $finCarbon])
            ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico))
            ->when($this->filtroPaciente, fn($q) => $q->where('paciente_id', $this->filtroPaciente))
            ->get();

        return $consultas->map(fn($c) => $this->mapConsultaToEvent($c))->toArray();
    }

    public function cambiarEstado($consultaId, $nuevoEstado)
    {
        $consulta = Consulta::findOrFail($consultaId);
        $consulta->cambiarEstado($nuevoEstado);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado actualizado a: ' . $this->getEstadoLabel($nuevoEstado)
        ]);

        $this->dispatch('consulta-saved');
    }

    public function getStatsProperty()
    {
        $query = Consulta::query();

        // Si es doctor, solo ver sus consultas
        if (auth()->user()->hasRole('Doctor')) {
            $medico = \App\Models\Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        $hoy = Carbon::today();

        return [
            'total_hoy' => (clone $query)->whereDate('fecha_consulta', $hoy)->count(),
            'sala_espera' => (clone $query)->porEstado(Consulta::ESTADO_SALA_ESPERA)->count(),
            'en_consultorio' => (clone $query)->porEstado(Consulta::ESTADO_EN_CONSULTORIO)->count(),
            'finalizadas_hoy' => (clone $query)->porEstado(Consulta::ESTADO_FINALIZADA)->whereDate('fecha_consulta', $hoy)->count(),
        ];
    }

    public function getMedicosProperty()
    {
        return Medico::activos()->orderBy('nombres')->get();
    }

    public function getPacientesProperty()
    {
        return Paciente::activos()->orderBy('nombres')->limit(50)->get();
    }

    protected function getEstadoLabel($estado)
    {
        return Consulta::ESTADO_LABELS[$estado] ?? ucfirst($estado);
    }

    protected function getEstadoColor($estado)
    {
        return Consulta::ESTADO_COLORES[$estado] ?? '#78909C';
    }

    protected function getPageTitle(): string
    {
        return 'Gestión de Consultas';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.gestion.consultas.index' => 'Gestión de Consultas',
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.index', [
            'eventos' => $this->eventos,
            'stats' => $this->stats,
            'medicos' => $this->medicos,
            'pacientes' => $this->pacientes,
            'estados' => $this->filtroEstados,
            'estadoLabels' => Consulta::ESTADO_LABELS,
            'estadoColores' => Consulta::ESTADO_COLORES,
        ])->layout($this->getLayout());
    }
}
