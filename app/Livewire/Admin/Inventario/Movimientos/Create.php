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
    public $producto_search = ''; // Búsqueda de productos
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

    // Productos filtrados por búsqueda
    public function getProductosFiltradosProperty()
    {
        $productos = $this->productos;

        if (strlen($this->producto_search) > 0) {
            $search = strtolower($this->producto_search);
            $productos = $productos->filter(function ($prod) use ($search) {
                return
                    stripos(strtolower($prod->nombre), $search) !== false ||
                    ($prod->codigo && stripos(strtolower($prod->codigo), $search) !== false) ||
                    ($prod->sku && stripos(strtolower($prod->sku), $search) !== false);
            });
        }

        return $productos->take(10); // Limitar a 10 resultados
    }

    // Producto seleccionado (modelo completo)
    public function getProductoSeleccionadoProperty()
    {
        if ($this->producto_id) {
            return Producto::find($this->producto_id);
        }
        return null;
    }

    // Seleccionar producto
    public function seleccionarProducto($productoId)
    {
        $this->producto_id = $productoId;

        if ($productoId) {
            $producto = Producto::find($productoId);
            $this->producto_search = $producto ? "{$producto->nombre} ({$producto->codigo})" : '';
        } else {
            $this->producto_search = '';
        }

        $this->actualizarStock();
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
            'productos'            => $this->productos,
            'productos_filtrados'  => $this->productos_filtrados,
            'producto_seleccionado'=> $this->producto_seleccionado,
            'almacenes'            => $this->almacenes,
            'tiposMovimiento'      => InventarioMovimiento::TIPOS,
        ])->layout($this->getLayout());
    }
}
