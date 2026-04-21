<?php

namespace App\Livewire\Admin\Inventario\Productos;

use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\InventarioMovimiento;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Storage;

class Form extends Component
{
    use HasDynamicLayout, WithFileUploads;

    public ?Producto $producto = null;
    public bool $isEdit = false;

    public $nombre = '';
    public $codigo = '';
    public $sku = '';
    public $descripcion = '';
    public $categoria_producto_id = '';
    public $marca_id = '';
    public $proveedor_id = '';
    public $unidad_medida = 'unidad';
    public $precio_costo = 0;
    public $precio_venta = 0;
    public $stock_minimo = 0;
    public $stock_maximo = '';
    public $punto_reorden = 0;
    public $fecha_vencimiento = '';
    public $ubicacion_fisica = '';
    public $requiere_receta = false;
    public $es_medicamento = false;
    public $status = true;
    public $imagen;
    public $imagen_actual = null;
    public $eliminar_imagen = false;

    // Stock inicial (solo creación)
    public $stock_inicial = 0;
    public $almacen_inicial_id = '';

    // Preview código
    public $codigo_preview = '';

    const UNIDADES = [
        'unidad'  => 'Unidad',   'caja'    => 'Caja',          'frasco'  => 'Frasco',
        'ampolla' => 'Ampolla',  'tableta' => 'Tableta',        'capsula' => 'Cápsula',
        'ml'      => 'ml',       'mg'      => 'mg',             'g'       => 'Gramo (g)',
        'kg'      => 'Kg',       'litro'   => 'Litro',          'par'     => 'Par',
        'rollo'   => 'Rollo',    'sobre'   => 'Sobre',          'vial'    => 'Vial',
    ];

    protected function rules()
    {
        return [
            'nombre'               => 'required|string|max:255',
            'codigo'               => 'nullable|string|max:50',
            'sku'                  => 'nullable|string|max:50',
            'descripcion'          => 'nullable|string|max:1000',
            'categoria_producto_id'=> 'nullable|exists:categorias_producto,id',
            'marca_id'             => 'nullable|exists:marcas,id',
            'proveedor_id'         => 'nullable|exists:proveedores,id',
            'unidad_medida'        => 'required|string|max:30',
            'precio_costo'         => 'required|numeric|min:0',
            'precio_venta'         => 'required|numeric|min:0|gte:precio_costo',
            'stock_minimo'         => 'required|integer|min:0',
            'stock_maximo'         => 'nullable|integer|min:0|gte:stock_minimo',
            'punto_reorden'        => 'required|integer|min:0',
            'fecha_vencimiento'    => 'nullable|date',
            'ubicacion_fisica'     => 'nullable|string|max:255',
            'requiere_receta'      => 'boolean',
            'es_medicamento'       => 'boolean',
            'status'               => 'boolean',
            'imagen'               => 'nullable|image|max:2048',
            'stock_inicial'        => 'integer|min:0',
            'almacen_inicial_id'   => 'nullable|exists:almacenes,id',
        ];
    }

    protected $messages = [
        'precio_venta.gte'   => 'El precio de venta no puede ser menor al precio de costo.',
        'stock_maximo.gte'   => 'El stock máximo no puede ser menor al stock mínimo.',
    ];

    public function mount(?Producto $producto = null)
    {
        if ($producto && $producto->exists) {
            $this->producto      = $producto;
            $this->isEdit        = true;
            $this->imagen_actual = $producto->imagen;

            $this->fill($producto->only(
                'nombre', 'codigo', 'sku', 'descripcion',
                'categoria_producto_id', 'marca_id', 'proveedor_id',
                'unidad_medida', 'precio_costo', 'precio_venta',
                'stock_minimo', 'stock_maximo', 'punto_reorden',
                'ubicacion_fisica', 'requiere_receta', 'es_medicamento', 'status'
            ));

            // Fix: formatear fecha para input type="date"
            $this->fecha_vencimiento = $producto->fecha_vencimiento
                ? $producto->fecha_vencimiento->format('Y-m-d')
                : '';
        } else {
            // Preview del próximo código
            $this->codigo_preview = 'PROD-' . str_pad(
                Producto::withoutGlobalScopes()->count() + 1, 5, '0', STR_PAD_LEFT
            );
        }
    }

