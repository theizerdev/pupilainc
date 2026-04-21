<?php

namespace App\Livewire\Admin\Inventario\Marcas;

use App\Models\Marca;
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

    protected $queryString = [
        'search'        => ['except' => ''],
        'status'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
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
        $this->authorize('edit marcas');

        $marca = Marca::findOrFail($id);
        $marca->status = !$marca->status;
        $marca->save();

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => "Estado de '{$marca->nombre}' actualizado.",
            'duration' => 3000,
        ]);
    }

    public function delete($id)
    {
        $this->authorize('delete marcas');

        Marca::findOrFail($id)->delete();

        $this->dispatch('notify', [
            'type'     => 'success',
            'message'  => 'Marca eliminada exitosamente.',
            'duration' => 3000,
        ]);
    }

    public function getMarcasProperty()
    {
        return Marca::forUser()
            ->when($this->search, fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('descripcion', 'like', '%' . $this->search . '%'))
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = Marca::forUser();
        return [
            'total'    => $query->count(),
            'activas'  => $query->where('status', true)->count(),
            'inactivas'=> $query->where('status', false)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.inventario.marcas.index', [
            'marcas' => $this->marcas,
            'stats'  => $this->stats,
        ])->layout($this->getLayout());
    }
}
