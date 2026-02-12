<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Pago;

class Dashboard extends Component
{
    use HasDynamicLayout;

    public $medico;
    public $stats = [];
    public $todayCitas = [];
    public $weekCitas = [];
    public $citasChartData = [];
    public $ingresosChartData = [];
    public $consultasConsultorio = [];

    public function mount($id = null)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Si no se pasa ID, obtener el médico del usuario autenticado
        if ($id) {
            $this->medico = Medico::findOrFail($id);
        } else {
            $this->medico = Medico::where('user_id', auth()->id())->first();
        }

        if (!$this->medico) {
            session()->flash('error', 'No se encontró información de médico asociada a este usuario.');
            return redirect()->route('dashboard');
        }

        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $hoy = Carbon::today();
        $estaSemana = Carbon::now()->startOfWeek();
        $esteMes = Carbon::now()->startOfMonth();

        // Estadísticas del médico
        $this->stats = [
            'citas_hoy' => Cita::where('medico_id', $this->medico->id)
                ->whereDate('fecha_inicio', $hoy)
                ->count(),
            'citas_semana' => Cita::where('medico_id', $this->medico->id)
                ->whereDate('fecha_inicio', '>=', $estaSemana)
                ->count(),
            'citas_mes' => Cita::where('medico_id', $this->medico->id)
                ->whereDate('fecha_inicio', '>=', $esteMes)
                ->count(),
            'citas_completadas_mes' => Cita::where('medico_id', $this->medico->id)
                ->where('estado', 'completada')
                ->whereDate('fecha_inicio', '>=', $esteMes)
                ->count(),
            'pacientes_unicos' => Cita::where('medico_id', $this->medico->id)
                ->whereDate('fecha_inicio', '>=', $esteMes)
                ->distinct('paciente_id')
                ->count('paciente_id'),
            'ingresos_mes' => 0,
        ];

        // Citas de hoy
        $this->todayCitas = Cita::with(['paciente', 'tipoConsulta'])
            ->where('medico_id', $this->medico->id)
            ->whereDate('fecha_inicio', $hoy)
            ->get();

        // Citas de la semana
        $this->weekCitas = Cita::with(['paciente', 'tipoConsulta'])
            ->where('medico_id', $this->medico->id)
            ->whereDate('fecha_inicio', '>=', $hoy)
            ->whereDate('fecha_inicio', '<=', Carbon::now()->endOfWeek())
            ->orderBy('fecha_inicio', 'asc')
        
            ->limit(10)
            ->get();

        $this->consultasConsultorio = \App\Models\Consulta::with('paciente')
            ->porMedico($this->medico->id)
            ->porEstado(\App\Models\Consulta::ESTADO_EN_CONSULTORIO)
            ->orderBy('fecha_consulta', 'asc')
            ->get();

        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Gráfico de citas por día (últimos 7 días)
        $citasPorDia = Cita::selectRaw('DATE(fecha_inicio) as fecha, COUNT(*) as total')
            ->where('medico_id', $this->medico->id)
            ->whereDate('fecha_inicio', '>=', Carbon::now()->subDays(6))
            ->groupBy('fecha_inicio')
            ->orderBy('fecha_inicio')
            ->get();

        $this->citasChartData = [
            'labels' => $citasPorDia->pluck('fecha_inicio')->map(fn($fecha) => Carbon::parse($fecha)->format('d/m'))->toArray(),
            'data' => $citasPorDia->pluck('total')->toArray()
        ];

        // Gráfico de ingresos por mes (últimos 6 meses)
        $ingresosPorMes = 0;;

        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        $this->ingresosChartData = [
            'labels' => 100,
            'data' => 200
        ];
    }

    public function cancelarCita($citaId)
    {
        try {
            $cita = Cita::where('id', $citaId)
                ->where('medico_id', $this->medico->id)
                ->first();

            if ($cita) {
                $cita->update(['estado' => 'cancelada']);
                session()->flash('success', 'Cita cancelada exitosamente.');
                $this->loadDashboardData();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cancelar la cita.');
        }
    }

    public function completarCita($citaId)
    {
        try {
            $cita = Cita::where('id', $citaId)
                ->where('medico_id', $this->medico->id)
                ->first();

            if ($cita) {
                $cita->update(['estado' => 'completada']);
                session()->flash('success', 'Cita marcada como completada.');
                $this->loadDashboardData();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al completar la cita.');
        }
    }

    public function render()
    {
        return view('livewire.doctor.dashboard', [
            'medico' => $this->medico,
            'stats' => $this->stats,
            'todayCitas' => $this->todayCitas,
            'weekCitas' => $this->weekCitas,
            'citasChartData' => $this->citasChartData,
            'ingresosChartData' => $this->ingresosChartData,
            'consultasConsultorio' => $this->consultasConsultorio
        ])->layout($this->getLayout());
    }
}
