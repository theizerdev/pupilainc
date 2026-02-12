<?php

namespace App\Livewire\Admin\Recepcion;

use Livewire\Component;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Consultorio;
use App\Models\ConsultorioAsignacion;
use Carbon\Carbon;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class ControlConsultorios extends Component
{
    use WithPagination;
    use HasDynamicLayout;

    public $fecha;
    public $search = '';

    public function mount()
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
    }

    public function asignarConsultorio($medicoId, $consultorioId)
    {
        if (empty($consultorioId)) {
            // Eliminar asignación si se selecciona "Sin Asignar"
            ConsultorioAsignacion::where('medico_id', $medicoId)
                ->where('fecha', $this->fecha)
                ->delete();
                
            $this->dispatch('show-toast', [
                'type' => 'info',
                'message' => 'Asignación eliminada.'
            ]);
            return;
        }

        // Verificar si el consultorio ya está asignado a otro médico hoy
        // (Opcional: permitir compartir si son turnos diferentes, pero por ahora asumimos bloqueo diario)
        $existe = ConsultorioAsignacion::where('consultorio_id', $consultorioId)
            ->where('fecha', $this->fecha)
            ->where('medico_id', '!=', $medicoId)
            ->exists();

        if ($existe) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Este consultorio ya está asignado a otro médico hoy.'
            ]);
            // No retornamos aquí para permitir sobrescribir si el usuario insiste? 
            // Mejor bloqueamos por ahora o pedimos confirmación. 
            // Para simplicidad, bloqueamos.
            return;
        }

        // Crear o Actualizar asignación
        ConsultorioAsignacion::updateOrCreate(
            [
                'medico_id' => $medicoId,
                'fecha' => $this->fecha,
            ],
            [
                'consultorio_id' => $consultorioId,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'created_by' => auth()->id(),
            ]
        );

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Consultorio asignado exitosamente.'
        ]);
    }

    public function render()
    {
        // Obtener IDs de médicos que tienen citas hoy (excluyendo canceladas)
        $medicosConCitasIds = Cita::query()
            ->whereDate('fecha_inicio', $this->fecha)
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->pluck('medico_id')
            ->unique();

        // Obtener los objetos Medico con sus asignaciones para hoy
        $medicos = Medico::whereIn('id', $medicosConCitasIds)
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('nombres', 'like', '%' . $this->search . '%')
                        ->orWhere('apellidos', 'like', '%' . $this->search . '%');
                });
            })
            ->with(['asignacionesConsultorios' => function($q) {
                $q->where('fecha', $this->fecha);
            }])
            ->get();

        // Obtener consultorios disponibles
        $consultoriosActivos = Consultorio::where('status', true)
            ->orderBy('nombre')
            ->get();
        $todosConsultorios = Consultorio::orderBy('nombre')->get();

        // Mapa de ocupación para visualización rápida
        $ocupacion = ConsultorioAsignacion::where('fecha', $this->fecha)
            ->with('medico')
            ->get()
            ->keyBy('consultorio_id');

        // Estadísticas
        $medicosConCitas = $medicos->count();
        $medicosSinAsignacion = $medicos->filter(function($m) {
            return $m->asignacionesConsultorios->isEmpty();
        })->count();
        $totalConsultorios = $todosConsultorios->count();
        $activos = $consultoriosActivos->count();
        $ocupados = count($ocupacion);
        $disponibles = max($activos - $ocupados, 0);
        $stats = compact('medicosConCitas', 'medicosSinAsignacion', 'totalConsultorios', 'activos', 'ocupados', 'disponibles');

        return view('livewire.admin.recepcion.control-consultorios', [
            'medicos' => $medicos,
            'consultorios' => $todosConsultorios,
            'ocupacion' => $ocupacion,
            'stats' => $stats,
        ])->layout($this->getLayout());
    }
}
