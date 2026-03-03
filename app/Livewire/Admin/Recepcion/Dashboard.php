<?php

namespace App\Livewire\Admin\Recepcion;

use Livewire\Component;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\ConsultorioAsignacion;
use App\Models\Consultorio;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Traits\HasDynamicLayout;

class Dashboard extends Component
{
    use WithPagination;
    use HasDynamicLayout;

    public $search = '';
    public $date = '';
    public $stats = [];
    public $selectedPaciente = null;
    public $selectedPacienteCitas = [];
    
    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->selectedPaciente = null;
        $this->selectedPacienteCitas = [];
    }

    public function selectPaciente($pacienteId)
    {
        $this->selectedPaciente = Paciente::with(['tutor', 'citas' => function($q) {
            $q->with(['medico', 'especialidad'])
              ->orderBy('fecha_inicio', 'desc')
              ->take(10);
        }])->find($pacienteId);

        $this->selectedPacienteCitas = $this->selectedPaciente 
            ? $this->selectedPaciente->citas->toArray() 
            : [];
    }

    public function clearSelection()
    {
        $this->selectedPaciente = null;
        $this->selectedPacienteCitas = [];
    }

    public function pacienteTieneDatosCompletos($paciente)
    {
        $required = ['nombres', 'apellidos', 'documento_identidad', 'fecha_nacimiento', 'telefono', 'genero', 'direccion'];
        
        foreach ($required as $field) {
            $value = $paciente->$field ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                return false;
            }
        }

        $edad = $paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age : null;
        if ($edad !== null && $edad < 18) {
            $tutor = $paciente->tutor;
            if (!$tutor) {
                return false;
            }
            $tutorRequired = ['nombres', 'apellidos', 'documento_identidad', 'parentesco', 'telefono'];
            foreach ($tutorRequired as $field) {
                $value = $tutor->$field ?? null;
                if ($value === null || (is_string($value) && trim($value) === '')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function marcarLlegada($citaId)
    {
        $cita = Cita::find($citaId);
        if (!$cita) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Cita no encontrada']);
            return;
        }
        $cita->cambiarEstado(Cita::ESTADO_EN_CURSO);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Llegada registrada']);
    }

    public function completarAtencion($citaId)
    {
        $cita = Cita::find($citaId);
        if (!$cita) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Cita no encontrada']);
            return;
        }
        $cita->cambiarEstado(Cita::ESTADO_COMPLETADA);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Atención completada']);
    }

    public function cancelarCita($citaId)
    {
        $cita = Cita::find($citaId);
        if (!$cita) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Cita no encontrada']);
            return;
        }
        $cita->cambiarEstado(Cita::ESTADO_CANCELADA);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Cita cancelada']);
    }

    public function enviarRecordatorio($citaId)
    {
        try {
            $cita = Cita::findOrFail($citaId);
            $service = \App\Services\CitaNotificationService::forCompany($cita->empresa_id);
            $resultado = $service->enviarRecordatorio($cita);
            if ($resultado) {
                $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Recordatorio enviado']);
            } else {
                $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'No se pudo enviar el recordatorio']);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al enviar el recordatorio']);
        }
    }
    
    public function render()
    {
        $pacientes = [];
        $hoy = Carbon::parse($this->date);
        $citasHoy = Cita::query()
            ->whereDate('fecha_inicio', $hoy)
            ->orderBy('fecha_inicio')
            ->with(['paciente', 'medico', 'especialidad'])
            ->get();
        
        $this->stats = [
            'pendientes' => $citasHoy->where('estado', Cita::ESTADO_PENDIENTE)->count(),
            'confirmadas' => $citasHoy->where('estado', Cita::ESTADO_CONFIRMADA)->count(),
            'completadas' => $citasHoy->where('estado', Cita::ESTADO_COMPLETADA)->count(),
            'canceladas' => $citasHoy->where('estado', Cita::ESTADO_CANCELADA)->count(),
            'no_asistio' => $citasHoy->where('estado', Cita::ESTADO_NO_ASISTIO)->count(),
            'total' => $citasHoy->count(),
        ];
        
        $ocupacionConsultorios = ConsultorioAsignacion::query()
            ->whereDate('fecha', $hoy)
            ->with(['medico', 'consultorio'])
            ->get()
            ->keyBy('consultorio_id');
        
        $consultorios = Consultorio::query()
            ->orderBy('nombre')
            ->get();

        $activosConsultorios = Consultorio::query()->where('status', true)->count();
        $ocupadosConsultorios = $ocupacionConsultorios->count();
        $disponiblesConsultorios = max($activosConsultorios - $ocupadosConsultorios, 0);
        $this->stats['ocupados'] = $ocupadosConsultorios;
        $this->stats['disponibles'] = $disponiblesConsultorios;
        
        if (strlen($this->search) > 2) {
            $pacientes = Paciente::query()
                ->forUser()
                ->where(function($query) {
                    $query->where('nombres', 'like', '%' . $this->search . '%')
                          ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                          ->orWhere('documento_identidad', 'like', '%' . $this->search . '%')
                          ->orWhere('telefono', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->with([
                    'tutor',
                    'citas' => function($query) {
                    $query->whereIn('estado', [
                            Cita::ESTADO_PENDIENTE, 
                            Cita::ESTADO_CONFIRMADA
                        ])
                        ->whereDate('fecha_inicio', '>=', Carbon::today())
                        ->orderBy('fecha_inicio', 'asc');
                }])
                ->take(10)
                ->get();
        }

        return view('livewire.admin.recepcion.dashboard', [
            'pacientes' => $pacientes,
            'citasHoy' => $citasHoy,
            'ocupacionConsultorios' => $ocupacionConsultorios,
            'stats' => $this->stats,
            'consultorios' => $consultorios,
        ])->layout($this->getLayout());
    }
}
