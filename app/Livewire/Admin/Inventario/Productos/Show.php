<?php

namespace App\Livewire\Admin\Inventario\Productos;

use App\Models\Producto;
use App\Models\Almacen;
use App\Models\InventarioMovimiento;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use WithPagination, HasDynamicLayout;

    public Producto $producto;
    public $almacen_id = '';
    public $tipo = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    protected $paginationTheme = 'bootstrap';

    public function mount(Producto $producto)
    {
        $this->producto = $producto;
    }

    public function resetFiltros()
    {
        $this->reset(['almacen_id', 'tipo', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function getMovimientosProperty()
    {
        return InventarioMovimiento::with(['almacen', 'user'])
            ->where('producto_id', $this->producto->id)
            ->when($this->almacen_id, fn($q) => $q->where('almacen_id', $this->almacen_id))
            ->when($this->tipo,       fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->fecha_desde, fn($q) => $q->whereDate('created_at', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn($q) => $q->whereDate('created_at', '<=', $this->fecha_hasta))
            ->latest()
            ->paginate(20);
    }

    public function getStockPorAlmacenProperty()
    {
        return $this->producto->stocks()->with('almacen')->get();
    }

    public function getAlmacenesProperty()
    {
        return Almacen::forUser()->activos()->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.admin.inventario.productos.show', [
            'movimientos'      => $this->movimientos,
            'stockPorAlmacen'  => $this->stockPorAlmacen,
            'almacenes'        => $this->almacenes,
            'tiposMovimiento'  => InventarioMovimiento::TIPOS,
        ])->layout($this->getLayout());
    }
}
