<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Pago;

class Dashboard extends Component
{
    use HasDynamicLayout;

    public $stats = [];
    public $recentCitas = [];
    public $citasChartData = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->stats = [
            'citas_hoy' => Cita::whereDate('fecha_inicio', Carbon::today())->count(),
            'pacientes_total' => Paciente::count(),
            'medicos_total' => Medico::count(),
            'ingresos_mes' => Pago::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('estado', Pago::ESTADO_APROBADO)
                ->sum('total_usd'),
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

    public function loadChartData()
    {
        $citasPorDia = Cita::selectRaw('DATE(fecha_inicio) as fecha, COUNT(*) as total')
            ->whereDate('fecha_inicio', '>=', Carbon::now()->subDays(6))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D d/m');
            $found = $citasPorDia->firstWhere('fecha', $date->format('Y-m-d'));
            $data[] = $found ? $found->total : 0;
        }

        $this->citasChartData = [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => $this->stats,
            'recentCitas' => $this->recentCitas,
            'citasChartData' => $this->citasChartData
        ])->layout($this->getLayout());
    }
}
