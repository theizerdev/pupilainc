<?php

namespace App\Livewire\Admin\Inventario\Proveedores;

use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    public $selected = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc') : 'asc';
        $this->sortField = $field;
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit proveedores');
        $p = Proveedor::findOrFail($id);
        $p->update(['status' => !$p->status]);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Estado de '{$p->nombre}' actualizado.", 'duration' => 3000]);
    }

    public function delete($id)
    {
        $this->authorize('delete proveedores');
        $p = Proveedor::findOrFail($id);
        if ($p->productos()->exists() || $p->ordenesCompra()->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No se puede eliminar: tiene productos u órdenes asociadas.', 'duration' => 4000]);
            return;
        }
        $p->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Proveedor eliminado.', 'duration' => 3000]);
    }

    public function deleteSelected()
    {
        $this->authorize('delete proveedores');
        if (empty($this->selected)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No hay proveedores seleccionados.', 'duration' => 3000]);
            return;
        }

        $deleted = 0;
        $skipped = [];

        foreach ($this->selected as $id) {
            $p = Proveedor::find($id);
            if (! $p) continue;
            if ($p->productos()->exists() || $p->ordenesCompra()->exists()) {
                $skipped[] = $p->nombre;
                continue;
            }
            $p->delete();
            $deleted++;
        }

        $this->selected = [];

        if ($deleted > 0) {
            $message = "$deleted proveedor(es) eliminado(s).";
            if (count($skipped)) {
                $message .= ' No se eliminaron: ' . implode(', ', array_slice($skipped, 0, 5));
                if (count($skipped) > 5) $message .= '...';
            }
            $this->dispatch('notify', ['type' => 'success', 'message' => $message, 'duration' => 5000]);
        } else {
            $message = 'No se eliminaron proveedores. Algunos tienen relaciones o no existen.';
            if (count($skipped)) {
                $message .= ' No se eliminaron: ' . implode(', ', array_slice($skipped, 0, 5));
                if (count($skipped) > 5) $message .= '...';
            }
            $this->dispatch('notify', ['type' => 'error', 'message' => $message, 'duration' => 5000]);
        }
    }

    public function getProveedoresProperty()
    {
        return Proveedor::forUser()
            ->when($this->search, fn($q) => $q->where('nombre', 'like', "%{$this->search}%")
                ->orWhere('documento', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->withCount('productos', 'ordenesCompra')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $q = Proveedor::forUser();
        return [
            'total'   => $q->count(),
            'activos' => $q->where('status', true)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.inventario.proveedores.index', [
            'proveedores' => $this->proveedores,
            'stats'       => $this->stats,
        ])->layout($this->getLayout());
    }
}
