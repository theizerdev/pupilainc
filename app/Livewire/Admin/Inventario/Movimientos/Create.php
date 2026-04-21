<?php

namespace App\Livewire\Admin\Inventario\Movimientos;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Almacen;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $producto_id = '';
    public $almacen_id = '';
    public $tipo = 'ajuste_positivo';
    public $cantidad = 1;
    public $costo_unitario = '';
    public $referencia = '';
    public $observacion = '';

    public $stockActual = 0;

    protected function rules()
    {
        return [
            'producto_id'   => 'required|exists:productos,id',
            'almacen_id'    => 'required|exists:almacenes,id',
            'tipo'          => 'required|in:' . implode(',', array_keys(InventarioMovimiento::TIPOS)),
            'cantidad'      => 'required|integer|min:1',
            'costo_unitario'=> 'nullable|numeric|min:0',
            'referencia'    => 'nullable|string|max:255',
            'observacion'   => 'nullable|string|max:500',
        ];
    }

    public function updatedProductoId()  { $this->actualizarStock(); }
    public function updatedAlmacenId()   { $this->actualizarStock(); }

    private function actualizarStock()
    {
        if ($this->producto_id && $this->almacen_id) {
            $p = Producto::find($this->producto_id);
            $this->stockActual = $p ? $p->stockEnAlmacen((int) $this->almacen_id) : 0;
        }
    }

    public function store()
    {
        $this->authorize('create movimientos-inventario');
        $data = $this->validate();

        InventarioMovimiento::registrar(
            productoId:    $data['producto_id'],
            almacenId:     $data['almacen_id'],
            tipo:          $data['tipo'],
            cantidad:      $data['cantidad'],
            costoUnitario: $data['costo_unitario'] ?: null,
            referencia:    $data['referencia'] ?: null,
            observacion:   $data['observacion'] ?: null,
        );

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Movimiento registrado exitosamente.', 'duration' => 4000]);
        return redirect()->route('admin.inventario.movimientos.index');
    }

    public function getProductosProperty()
    {
        return Producto::forUser()->activos()->orderBy('nombre')->get();
    }

    public function getAlmacenesProperty()
    {
        return Almacen::forUser()->activos()->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.admin.inventario.movimientos.create', [
            'productos'      => $this->productos,
            'almacenes'      => $this->almacenes,
            'tiposMovimiento'=> InventarioMovimiento::TIPOS,
        ])->layout($this->getLayout());
    }
}
