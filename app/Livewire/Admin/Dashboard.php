<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Caja;
use App\Models\Empresa;
use App\Models\User;
use App\Models\WhatsAppMessage;

class Dashboard extends Component
{
    use HasDynamicLayout;

    public $stats = [];
    public $recentCitas = [];
    public $citasChartData = [];
    public $ingresosChartData = [];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->stats = [
            'total_citas' => Cita::count(),
            'citas_hoy' => Cita::whereDate('fecha_inicio', Carbon::today())->count(),
            'total_medicos' => Medico::count(),
            'total_pacientes' => Paciente::count(),
            'ingresos_mes' => 0,
            'cajas_activas' => Caja::where('estado', 'abierta')->count(),
            'total_empresas' => Empresa::count(),
            'usuarios_activos' => User::where('status', 1)->count(),
            'mensajes_whatsapp' => WhatsAppMessage::whereDate('created_at', Carbon::today())->count(),
        ];

        $this->recentCitas = Cita::with(['paciente', 'medico'])
            ->whereDate('fecha_inicio', '>=', Carbon::today())
            ->orderBy('fecha_inicio', 'asc')
            
            ->limit(5)
            ->get();

        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Datos para gráfico de citas por día (últimos 7 días)
        $citasPorDia = Cita::selectRaw('DATE(fecha_inicio) as fecha, COUNT(*) as total')
            ->whereDate('fecha_inicio', '>=', Carbon::now()->subDays(6))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $this->citasChartData = [
            'labels' => $citasPorDia->pluck('fecha')->map(fn($fecha) => Carbon::parse($fecha)->format('d/m'))->toArray(),
            'data' => $citasPorDia->pluck('total')->toArray()
        ];

        // Datos para gráfico de ingresos por mes (últimos 6 meses)
        $ingresosPorMes = 0;

        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        
    }

    public function render()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        return view('livewire.admin.dashboard', [
            'stats' => $this->stats,
            'recentCitas' => $this->recentCitas,
            'citasChartData' => $this->citasChartData,
            'ingresosChartData' => $this->ingresosChartData
        ])->layout($this->getLayout());
    }
}