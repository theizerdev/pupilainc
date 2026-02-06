<?php

namespace App\Livewire\Admin\Citas;

use App\Models\Cita;
use App\Models\Medico;
use App\Services\CitaReagendamientoService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Reagendamiento extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $estado = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $medicoId = '';

    public $stats = [];

    public $mostrarModalReagendamiento = false;
    public $citaSeleccionada = null;
    public $horariosDisponibles = [];
    public $diasBusqueda = 7;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'medicoId' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->fechaDesde = now()->subMonth()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->calcularStats();
    }

    public function updated($name)
    {
        if (in_array($name, ['search', 'estado', 'fechaDesde', 'fechaHasta', 'medicoId'])) {
            $this->resetPage();
        }
    }

    public function calcularStats()
    {
        $empresaId = auth()->user()->empresa_id;

        $baseQuery = fn() => Cita::where('empresa_id', $empresaId);

        $this->stats = [
            'canceladas' => $baseQuery()->where('estado', 'cancelada')
                ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_inicio', '<=', $this->fechaHasta))
                ->count(),
            'no_asistidas' => $baseQuery()->where('estado', 'no_asistio')
                ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_inicio', '<=', $this->fechaHasta))
                ->count(),
            'reagendables' => $baseQuery()->whereIn('estado', ['cancelada', 'no_asistio'])
                ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_inicio', '<=', $this->fechaHasta))
                ->count(),
            'reagendadas' => $baseQuery()->where('motivo', 'like', '%[Re-agendada]%')
                ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_inicio', '<=', $this->fechaHasta))
                ->count(),
        ];
    }

    public function getHayFiltrosActivosProperty(): bool
    {
        return $this->search !== ''
            || $this->estado !== ''
            || $this->medicoId !== ''
            || $this->fechaDesde !== now()->subMonth()->format('Y-m-d')
            || $this->fechaHasta !== now()->format('Y-m-d');
    }

    public function filtrarPorEstado(?string $estado = null)
    {
        $this->estado = $estado ?: '';
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'estado', 'medicoId']);
        $this->fechaDesde = now()->subMonth()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->resetPage();
        $this->calcularStats();
    }

    public function render()
    {
        $empresaId = auth()->user()->empresa_id;

        $citas = Cita::with(['paciente', 'medico', 'especialidad'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('paciente', function ($sub) {
                        $sub->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido', 'like', '%' . $this->search . '%')
                            ->orWhere('cedula', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('medico', function ($sub) {
                        $sub->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->estado, function ($query) {
                $query->where('estado', $this->estado);
            }, function ($query) {
                $query->whereIn('estado', ['cancelada', 'no_asistio']);
            })
            ->when($this->medicoId, function ($query) {
                $query->where('medico_id', $this->medicoId);
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha_inicio', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha_inicio', '<=', $this->fechaHasta);
            })
            ->where('empresa_id', $empresaId)
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(15);

        $medicos = Medico::where('empresa_id', $empresaId)
            ->where('status', true)
            ->orderBy('nombres')
            ->get();

        return view('livewire.admin.citas.reagendamiento', [
            'citas' => $citas,
            'medicos' => $medicos,
            'estados' => [
                '' => 'Canceladas / No asistidas',
                'cancelada' => 'Solo Canceladas',
                'no_asistio' => 'Solo No Asistidas',
                'pendiente' => 'Pendientes',
                'confirmada' => 'Confirmadas',
            ],
        ])->layout($this->getLayout());
    }

    public function buscarHorariosDisponibles($citaId)
    {
        try {
            $cita = Cita::with(['paciente', 'medico', 'especialidad'])->findOrFail($citaId);

            if ($cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No tiene permisos para re-agendar esta cita.',
                    'duration' => 4000
                ]);
                return;
            }

            $service = CitaReagendamientoService::forCompany(auth()->user()->empresa_id);

            $this->citaSeleccionada = $cita;
            $this->horariosDisponibles = $service->buscarHorariosDisponibles($cita, $this->diasBusqueda);
            $this->mostrarModalReagendamiento = true;

            if (empty($this->horariosDisponibles)) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se encontraron horarios disponibles en los próximos ' . $this->diasBusqueda . ' días.',
                    'duration' => 5000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al buscar horarios: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function ampliarBusqueda()
    {
        $this->diasBusqueda = min($this->diasBusqueda + 7, 60);

        if ($this->citaSeleccionada) {
            $this->buscarHorariosDisponibles($this->citaSeleccionada->id);
        }
    }

    public function reagendarManualmente($fechaHora)
    {
        try {
            if (!$this->citaSeleccionada) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se ha seleccionado una cita para re-agendar.',
                    'duration' => 4000
                ]);
                return;
            }

            $service = CitaReagendamientoService::forCompany(auth()->user()->empresa_id);

            $nuevaCita = $service->crearNuevaCita($this->citaSeleccionada, [
                'fecha_hora' => $fechaHora,
                'fecha_formateada' => Carbon::parse($fechaHora)->format('d/m/Y H:i'),
                'duracion' => 30,
            ]);

            $nuevaCita->programarRecordatorios();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Cita re-agendada exitosamente para ' . Carbon::parse($fechaHora)->format('d/m/Y H:i'),
                'duration' => 5000
            ]);

            $this->cerrarModal();
            $this->calcularStats();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al re-agendar: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function reagendarAutomaticamente($citaId)
    {
        try {
            $cita = Cita::findOrFail($citaId);

            if ($cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No tiene permisos para re-agendar esta cita.',
                    'duration' => 4000
                ]);
                return;
            }

            $service = CitaReagendamientoService::forCompany(auth()->user()->empresa_id);
            $nuevaCita = $service->reagendarAutomaticamente($cita);

            if ($nuevaCita) {
                $nuevaCita->programarRecordatorios();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Cita re-agendada automáticamente para ' . $nuevaCita->fecha_inicio->format('d/m/Y H:i'),
                    'duration' => 5000
                ]);
                $this->calcularStats();
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se encontraron horarios disponibles para re-agendar automáticamente.',
                    'duration' => 5000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en re-agendamiento automático: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function procesarReagendamientosMasivos()
    {
        try {
            $service = CitaReagendamientoService::forCompany(auth()->user()->empresa_id);
            $fechaInicio = Carbon::parse($this->fechaDesde);
            $fechaFin = Carbon::parse($this->fechaHasta);

            $resultados = $service->procesarReagendamientosMasivos($fechaInicio, $fechaFin);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Re-agendamiento masivo: {$resultados['exitosas']} exitosas, {$resultados['fallidas']} fallidas de {$resultados['total_procesadas']} procesadas.",
                'duration' => 6000
            ]);

            $this->calcularStats();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en re-agendamiento masivo: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModalReagendamiento = false;
        $this->citaSeleccionada = null;
        $this->horariosDisponibles = [];
        $this->diasBusqueda = 7;
    }
}
