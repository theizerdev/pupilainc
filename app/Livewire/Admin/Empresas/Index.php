<?php

namespace App\Livewire\Admin\Empresas;
use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use App\Traits\Exportable;

class Index extends Component
{
    use WithPagination, Exportable, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $sortBy = 'razon_social';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'razon_social'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        if (!Auth::user()->can('access empresas')) {
            abort(403, 'No tienes permiso para acceder a empresas.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleStatus($empresaId)
    {
        if (!Auth::user()->can('edit empresas')) {
            session()->flash('error', 'No tienes permiso para editar empresas.');
            return;
        }

        $empresa = Empresa::findOrFail($empresaId);

        if ($empresa->status === 'activo') {
            $empresa->status = 'inactivo';
            $message = 'Empresa desactivada exitosamente.';
        } else {
            $empresa->status = 'activo';
            $message = 'Empresa activada exitosamente.';
        }

        $empresa->save();
        session()->flash('message', $message);
    }

    public function deleteEmpresa($empresaId)
    {
        if (!Auth::user()->can('delete empresas')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar empresas.',
                'duration' => 4000
            ]);
            return;
        }

        $empresa = Empresa::findOrFail($empresaId);

        // Verificar si tiene sucursales asociadas
        if ($empresa->sucursales()->exists()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar la empresa porque tiene sucursales asociadas.',
                'duration' => 4000
            ]);
            return;
        }

        try {
            $nombreEmpresa = $empresa->razon_social;
            $empresa->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Empresa '{$nombreEmpresa}' eliminada exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la empresa: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'sortBy', 'sortDirection', 'perPage']);
        $this->sortBy = 'razon_social';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
    }

    protected function getExportQuery()
    {
        return $this->getBaseQuery();
    }

    protected function getExportHeaders(): array
    {
        return ['ID', 'Razón Social', 'Documento', 'Email', 'Teléfono', 'Dirección', 'Status'];
    }

    protected function formatExportRow($empresa): array
    {
        return [
            $empresa->id,
            $empresa->razon_social,
            $empresa->documento,
            $empresa->email,
            $empresa->telefono,
            $empresa->direccion,
            $empresa->status ? 'Activo' : 'Inactivo'
        ];
    }

    private function getBaseQuery()
    {
        $query = Empresa::forUser()->with(['sucursales']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('razon_social', 'like', '%' . $this->search . '%')
                  ->orWhere('documento', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getBaseQuery();

        // Ordenar
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Obtener empresas paginadas
        $empresas = $query->paginate($this->perPage);

        // Calcular estadísticas
        $totalEmpresas = Empresa::forUser()->count();
        $empresasActivas = Empresa::forUser()->where('status', 'activo')->count();
        $empresasInactivas = Empresa::forUser()->where('status', 'inactivo')->count();

         return $this->renderWithLayout('livewire.admin.empresas.index', compact(
            'empresas',
            'totalEmpresas',
            'empresasActivas',
            'empresasInactivas',

        ), [
            'title' => 'Empresas',
            'description' => 'Gestión de empresas del sistema',
            'breadcrumb' => [
                'admin.dashboard' => 'Dashboard',
                'admin.empresas.index' => 'Empresas'
            ]
        ]);
    }
}