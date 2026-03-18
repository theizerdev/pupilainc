<?php

namespace App\Livewire\Admin\Categorias;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $activo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'activo' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActivo()
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
        $this->authorize('admin.categorias.destroy');
        
        try {
            $categoria = Categoria::findOrFail($id);
            $nombreCategoria = $categoria->nombre;
            $categoria->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Categoría '{$nombreCategoria}' eliminada exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la categoría: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function toggleActivo($id)
    {
        $this->authorize('edit categorias');
        
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->activo = !$categoria->activo;
            $categoria->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Estado de la categoría actualizado exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function getCategoriasProperty()
    {
        return Categoria::with(['empresa', 'sucursal'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activo !== '', function ($query) {
                $query->where('activo', $this->activo);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = Categoria::forUser();
        
        return [
            'total' => $query->count(),
            'activas' => $query->where('activo', true)->count(),
            'inactivas' => $query->where('activo', false)->count(),
        ];
    }

    public function deleteCategoria($categoria)
    {
        $categoria = Categoria::find($categoria);
        $nombreCategoria = $categoria->nombre;
        $categoria->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Categoría '{$nombreCategoria}' eliminada exitosamente.",
            'duration' => 4000
        ]);
    }

    public function render()
    {
        return view('livewire.admin.categorias.index', [
            'categorias' => $this->categorias,
            'stats' => $this->stats
        ])->layout($this->getLayout());
    }
}
