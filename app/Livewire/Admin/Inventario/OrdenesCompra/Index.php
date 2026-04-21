<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $estado = '';
    public $proveedor_id = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search'      => ['except' => ''],
        'estado'      => ['except' => ''],
        'proveedor_id'=> ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'estado', 'proveedor_id']);
        $this->resetPage();
    }

    public function cancelar($id)
    {
        $this->authorize('edit ordenes-compra');
        $oc = OrdenCompra::findOrFail($id);
        if (in_array($oc->estado, ['recibida', 'cancelada'])) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No se puede cancelar esta orden.', 'duration' => 3000]);
            return;
        }
        $oc->update(['estado' => 'cancelada']);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Orden #{$oc->numero} cancelada.", 'duration' => 3000]);
    }

    public function recibirCompleta($id)
    {
        $this->authorize('edit ordenes-compra');
        $oc = OrdenCompra::with('detalles')->findOrFail($id);
        $oc->recibirCompleta();
        $this->dispatch('notify', ['type' => 'success', 'message' => "Orden #{$oc->numero} recibida. Stock actualizado.", 'duration' => 4000]);
    }

    public function getOrdenesProperty()
    {
        return OrdenCompra::forUser()
            ->with(['proveedor', 'almacen'])
            ->withCount('detalles')
            ->when($this->search, fn($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('proveedor', fn($p) => $p->where('nombre', 'like', "%{$this->search}%")))
            ->when($this->estado,       fn($q) => $q->where('estado', $this->estado))
            ->when($this->proveedor_id, fn($q) => $q->where('proveedor_id', $this->proveedor_id))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        $q = OrdenCompra::forUser();
        return [
            'total'    => $q->count(),
            'activas'  => $q->activas()->count(),
            'borradores'=> $q->where('estado', 'borrador')->count(),
        ];
    }

    public function getProveedoresProperty()
    {
        return Proveedor::forUser()->activos()->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.admin.inventario.ordenes-compra.index', [
            'ordenes'     => $this->ordenes,
            'stats'       => $this->stats,
            'proveedores' => $this->proveedores,
            'estados'     => OrdenCompra::ESTADOS,
        ])->layout($this->getLayout());
    }
}
