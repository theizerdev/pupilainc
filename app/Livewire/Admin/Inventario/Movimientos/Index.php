<?php

namespace App\Livewire\Admin\Inventario\Movimientos;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Almacen;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $almacen_id = '';
    public $tipo = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    protected $queryString = [
        'search'      => ['except' => ''],
        'almacen_id'  => ['except' => ''],
        'tipo'        => ['except' => ''],
        'fecha_desde' => ['except' => ''],
        'fecha_hasta' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'almacen_id', 'tipo', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function getMovimientosProperty()
    {
        return InventarioMovimiento::forUser()
            ->with(['producto', 'almacen', 'user'])
            ->when($this->search, fn($q) => $q->whereHas('producto', fn($p) =>
                $p->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('codigo', 'like', "%{$this->search}%")
            ))
            ->when($this->almacen_id, fn($q) => $q->where('almacen_id', $this->almacen_id))
            ->when($this->tipo,       fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->fecha_desde, fn($q) => $q->whereDate('created_at', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn($q) => $q->whereDate('created_at', '<=', $this->fecha_hasta))
            ->latest()
            ->paginate(20);
    }

    public function getAlmacenesProperty()
    {
        return Almacen::forUser()->activos()->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.admin.inventario.movimientos.index', [
            'movimientos'    => $this->movimientos,
            'almacenes'      => $this->almacenes,
            'tiposMovimiento'=> InventarioMovimiento::TIPOS,
        ])->layout($this->getLayout());
    }
}
