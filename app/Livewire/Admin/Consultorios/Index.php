<?php

namespace App\Livewire\Admin\Consultorios;

use App\Models\Consultorio;
use App\Models\ConsultorioAsignacion;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function delete($id)
    {
        try {
            $consultorio = Consultorio::findOrFail($id);
            $nombre = $consultorio->nombre;
            $consultorio->delete();
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Consultorio '{$nombre}' eliminado exitosamente."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $consultorio = Consultorio::findOrFail($id);
            $consultorio->status = !$consultorio->status;
            $consultorio->save();
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Estado actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $hoy = Carbon::today();
        $total = Consultorio::query()->count();
        $activos = Consultorio::query()->where('status', true)->count();
        $inactivos = Consultorio::query()->where('status', false)->count();
        $ocupadosIds = ConsultorioAsignacion::query()
            ->whereDate('fecha', $hoy)
            ->pluck('consultorio_id')
            ->unique()
            ->toArray();
        $ocupadosHoy = Consultorio::query()->where('status', true)->whereIn('id', $ocupadosIds)->count();
        $disponiblesHoy = max($activos - $ocupadosHoy, 0);
        $stats = compact('total', 'activos', 'inactivos', 'ocupadosHoy', 'disponiblesHoy');

        $consultorios = Consultorio::query()
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('ubicacion', 'like', '%' . $this->search . '%');
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.consultorios.index', [
            'consultorios' => $consultorios,
            'stats' => $stats,
        ])->layout($this->getLayout());
    }
}
