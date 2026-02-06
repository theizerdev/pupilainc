<?php

namespace App\Livewire\Admin\Citas;

use App\Models\Cita;
use App\Services\CitaReagendamientoService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Reagendamiento extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = 'cancelada';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $medicoId = '';

    public $mostrarModalReagendamiento = false;
    public $citaSeleccionada = null;
    public $horariosDisponibles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => 'cancelada'],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'medicoId' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->fechaDesde = now()->subMonth()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $citas = Cita::with(['paciente', 'medico', 'especialidad'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('paciente', function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('apellido', 'like', '%' . $this->search . '%')
                          ->orWhere('cedula', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('medico', function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('apellido', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->estado, function ($query) {
                $query->where('estado', $this->estado);
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
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(15);

        return view('livewire.admin.citas.reagendamiento', [
            'citas' => $citas,
            'estados' => [
                'cancelada' => 'Canceladas',
                'no_asistio' => 'No Asistidas',
                'pendiente' => 'Pendientes',
                'confirmada' => 'Confirmadas',
            ],
        ]);
    }

    public function buscarHorariosDisponibles($citaId)
    {
        try {
            $cita = Cita::findOrFail($citaId);
            
            // Verificar que la cita pertenezca a la empresa
            if ($cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No tiene permisos para re-agendar esta cita.',
                    'duration' => 4000
                ]);
                return;
            }

            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id
            );

            $this->citaSeleccionada = $cita;
            $this->horariosDisponibles = $service->buscarHorariosDisponibles($cita);
            $this->mostrarModalReagendamiento = true;

            if (empty($this->horariosDisponibles)) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se encontraron horarios disponibles para re-agendar esta cita.',
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

            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id
            );

            $nuevaCita = $service->crearNuevaCita($this->citaSeleccionada, [
                'fecha_hora' => $fechaHora,
                'fecha_formateada' => Carbon::parse($fechaHora)->format('d/m/Y H:i'),
                'duracion' => 30,
            ]);

            // Programar recordatorios para la nueva cita
            $nuevaCita->programarRecordatorios();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Cita re-agendada exitosamente para ' . Carbon::parse($fechaHora)->format('d/m/Y H:i'),
                'duration' => 5000
            ]);

            $this->cerrarModal();
            $this->render(); // Recargar la lista

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
            
            // Verificar que la cita pertenezca a la empresa
            if ($cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No tiene permisos para re-agendar esta cita.',
                    'duration' => 4000
                ]);
                return;
            }

            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id
            );

            $nuevaCita = $service->reagendarAutomaticamente($cita);

            if ($nuevaCita) {
                // Programar recordatorios para la nueva cita
                $nuevaCita->programarRecordatorios();

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Cita re-agendada automáticamente para ' . $nuevaCita->fecha_inicio->format('d/m/Y H:i'),
                    'duration' => 5000
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se encontraron horarios disponibles para re-agendar automáticamente.',
                    'duration' => 5000
                ]);
            }

            $this->render(); // Recargar la lista

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
            $service = CitaReagendamientoService::forCompany(
                auth()->user()->empresa_id
            );

            $fechaInicio = Carbon::parse($this->fechaDesde);
            $fechaFin = Carbon::parse($this->fechaHasta);

            $resultados = $service->procesarReagendamientosMasivos($fechaInicio, $fechaFin);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Re-agendamiento masivo completado: {$resultados['exitosas']} exitosas, {$resultados['fallidas']} fallidas.",
                'duration' => 5000
            ]);

            $this->render(); // Recargar la lista

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
    }
}