    // ─── Margen en tiempo real ────────────────────────────────────
    public function getMargen(): float
    {
        $costo = (float) $this->precio_costo;
        $venta = (float) $this->precio_venta;
        if ($costo <= 0 || $venta <= 0) return 0;
        return round((($venta - $costo) / $venta) * 100, 1);
    }

    public function getGanancia(): float
    {
        return round((float) $this->precio_venta - (float) $this->precio_costo, 2);
    }

    public function getMargenColor(): string
    {
        $margen = $this->getMargen();
        if ($margen < 0)  return 'danger';
        if ($margen < 10) return 'warning';
        if ($margen < 30) return 'info';
        return 'success';
    }

    // ─── Stock actual en edición ───────────────────────────────────
    public function getStockActual()
    {
        if (!$this->isEdit || !$this->producto) return collect();
        return $this->producto->stocks()->with('almacen')->get();
    }

    public function getStockTotal(): int
    {
        if (!$this->isEdit || !$this->producto) return 0;
        return $this->producto->stockTotal();
    }

    // ─── Save ──────────────────────────────────────────────────────
    public function save()
    {
        $this->isEdit
            ? $this->authorize('edit productos')
            : $this->authorize('create productos');

        $data = $this->validate();

        // Imagen
        $imagenPath = $this->imagen_actual;
        if ($this->eliminar_imagen) {
            if ($this->imagen_actual) Storage::disk('public')->delete($this->imagen_actual);
            $imagenPath = null;
        }
        if ($this->imagen) {
            if ($this->imagen_actual) Storage::disk('public')->delete($this->imagen_actual);
            $imagenPath = $this->imagen->store('productos', 'public');
        }

        $payload = collect($data)
            ->except(['stock_inicial', 'almacen_inicial_id', 'imagen'])
            ->merge([
                'empresa_id'  => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'imagen'      => $imagenPath,
            ])
            ->toArray();

        if ($this->isEdit) {
            $this->producto->update($payload);
            $msg = "Producto '{$this->nombre}' actualizado.";
        } else {
            $producto = Producto::create($payload);
            $msg      = "Producto '{$this->nombre}' creado.";

            if ($data['stock_inicial'] > 0 && $data['almacen_inicial_id']) {
                InventarioMovimiento::registrar(
                    productoId:    $producto->id,
                    almacenId:     $data['almacen_inicial_id'],
                    tipo:          'entrada',
                    cantidad:      $data['stock_inicial'],
                    costoUnitario: $data['precio_costo'],
                    referencia:    'Stock inicial',
                    observacion:   'Carga inicial de inventario',
                );
            }
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => $msg, 'duration' => 4000]);
        return redirect()->route('admin.inventario.productos.index');
    }

    public function getCategoriasProperty()  { return CategoriaProducto::forUser()->activas()->orderBy('nombre')->get(); }
    public function getMarcasProperty()      { return Marca::forUser()->activas()->orderBy('nombre')->get(); }
    public function getProveedoresProperty() { return Proveedor::forUser()->activos()->orderBy('nombre')->get(); }
    public function getAlmacenesProperty()   { return Almacen::forUser()->activos()->orderBy('nombre')->get(); }

    public function render()
    {
        return view('livewire.admin.inventario.productos.form', [
            'categorias'  => $this->categorias,
            'marcas'      => $this->marcas,
            'proveedores' => $this->proveedores,
            'almacenes'   => $this->almacenes,
            'unidades'    => self::UNIDADES,
        ])->layout($this->getLayout());
    }
}
