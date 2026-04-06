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
    public $dateRange = 'week';

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
            'ingresos_mes' => Pago::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('estado', Pago::ESTADO_APROBADO)
                ->whereDoesntHave('notasCredito', fn($q) => $q->where('estado', Pago::ESTADO_APROBADO))
                ->sum('total_usd'),
            'cajas_activas' => Caja::where('estado', 'abierta')->count(),
            'total_empresas' => Empresa::count(),
            'usuarios_activos' => User::where('status', 1)->count(),
            'mensajes_whatsapp' => WhatsAppMessage::whereDate('createdAt', Carbon::today())->count(),
        ];

        $this->recentCitas = Cita::with(['paciente', 'medico'])
            ->whereDate('fecha_inicio', '>=', Carbon::today())
            ->orderBy('fecha_inicio', 'asc')

            ->limit(5)
            ->get();

        $this->loadChartData();

        $this->dispatch('chartDataUpdated', [
            'citasChartData' => $this->citasChartData,
        ]);
    }

    public function updatedDateRange()
    {
        $this->loadChartData();
        $this->dispatch('chartDataUpdated', [
            'citasChartData' => $this->citasChartData,
        ]);
    }

    public function loadChartData()
    {
        $statusKeys = [
            Cita::ESTADO_PENDIENTE,
            Cita::ESTADO_CONFIRMADA,
            Cita::ESTADO_FINALIZADA,
            Cita::ESTADO_CANCELADA,
            Cita::ESTADO_NO_ASISTIO
        ];
        $statesSeries = [
            Cita::ESTADO_PENDIENTE => [],
            Cita::ESTADO_CONFIRMADA => [],
            Cita::ESTADO_FINALIZADA => [],
            Cita::ESTADO_CANCELADA => [],
            Cita::ESTADO_NO_ASISTIO => [],
        ];

        switch ($this->dateRange) {
            case 'week':
                $citasPorPeriodo = Cita::selectRaw('DATE(fecha_inicio) as fecha, COUNT(*) as total')
                    ->whereDate('fecha_inicio', '>=', Carbon::now()->subDays(6))
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $labels[] = $date->format('D d/m');
                    $found = $citasPorPeriodo->firstWhere('fecha', $date->format('Y-m-d'));
                    $data[] = $found ? $found->total : 0;
                    foreach ($statusKeys as $st) {
                        $statesSeries[$st][] = Cita::whereDate('fecha_inicio', $date->format('Y-m-d'))
                            ->where('estado', $st)
                            ->count();
                    }
                }
                break;

            case 'month':
                $labels = [];
                $data = [];
                for ($i = 3; $i >= 0; $i--) {
                    $start = Carbon::now()->subWeeks($i)->startOfWeek();
                    $end = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'Sem ' . $start->format('d/m');
                    $data[] = Cita::whereDate('fecha_inicio', '>=', $start)
                        ->whereDate('fecha_inicio', '<=', $end)
                        ->count();
                    foreach ($statusKeys as $st) {
                        $statesSeries[$st][] = Cita::whereDate('fecha_inicio', '>=', $start)
                            ->whereDate('fecha_inicio', '<=', $end)
                            ->where('estado', $st)
                            ->count();
                    }
                }
                break;

            case 'quarter':
                $labels = [];
                $data = [];
                for ($i = 2; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->translatedFormat('M Y');
                    $data[] = Cita::whereMonth('fecha_inicio', $month->month)
                        ->whereYear('fecha_inicio', $month->year)
                        ->count();
                    foreach ($statusKeys as $st) {
                        $statesSeries[$st][] = Cita::whereMonth('fecha_inicio', $month->month)
                            ->whereYear('fecha_inicio', $month->year)
                            ->where('estado', $st)
                            ->count();
                    }
                }
                break;

            case 'semester':
                $labels = [];
                $data = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->translatedFormat('M');
                    $data[] = Cita::whereMonth('fecha_inicio', $month->month)
                        ->whereYear('fecha_inicio', $month->year)
                        ->count();
                    foreach ($statusKeys as $st) {
                        $statesSeries[$st][] = Cita::whereMonth('fecha_inicio', $month->month)
                            ->whereYear('fecha_inicio', $month->year)
                            ->where('estado', $st)
                            ->count();
                    }
                }
                break;
        }

        $this->citasChartData = [
            'labels' => $labels,
            'data' => $data,
            'totales' => $data,
            'states' => $statesSeries,
        ];
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
