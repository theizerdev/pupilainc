<?php

namespace App\Livewire\Admin\Pacientes;

use App\Models\Paciente;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $empresa_id = null;
    public $sucursal_id = null;
    public $soloMenores = false;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => null],
        'sucursal_id' => ['except' => null],
        'soloMenores' => ['except' => false],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('access pacientes');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
        $this->sucursal_id = null;
    }

    public function updatingSucursalId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingSoloMenores()
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

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'empresa_id', 'sucursal_id', 'soloMenores']);
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit pacientes');

        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->status = !$paciente->status;
            $paciente->save();

            $mensaje = $paciente->status
                ? 'Paciente activado exitosamente.'
                : 'Paciente desactivado exitosamente.';

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $this->authorize('delete pacientes');

        try {
            $paciente = Paciente::findOrFail($id);
            $nombrePaciente = $paciente->nombres . ' ' . $paciente->apellidos;
            $paciente->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Paciente '{$nombrePaciente}' eliminado exitosamente.",
                'duration' => 4000,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el paciente: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    public function getPacientesProperty()
    {
        return Paciente::with(['empresa', 'sucursal', 'tutor'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                      ->orWhere('documento_identidad', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%')
                      ->orWhere('nickname', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->when($this->sucursal_id, function ($query) {
                $query->where('sucursal_id', $this->sucursal_id);
            })
            ->when($this->soloMenores, function ($query) {
                $query->where('fecha_nacimiento', '>', now()->subYears(18));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Paciente::count(),
            'activos' => Paciente::where('status', true)->count(),
            'inactivos' => Paciente::where('status', false)->count(),
            'menores' => Paciente::where('fecha_nacimiento', '>', now()->subYears(18))->count(),
        ];
    }

    public function getEmpresasProperty()
    {
        return Empresa::forUser()->get();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->forUser()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.pacientes.index', [
            'pacientes' => $this->pacientes,
            'stats' => $this->stats,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}
