<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\Producto;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    use HasDynamicLayout;

    public $proveedor_id = '';
    public $almacen_id = '';
    public $fecha_emision;
    public $fecha_esperada = '';
    public $observaciones = '';

    public array $lineas = [];

    public function mount()
    {
        $this->fecha_emision = now()->format('Y-m-d');
        $this->agregarLinea();
    }

    public function agregarLinea()
    {
        $this->lineas[] = [
            'producto_id'      => '',
            'cantidad'         => 1,
            'precio_unitario'  => 0,
            'subtotal'         => 0,
        ];
    }

    public function quitarLinea(int $index)
    {
        if (count($this->lineas) > 1) {
            array_splice($this->lineas, $index, 1);
        }
    }

    public function updatedLineas($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if (in_array($field, ['cantidad', 'precio_unitario'])) {
            $cant   = (float) ($this->lineas[$index]['cantidad'] ?? 0);
            $precio = (float) ($this->lineas[$index]['precio_unitario'] ?? 0);
            $this->lineas[$index]['subtotal'] = round($cant * $precio, 2);
        }

        if ($field === 'producto_id' && $value) {
            $p = Producto::find($value);
            if ($p) {
                $this->lineas[$index]['precio_unitario'] = $p->precio_costo;
                $cant = (float) ($this->lineas[$index]['cantidad'] ?? 1);
                $this->lineas[$index]['subtotal'] = round($cant * (float) $p->precio_costo, 2);
            }
        }
    }

    public function getTotalProperty(): float
    {
        return collect($this->lineas)->sum(fn($l) => (float) ($l['subtotal'] ?? 0));
    }

    protected function rules()
    {
        return [
            'proveedor_id'          => 'required|exists:proveedores,id',
            'almacen_id'            => 'required|exists:almacenes,id',
            'fecha_emision'         => 'required|date',
            'fecha_esperada'        => 'nullable|date|after_or_equal:fecha_emision',
            'observaciones'         => 'nullable|string|max:1000',
            'lineas'                => 'required|array|min:1',
            'lineas.*.producto_id'  => 'required|exists:productos,id',
            'lineas.*.cantidad'     => 'required|integer|min:1',
            'lineas.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function store()
    {
        $this->authorize('create ordenes-compra');
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $orden = OrdenCompra::create([
                'numero'       => OrdenCompra::generarNumero(),
                'proveedor_id' => $data['proveedor_id'],
                'almacen_id'   => $data['almacen_id'],
                'estado'       => 'borrador',
                'fecha_emision'=> $data['fecha_emision'],
                'fecha_esperada'=> $data['fecha_esperada'] ?: null,
                'observaciones'=> $data['observaciones'],
                'user_id'      => auth()->id(),
                'empresa_id'   => auth()->user()->empresa_id,
                'sucursal_id'  => auth()->user()->sucursal_id,
            ]);

            foreach ($data['lineas'] as $linea) {
                $orden->detalles()->create([
                    'producto_id'        => $linea['producto_id'],
                    'cantidad_solicitada'=> $linea['cantidad'],
                    'precio_unitario'    => $linea['precio_unitario'],
                    'subtotal'           => $linea['cantidad'] * $linea['precio_unitario'],
                ]);
            }

            $orden->recalcularTotal();
        });

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Orden de compra creada exitosamente.', 'duration' => 4000]);
        return redirect()->route('admin.inventario.ordenes-compra.index');
    }

    public function getProveedoresProperty() { return Proveedor::forUser()->activos()->orderBy('nombre')->get(); }
    public function getAlmacenesProperty()   { return Almacen::forUser()->activos()->orderBy('nombre')->get(); }
    public function getProductosProperty()   { return Producto::forUser()->activos()->orderBy('nombre')->get(); }

    public function render()
    {
        return view('livewire.admin.inventario.ordenes-compra.form', [
            'proveedores' => $this->proveedores,
            'almacenes'   => $this->almacenes,
            'productos'   => $this->productos,
        ])->layout($this->getLayout());
    }
}
