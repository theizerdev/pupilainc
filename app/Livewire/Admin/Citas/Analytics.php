<?php

namespace App\Livewire\Admin\Citas;

use App\Services\CitaAnalyticsService;
use App\Services\CitaReagendamientoService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Analytics extends Component
{
    use WithPagination, HasDynamicLayout;

    public $fechaInicio;
    public $fechaFin;
    public $medicoId;
    public $sucursalId;
    public $tipoReporte = 'general';

    public $analyticsData = [];
    public $reagendamientosPendientes = [];
    public $especialidades = [];
    public $medicos = [];
    public $sucursales = [];

    protected $queryString = [
        'fechaInicio' => ['except' => ''],
        'fechaFin' => ['except' => ''],
        'medicoId' => ['except' => ''],
        'sucursalId' => ['except' => ''],
        'tipoReporte' => ['except' => 'general'],
    ];

    protected $rules = [
        'fechaInicio' => 'required|date',
        'fechaFin' => 'required|date|after_or_equal:fechaInicio',
    ];

    public function mount()
    {
        $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
        $this->medicoId = '';
        $this->sucursalId = '';
        
        $this->cargarDatosIniciales();
        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.admin.citas.analytics-materialize', [
            'analyticsData' => $this->analyticsData,
            'reagendamientosPendientes' => $this->reagendamientosPendientes,
            'especialidades' => $this->especialidades,
            'medicos' => $this->medicos,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }

    public function cargarDatosIniciales()
    {
        // Cargar especialidades
        $this->especialidades = \App\Models\Especialidad::forUser()
            ->activas()
            ->orderBy('nombre')
            ->get()
            ->map(function ($especialidad) {
                return [
                    'id' => $especialidad->id,
                    'nombre' => $especialidad->nombre,
                    'color' => $especialidad->color,
                    'icono' => $especialidad->icono,
                ];
            })
            ->toArray();

        // Cargar médicos
        $this->medicos = \App\Models\Medico::forUser()
            ->activos()
            ->orderBy('nombres')
            ->get()
            ->map(function ($medico) {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->nombre_completo,
                    'especialidad' => $medico->especialidad->nombre ?? 'Sin especialidad',
                ];
            })
            ->toArray();

        // Cargar sucursales
        $this->sucursales = \App\Models\Sucursal::forUser()
            
            ->orderBy('nombre')
            ->get()
            ->map(function ($sucursal) {
                return [
                    'id' => $sucursal->id,
                    'nombre' => $sucursal->nombre,
                    'direccion' => $sucursal->direccion,
                ];
            })
            ->toArray();
    }

    public function cargarDatos()
    {
        $this->validate();

        try {
            $fechaInicio = Carbon::parse($this->fechaInicio);
            $fechaFin = Carbon::parse($this->fechaFin);

            $service = CitaAnalyticsService::forCompany(
                auth()->user()->empresa_id,
                $this->sucursalId ?: null
            );

            $this->analyticsData = $service->getDashboardData($fechaInicio, $fechaFin);

            // Cargar re-agendamientos pendientes
            $this->cargarReagendamientosPendientes();

            // Emitir evento para actualizar charts
            $this->dispatch('charts-updated', [
                'chartData' => $this->getChartData()
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Datos actualizados correctamente.',
                'duration' => 3000
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar datos: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function cargarReagendamientosPendientes()
    {
        try {
            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id,
                $this->sucursalId ?: null
            );

            // Obtener citas canceladas/no asistidas del último mes
            $fechaInicio = now()->subMonth();
            $fechaFin = now();

            // Aquí iría la lógica para obtener las citas que necesitan re-agendamiento
            // Por ahora, simulamos algunos datos
            $this->reagendamientosPendientes = [
                [
                    'id' => 1,
                    'paciente' => 'Juan Pérez',
                    'medico' => 'Dr. García',
                    'fecha_cancelada' => '2026-02-01 10:00',
                    'motivo' => 'Cancelada por paciente',
                    'horarios_disponibles' => 3,
                ],
                [
                    'id' => 2,
                    'paciente' => 'María López',
                    'medico' => 'Dra. Rodríguez',
                    'fecha_cancelada' => '2026-02-02 15:00',
                    'motivo' => 'No asistió',
                    'horarios_disponibles' => 5,
                ],
            ];

        } catch (\Exception $e) {
            $this->reagendamientosPendientes = [];
        }
    }

    public function procesarReagendamientoAutomatico()
    {
        try {
            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id,
                $this->sucursalId ?: null
            );

            $fechaInicio = Carbon::parse($this->fechaInicio);
            $fechaFin = Carbon::parse($this->fechaFin);

            $resultados = $service->procesarReagendamientosMasivos($fechaInicio, $fechaFin);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Re-agendamiento completado: {$resultados['exitosas']} exitosas, {$resultados['fallidas']} fallidas.",
                'duration' => 5000
            ]);

            $this->cargarDatos(); // Recargar datos

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en re-agendamiento: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function actualizarFechas($rango)
    {
        switch ($rango) {
            case 'hoy':
                $this->fechaInicio = now()->format('Y-m-d');
                $this->fechaFin = now()->format('Y-m-d');
                break;
            case 'semana':
                $this->fechaInicio = now()->startOfWeek()->format('Y-m-d');
                $this->fechaFin = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes':
                $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
                $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'anio':
                $this->fechaInicio = now()->startOfYear()->format('Y-m-d');
                $this->fechaFin = now()->endOfYear()->format('Y-m-d');
                break;
        }

        $this->cargarDatos();
    }

    public function getChartData()
    {
        return [
            'estados' => $this->analyticsData['estados'] ?? [],
            'tendencias' => $this->analyticsData['tendencias'] ?? [],
            'medicos' => $this->analyticsData['medicos'] ?? [],
            'especialidades' => $this->analyticsData['especialidades'] ?? [],
        ];
    }

    public function updatedFechaInicio()
    {
        $this->cargarDatos();
    }

    public function updatedFechaFin()
    {
        $this->cargarDatos();
    }

    public function updatedMedicoId()
    {
        $this->cargarDatos();
    }

    public function updatedSucursalId()
    {
        $this->cargarDatos();
    }
}