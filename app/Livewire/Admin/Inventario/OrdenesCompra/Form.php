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

    public $ordenId = null; // ID de la orden si estamos editando
    public $proveedor_id = '';
    public $proveedor_search = ''; // Búsqueda de proveedores
    public $almacen_id = '';
    public $fecha_emision;
    public $fecha_esperada = '';
    public $observaciones = '';

    // Buscador de productos para agregar al carrito
    public $producto_buscar = '';

    public array $lineas = [];

    public function mount($orden = null)
    {
        if ($orden) {
            // Modo edición
            // Si $orden es un ID (string/int), buscar el modelo
            if (is_numeric($orden)) {
                $ordenModel = OrdenCompra::findOrFail($orden);
            } else {
                $ordenModel = $orden;
            }

            $this->ordenId = $ordenModel->id;
            $this->authorize('edit ordenes-compra');

            // Cargar datos de la orden
            $this->proveedor_id = $ordenModel->proveedor_id;
            $this->almacen_id = $ordenModel->almacen_id;
            $this->fecha_emision = $ordenModel->fecha_emision->format('Y-m-d');
            $this->fecha_esperada = $ordenModel->fecha_esperada?->format('Y-m-d') ?? '';
            $this->observaciones = $ordenModel->observaciones ?? '';

            // Cargar líneas
            $this->lineas = [];
            foreach ($ordenModel->detalles as $detalle) {
                $producto = Producto::find($detalle->producto_id);
                $this->lineas[] = [
                    'producto_id'      => $detalle->producto_id,
                    'cantidad'         => $detalle->cantidad_solicitada,
                    'precio_unitario'  => $detalle->precio_unitario,
                    'subtotal'         => $detalle->subtotal,
                ];
            }

            // Si no hay líneas, agregar una vacía
            if (empty($this->lineas)) {
                $this->agregarLinea();
            }

            // Establecer texto de búsqueda del proveedor
            if ($ordenModel->proveedor) {
                $this->proveedor_search = $ordenModel->proveedor->nombre;
            }
        } else {
            // Modo creación
            $this->fecha_emision = now()->format('Y-m-d');
            // No agregamos línea vacía, el usuario debe usar el buscador
        }
    }

    public function agregarLinea()
    {
        // Este método ya no se usa directamente, ahora usamos agregarProductoAlCarrito
    }

    // Agregar producto al carrito desde el buscador
    public function agregarProductoAlCarrito($productoId)
    {
        if (!$productoId) {
            return;
        }

        $producto = Producto::find($productoId);
        if (!$producto) {
            return;
        }

        // Verificar si el producto ya está en el carrito
        $existe = false;
        foreach ($this->lineas as &$linea) {
            if ($linea['producto_id'] == $productoId) {
                // Si ya existe, incrementar cantidad
                $linea['cantidad'] += 1;
                $linea['subtotal'] = round($linea['cantidad'] * $linea['precio_unitario'], 2);
                $existe = true;
                break;
            }
        }
        unset($linea);

        // Si no existe, agregar nueva línea
        if (!$existe) {
            $this->lineas[] = [
                'producto_id'      => $productoId,
                'cantidad'         => 1,
                'precio_unitario'  => $producto->precio_costo,
                'subtotal'         => $producto->precio_costo,
            ];
        }

        // Limpiar el buscador
        $this->producto_buscar = '';
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
    }

    public function getTotalProperty(): float
    {
        return collect($this->lineas)->sum(fn($l) => (float) ($l['subtotal'] ?? 0));
    }

    // Proveedores filtrados por búsqueda
    public function getProveedoresFiltradosProperty()
    {
        $proveedores = $this->proveedores;

        if (strlen($this->proveedor_search) > 0) {
            $search = strtolower($this->proveedor_search);
            $proveedores = $proveedores->filter(function ($prov) use ($search) {
                return
                    stripos(strtolower($prov->nombre), $search) !== false ||
                    ($prov->rif && stripos(strtolower($prov->rif), $search) !== false) ||
                    ($prov->telefono && stripos(strtolower($prov->telefono), $search) !== false);
            });
        }

        return $proveedores->take(10); // Limitar a 10 resultados
    }

    // Proveedor seleccionado (modelo completo)
    public function getProveedorSeleccionadoProperty()
    {
        if ($this->proveedor_id) {
            return Proveedor::find($this->proveedor_id);
        }
        return null;
    }

    // Seleccionar proveedor
    public function seleccionarProveedor($proveedorId)
    {
        $this->proveedor_id = $proveedorId;

        if ($proveedorId) {
            $proveedor = Proveedor::find($proveedorId);
            $this->proveedor_search = $proveedor ? $proveedor->nombre : '';
        } else {
            $this->proveedor_search = '';
        }
    }

    // Productos filtrados por búsqueda (para el buscador principal)
    public function getProductosFiltradosProperty()
    {
        $productos = $this->productos;

        if (strlen($this->producto_buscar) > 0) {
            $searchLower = strtolower($this->producto_buscar);
            $productos = $productos->filter(function ($prod) use ($searchLower) {
                return
                    stripos(strtolower($prod->nombre), $searchLower) !== false ||
                    ($prod->codigo && stripos(strtolower($prod->codigo), $searchLower) !== false) ||
                    ($prod->sku && stripos(strtolower($prod->sku), $searchLower) !== false);
            });
        }

        return $productos->take(10); // Limitar a 10 resultados
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

    public function update()
    {
        if (!$this->ordenId) {
            return;
        }

        $orden = OrdenCompra::findOrFail($this->ordenId);
        $this->authorize('edit ordenes-compra');

        // Validar que no esté recibida o cancelada
        if (in_array($orden->estado, ['recibida', 'cancelada'])) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No se puede editar una orden recibida o cancelada.', 'duration' => 3000]);
            return;
        }

        $data = $this->validate();

        DB::transaction(function () use ($orden, $data) {
            // Actualizar cabecera
            $orden->update([
                'proveedor_id'  => $data['proveedor_id'],
                'almacen_id'    => $data['almacen_id'],
                'fecha_emision' => $data['fecha_emision'],
                'fecha_esperada'=> $data['fecha_esperada'] ?: null,
                'observaciones' => $data['observaciones'],
            ]);

            // Eliminar detalles antiguos
            $orden->detalles()->delete();

            // Crear nuevos detalles
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

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Orden de compra actualizada exitosamente.', 'duration' => 4000]);
        return redirect()->route('admin.inventario.ordenes-compra.index');
    }

    public function getProveedoresProperty() { return Proveedor::forUser()->activos()->orderBy('nombre')->get(); }
    public function getAlmacenesProperty()   { return Almacen::forUser()->activos()->orderBy('nombre')->get(); }
    public function getProductosProperty()   { return Producto::forUser()->activos()->orderBy('nombre')->get(); }

    public function render()
    {
        return view('livewire.admin.inventario.ordenes-compra.form', [
            'ordenId'             => $this->ordenId,
            'proveedores'         => $this->proveedores,
            'proveedores_filtrados' => $this->proveedores_filtrados,
            'proveedor_seleccionado'=> $this->proveedor_seleccionado,
            'almacenes'           => $this->almacenes,
            'productos'           => $this->productos,
            'productos_filtrados' => $this->productos_filtrados,
        ])->layout($this->getLayout());
    }
}
