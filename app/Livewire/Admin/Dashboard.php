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
    public $alerts = [];
    public $recentPayments = [];
    public $topMedicos = [];

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
            'ingresos_hoy' => Pago::whereDate('created_at', Carbon::today())
                ->where('estado', Pago::ESTADO_APROBADO)
                ->sum('total_usd'),
            'tasa_asistencia' => $this->calcularTasaAsistencia(),
        ];

        $this->recentCitas = Cita::with(['paciente', 'medico'])
            ->whereDate('fecha_inicio', '>=', Carbon::today())
            ->orderBy('fecha_inicio', 'asc')
            ->limit(5)
            ->get();

        $this->loadChartData();
        $this->loadAlerts();
        $this->loadRecentPayments();
        $this->loadTopMedicos();

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

    public function calcularTasaAsistencia()
    {
        $totalCitasPasadas = Cita::whereDate('fecha_inicio', '<', Carbon::today())
            ->where('estado', '!=', Cita::ESTADO_CANCELADA)
            ->count();

        if ($totalCitasPasadas == 0) {
            return 0;
        }

        $citasCompletadas = Cita::whereDate('fecha_inicio', '<', Carbon::today())
            ->where('estado', Cita::ESTADO_COMPLETADA)
            ->count();

        return round(($citasCompletadas / $totalCitasPasadas) * 100, 1);
    }

    public function loadAlerts()
    {
        $this->alerts = [];

        // Citas sin confirmar (próximas 24h)
        $citasSinConfirmar = Cita::whereBetween('fecha_inicio', [Carbon::now(), Carbon::now()->addHours(24)])
            ->where('estado', '!=', 'confirmada')
            ->count();

        if ($citasSinConfirmar > 0) {
            $this->alerts[] = [
                'type' => 'warning',
                'icon' => 'ri-time-line',
                'title' => 'Citas sin confirmar',
                'message' => "$citasSinConfirmar citas en las próximas 24 horas",
                'color' => '#f59e0b',
            ];
        }

        // Recordatorios fallidos
        $recordatoriosFallidos = \App\Models\CitaRecordatorio::where('estado', 'fallido')
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($recordatoriosFallidos > 0) {
            $this->alerts[] = [
                'type' => 'error',
                'icon' => 'ri-error-warning-line',
                'title' => 'Recordatorios fallidos',
                'message' => "$recordatoriosFallidos recordatorios no enviados hoy",
                'color' => '#ef4444',
            ];
        }

        // Pagos pendientes
        $pagosPendientes = Pago::where('estado', '!=', Pago::ESTADO_APROBADO)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($pagosPendientes > 0) {
            $this->alerts[] = [
                'type' => 'info',
                'icon' => 'ri-money-dollar-circle-line',
                'title' => 'Pagos pendientes',
                'message' => "$pagosPendientes pagos por aprobar hoy",
                'color' => '#3b82f6',
            ];
        }

        // Citas canceladas hoy
        $citasCanceladas = Cita::whereDate('fecha_inicio', Carbon::today())
            ->where('estado', Cita::ESTADO_CANCELADA)
            ->count();

        if ($citasCanceladas > 0) {
            $this->alerts[] = [
                'type' => 'danger',
                'icon' => 'ri-close-circle-line',
                'title' => 'Citas canceladas',
                'message' => "$citasCanceladas citas canceladas hoy",
                'color' => '#dc2626',
            ];
        }
    }

    public function loadRecentPayments()
    {
        $this->recentPayments = Pago::with(['consulta.paciente'])
            ->where('estado', Pago::ESTADO_APROBADO)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function loadTopMedicos()
    {
        $this->topMedicos = Medico::withCount(['citas as citas_hoy_count' => function ($query) {
            $query->whereDate('fecha_inicio', Carbon::today());
        }])
        ->orderByDesc('citas_hoy_count')
        ->limit(5)
        ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => $this->stats,
            'recentCitas' => $this->recentCitas,
            'citasChartData' => $this->citasChartData,
            'alerts' => $this->alerts,
            'recentPayments' => $this->recentPayments,
            'topMedicos' => $this->topMedicos
        ])->layout($this->getLayout());
    }
}
