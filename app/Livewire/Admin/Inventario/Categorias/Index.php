<?php

namespace App\Livewire\Admin\Inventario\Categorias;

use App\Models\CategoriaProducto;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selected = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit categorias-producto');

        $categoria = CategoriaProducto::findOrFail($id);
        $categoria->status = !$categoria->status;
        $categoria->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Estado de '{$categoria->nombre}' actualizado.",
            'duration' => 3000,
        ]);
    }

    public function delete($id)
    {
        $this->authorize('delete categorias-producto');

        CategoriaProducto::findOrFail($id)->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Categoría eliminada exitosamente.',
            'duration' => 3000,
        ]);
    }

    public function deleteSelected()
    {
        $this->authorize('delete categorias-producto');
        if (empty($this->selected)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No hay categorías seleccionadas.', 'duration' => 3000]);
            return;
        }

        $deleted = 0;
        $skipped = [];

        foreach ($this->selected as $id) {
            $c = CategoriaProducto::find($id);
            if (! $c) continue;
            // Evitar eliminar si hay productos relacionados
            if (\App\Models\Producto::where('categoria_producto_id', $id)->exists()) {
                $skipped[] = $c->nombre;
                continue;
            }
            $c->delete();
            $deleted++;
        }

        $this->selected = [];

        if ($deleted > 0) {
            $message = "$deleted categoría(s) eliminada(s).";
            if (count($skipped)) {
                $message .= ' No se eliminaron: ' . implode(', ', array_slice($skipped, 0, 5));
                if (count($skipped) > 5) $message .= '...';
            }
            $this->dispatch('notify', ['type' => 'success', 'message' => $message, 'duration' => 5000]);
        } else {
            $message = 'No se eliminaron categorías. Algunos registros tienen relaciones.';
            if (count($skipped)) {
                $message .= ' No se eliminaron: ' . implode(', ', array_slice($skipped, 0, 5));
                if (count($skipped) > 5) $message .= '...';
            }
            $this->dispatch('notify', ['type' => 'error', 'message' => $message, 'duration' => 5000]);
        }
    }

    public function getCategoriasProperty()
    {
        return CategoriaProducto::forUser()
            ->when($this->search, fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('descripcion', 'like', '%' . $this->search . '%'))
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = CategoriaProducto::forUser();
        return [
            'total'    => $query->count(),
            'activas'  => $query->where('status', true)->count(),
            'inactivas'=> $query->where('status', false)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.inventario.categorias.index', [
            'categorias' => $this->categorias,
            'stats'      => $this->stats,
        ])->layout($this->getLayout());
    }
}
