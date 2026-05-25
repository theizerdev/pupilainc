<?php

namespace App\Livewire\Admin\Inventario\Productos;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\ProductoImagen;
use App\Models\TipoVariante;
use App\Models\ValorVariante;
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

    // ─── Tab activo ───────────────────────────────────────────────
    public string $activeTab = 'general';

    // ─── Info General ──────────────────────────────────────────────
    public $nombre = '';
    public $codigo = '';
    public $sku = '';
    public $descripcion = '';
    public $categoria_producto_id = '';
    public $categoria_search = ''; // Búsqueda de categorías
    public $marca_id = '';
    public $marca_search = ''; // Búsqueda de marcas
    public $proveedor_id = '';
    public $proveedor_search = ''; // Búsqueda de proveedores
    public $unidad_medida = 'unidad';
    public $codigo_barras = '';
    public $dimensiones = '';
    public $ubicacion_fisica = '';

    // ─── Precios / Stock ──────────────────────────────────────────
    public $precio_costo = 0;
    public $precio_venta = 0;
    public $stock_minimo = 0;
    public $stock_maximo = '';
    public $punto_reorden = 0;
    public $peso = '';
    public $fecha_vencimiento = '';
    public $stock_inicial = 0;
    public $almacen_inicial_id = '';

    // ─── Opciones ─────────────────────────────────────────────────
    public $requiere_receta = false;
    public $es_medicamento = false;
    public $status = true;

    // ─── Fiscal ───────────────────────────────────────────────────
    public $aplica_iva = false;
    public $exento_iva = false;
    public $iva_alicuota = 0;

    // ─── Imagen principal (legacy) ───────────────────────────────
    public $imagen;
    public $imagen_actual = null;
    public $eliminar_imagen = false;

    // ─── Galería de imágenes ──────────────────────────────────────
    public $imagenes_nuevas = [];
    public $imagenes_existentes = [];
    public $imagenes_a_eliminar = [];
    public $imagen_principal_id = null;

    // ─── Variantes ─────────────────────────────────────────────────
    public $variantes = [];
    public $variantes_imagenes = [];

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

    // Marcas filtradas por búsqueda
    public function getMarcasFiltradasProperty()
    {
        $marcas = $this->marcas;

        if (strlen($this->marca_search) > 0) {
            $search = strtolower($this->marca_search);
            $marcas = $marcas->filter(function ($marca) use ($search) {
                return stripos(strtolower($marca->nombre), $search) !== false;
            });
        }

        return $marcas->take(10); // Limitar a 10 resultados
    }

    // Marca seleccionada (modelo completo)
    public function getMarcaSeleccionadaProperty()
    {
        if ($this->marca_id) {
            return Marca::find($this->marca_id);
        }
        return null;
    }

    // Seleccionar marca
    public function seleccionarMarca($marcaId)
    {
        $this->marca_id = $marcaId;

        if ($marcaId) {
            $marca = Marca::find($marcaId);
            $this->marca_search = $marca ? $marca->nombre : '';
        } else {
            $this->marca_search = '';
        }
    }

    // Categorías filtradas por búsqueda
    public function getCategoriasFiltradasProperty()
    {
        $categorias = $this->categorias;

        if (strlen($this->categoria_search) > 0) {
            $search = strtolower($this->categoria_search);
            $categorias = $categorias->filter(function ($cat) use ($search) {
                return stripos(strtolower($cat->nombre), $search) !== false;
            });
        }

        return $categorias->take(10); // Limitar a 10 resultados
    }

    // Categoría seleccionada (modelo completo)
    public function getCategoriaSeleccionadaProperty()
    {
        if ($this->categoria_producto_id) {
            return CategoriaProducto::find($this->categoria_producto_id);
        }
        return null;
    }

    // Seleccionar categoría
    public function seleccionarCategoria($categoriaId)
    {
        $this->categoria_producto_id = $categoriaId;

        if ($categoriaId) {
            $categoria = CategoriaProducto::find($categoriaId);
            $this->categoria_search = $categoria ? $categoria->nombre : '';
        } else {
            $this->categoria_search = '';
        }
    }

    // Propiedades para los modales de creación rápida
    public $showModalCategoria = false;
    public $showModalMarca = false;
    public $showModalProveedor = false;

    // Datos para nueva categoría
    public $nueva_categoria_nombre = '';
    public $nueva_categoria_descripcion = '';

    // Datos para nueva marca
    public $nueva_marca_nombre = '';

    // Datos para nuevo proveedor
    public $nuevo_proveedor_nombre = '';
    public $nuevo_proveedor_rif = '';
    public $nuevo_proveedor_telefono = '';
    public $nuevo_proveedor_email = '';

    // Crear nueva categoría desde modal
    public function crearNuevaCategoria()
    {
        $this->validate([
            'nueva_categoria_nombre' => 'required|string|max:255',
            'nueva_categoria_descripcion' => 'nullable|string|max:500',
        ]);

        $categoria = CategoriaProducto::create([
            'nombre' => $this->nueva_categoria_nombre,
            'descripcion' => $this->nueva_categoria_descripcion,
            'empresa_id' => auth()->user()->empresa_id,
            'status' => true,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Categoría '{$categoria->nombre}' creada exitosamente.",
            'duration' => 3000
        ]);

        // Seleccionar automáticamente la nueva categoría
        $this->seleccionarCategoria($categoria->id);

        // Limpiar y cerrar modal
        $this->reset(['nueva_categoria_nombre', 'nueva_categoria_descripcion']);
        $this->showModalCategoria = false;

        // Recargar listas
        $this->categorias = CategoriaProducto::forUser()->activas()->orderBy('nombre')->get();
    }

    // Crear nueva marca desde modal
    public function crearNuevaMarca()
    {
        $this->validate([
            'nueva_marca_nombre' => 'required|string|max:255',
        ]);

        $marca = Marca::create([
            'nombre' => $this->nueva_marca_nombre,
            'empresa_id' => auth()->user()->empresa_id,
            'status' => true,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Marca '{$marca->nombre}' creada exitosamente.",
            'duration' => 3000
        ]);

        // Seleccionar automáticamente la nueva marca
        $this->seleccionarMarca($marca->id);

        // Limpiar y cerrar modal
        $this->reset('nueva_marca_nombre');
        $this->showModalMarca = false;

        // Recargar listas
        $this->marcas = Marca::forUser()->activas()->orderBy('nombre')->get();
    }

    // Crear nuevo proveedor desde modal
    public function crearNuevoProveedor()
    {
        $this->validate([
            'nuevo_proveedor_nombre' => 'required|string|max:255',
            'nuevo_proveedor_rif' => 'nullable|string|max:20',
            'nuevo_proveedor_telefono' => 'nullable|string|max:20',
            'nuevo_proveedor_email' => 'nullable|email|max:255',
        ]);

        $proveedor = Proveedor::create([
            'nombre' => $this->nuevo_proveedor_nombre,
            'rif' => $this->nuevo_proveedor_rif,
            'telefono' => $this->nuevo_proveedor_telefono,
            'email' => $this->nuevo_proveedor_email,
            'empresa_id' => auth()->user()->empresa_id,
            'status' => true,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Proveedor '{$proveedor->nombre}' creado exitosamente.",
            'duration' => 3000
        ]);

        // Seleccionar automáticamente el nuevo proveedor
        $this->seleccionarProveedor($proveedor->id);

        // Limpiar y cerrar modal
        $this->reset(['nuevo_proveedor_nombre', 'nuevo_proveedor_rif', 'nuevo_proveedor_telefono', 'nuevo_proveedor_email']);
        $this->showModalProveedor = false;

        // Recargar listas
        $this->proveedores = Proveedor::forUser()->activos()->orderBy('nombre')->get();
    }

    const UNIDADES = [
        'unidad'  => 'Unidad',   'caja'    => 'Caja',          'frasco'  => 'Frasco',
        'ampolla' => 'Ampolla',  'tableta' => 'Tableta',        'capsula' => 'Cápsula',
        'ml'      => 'ml',       'mg'      => 'mg',             'g'       => 'Gramo (g)',
        'kg'      => 'Kg',       'litro'   => 'Litro',          'par'     => 'Par',
        'rollo'   => 'Rollo',    'sobre'   => 'Sobre',          'vial'    => 'Vial',
    ];

    // Preview código
    public $codigo_preview = '';

    protected function rules()
    {
        return [
            'nombre'               => 'required|string|max:255',
            'codigo'               => 'nullable|string|max:50',
            'sku'                  => 'nullable|string|max:50',
            'codigo_barras'        => 'nullable|string|max:50',
            'descripcion'          => 'nullable|string|max:1000',
            'categoria_producto_id'=> 'required|exists:categorias_producto,id',
            'marca_id'             => 'required|exists:marcas,id',
            'proveedor_id'         => 'required|exists:proveedores,id',
            'unidad_medida'        => 'required|string|max:30',
            'precio_costo'         => 'required|numeric|min:0',
            'precio_venta'         => 'required|numeric|min:0|gte:precio_costo',
            'aplica_iva'           => 'boolean',
            'exento_iva'           => 'boolean',
            'iva_alicuota'         => 'required|numeric',
            'stock_minimo'         => 'required|integer|min:0',
            'stock_maximo'         => 'nullable|integer|min:0|gte:stock_minimo',
            'punto_reorden'        => 'required|integer|min:0',
            'fecha_vencimiento'    => $this->es_medicamento ? 'required|date' : 'nullable|date',
            'ubicacion_fisica'     => 'nullable|string|max:255',
            'requiere_receta'      => 'boolean',
            'es_medicamento'       => 'boolean',
            'status'               => 'boolean',
            'peso'                 => 'nullable|numeric|min:0',
            'dimensiones'          => 'nullable|string|max:50',
            'imagen'               => 'nullable|image|max:2048',
            'imagenes_nuevas.*'    => 'nullable|image|max:2048',
            'stock_inicial'        => 'integer|min:0',
            'almacen_inicial_id'   => 'nullable|exists:almacenes,id',
            'variantes'            => 'nullable|array',
            'variantes.*.tipo_variante_id' => 'nullable|exists:tipo_variantes,id',
            'variantes.*.valor_variante_id'=> 'nullable|exists:valor_variantes,id',
            'variantes.*.sku_variante'    => 'nullable|string|max:50',
            'variantes.*.atributo'        => 'nullable|string|max:50',
            'variantes.*.valor'           => 'nullable|string|max:50',
            'variantes.*.codigo_barras'    => 'nullable|string|max:50',
            'variantes.*.precio_costo'     => 'nullable|numeric|min:0',
            'variantes.*.precio_venta'     => 'nullable|numeric|min:0',
            'variantes.*.stock'            => 'nullable|integer|min:0',
            'variantes.*.tamano'           => 'nullable|string|max:50',
            'variantes.*.peso'             => 'nullable|numeric|min:0',
            'variantes.*.presentacion'     => 'nullable|string|max:50',
            'variantes.*.unidad_medida'    => 'nullable|string|max:30',
            'variantes.*.alt'              => 'nullable|string|max:255',
            'variantes.*.status'           => 'boolean',
            'variantes_imagenes.*'         => 'nullable|image|max:2048',
        ];
    }

    protected $messages = [
        'precio_venta.gte'   => 'El precio de venta no puede ser menor al precio de costo.',
        'stock_maximo.gte'   => 'El stock máximo no puede ser menor al stock mínimo.',
        'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria para medicamentos.',
    ];

    public function mount(?Producto $producto = null)
    {
        if ($producto && $producto->exists) {
            $this->producto      = $producto;
            $this->isEdit        = true;
            $this->imagen_actual = $producto->imagen;

            $this->fill($producto->only(
                'nombre', 'codigo', 'sku', 'codigo_barras', 'descripcion',
                'categoria_producto_id', 'marca_id', 'proveedor_id',
                'unidad_medida', 'precio_costo', 'precio_venta',
                'aplica_iva', 'exento_iva', 'iva_alicuota',
                'stock_minimo', 'stock_maximo', 'punto_reorden',
                'ubicacion_fisica', 'requiere_receta', 'es_medicamento', 'status',
                'peso', 'dimensiones'
            ));

            $this->fecha_vencimiento = $producto->fecha_vencimiento
                ? $producto->fecha_vencimiento->format('Y-m-d')
                : '';

            // Imágenes existentes
            $this->imagenes_existentes = $producto->imagenes->map(function ($img) {
                return [
                    'id'        => $img->id,
                    'url'       => $img->url,
                    'titulo'    => $img->titulo,
                    'orden'     => $img->orden,
                    'principal' => (bool) $img->principal,
                ];
            })->toArray();

            if (!empty($this->imagenes_existentes)) {
                $principal = collect($this->imagenes_existentes)->firstWhere('principal', true);
                $this->imagen_principal_id = $principal['id'] ?? $this->imagenes_existentes[0]['id'];
            }

            // Variantes existentes
            $this->variantes = $producto->variantes->map(function ($v) {
                return [
                    'id'                => $v->id,
                    'tipo_variante_id'  => $v->tipo_variante_id,
                    'valor_variante_id' => $v->valor_variante_id,
                    'sku_variante'      => $v->sku_variante,
                    'atributo'          => $v->atributo,
                    'valor'             => $v->valor,
                    'codigo_barras'     => $v->codigo_barras,
                    'precio_costo'      => $v->precio_costo,
                    'precio_venta'      => $v->precio_venta,
                    'stock'             => $v->stock,
                    'tamano'            => $v->tamano,
                    'peso'              => $v->peso,
                    'presentacion'      => $v->presentacion,
                    'unidad_medida'     => $v->unidad_medida,
                    'alt'               => $v->alt,
                    'status'            => (bool) $v->status,
                    'imagen_existente'  => $v->imagen,
                ];
            })->toArray();
        } else {
            $this->codigo_preview = 'PROD-' . str_pad(
                Producto::withoutGlobalScopes()->count() + 1, 5, '0', STR_PAD_LEFT
            );
        }
    }

    // ─── Margen en tiempo real ────────────────────────────────────
    public function updatedExentoIva($value)
    {
        if ($value) {
            $this->aplica_iva   = false;
            $this->iva_alicuota = 0;
        } else {
            $this->aplica_iva   = true;
            $this->iva_alicuota = 12.00;
        }
    }

    public function updatedAplicaIva($value)
    {
        if (!$value) {
            $this->iva_alicuota = 0;
        } else {
            $this->exento_iva   = false;
            $this->iva_alicuota = 12.00;
        }
    }

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

    // ─── Tabs ─────────────────────────────────────────────────────
    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        // Disparar evento para reinicializar Select2 cuando cambia el tab
        $this->dispatch('tab-changed');
    }

    // ─── Imágenes ───────────────────────────────────────────────────
    public function addImagenGaleria()
    {
        $this->imagenes_nuevas[] = null;
    }

    public function removeImagenNueva(int $index)
    {
        if (isset($this->imagenes_nuevas[$index])) {
            unset($this->imagenes_nuevas[$index]);
            $this->imagenes_nuevas = array_values($this->imagenes_nuevas);
        }
    }

    public function removeImagenExistente(int $id)
    {
        $this->imagenes_a_eliminar[] = $id;
        $this->imagenes_existentes = array_values(array_filter($this->imagenes_existentes, fn($i) => $i['id'] !== $id));
    }

    public function setImagenPrincipal(int $id)
    {
        $this->imagen_principal_id = $id;
        $this->imagenes_existentes = collect($this->imagenes_existentes)->map(function ($img) use ($id) {
            $img['principal'] = ($img['id'] === $id);
            return $img;
        })->toArray();
    }

    // ─── Variantes ────────────────────────────────────────────────
    public function getTiposVariantesProperty()
    {
        return TipoVariante::forUser()->activos()->with('valores')->orderBy('nombre')->get();
    }

    public function addVarianteManual()
    {
        $this->variantes[] = [
            'id'                => null,
            'tipo_variante_id'  => null,
            'valor_variante_id' => null,
            'atributo'          => '',
            'valor'             => '',
            'sku_variante'      => '',
            'codigo_barras'     => '',
            'precio_costo'      => '',
            'precio_venta'      => '',
            'stock'             => 0,
            'tamano'            => '',
            'peso'              => '',
            'presentacion'      => '',
            'unidad_medida'     => $this->unidad_medida,
            'alt'               => '',
            'status'            => true,
            'imagen_existente'  => null,
        ];
    }

    public function selectValorPredefinido(int $index, int $valorId)
    {
        if (!isset($this->variantes[$index])) return;
        $this->variantes[$index]['valor_variante_id'] = $valorId;
        $this->variantes[$index]['valor'] = '';
    }

    public function clearValorPredefinido(int $index)
    {
        if (!isset($this->variantes[$index])) return;
        $this->variantes[$index]['valor_variante_id'] = null;
    }

    public function removeVariante(int $index)
    {
        if (isset($this->variantes[$index])) {
            unset($this->variantes[$index]);
            $this->variantes = array_values($this->variantes);
            if (isset($this->variantes_imagenes[$index])) {
                unset($this->variantes_imagenes[$index]);
                $this->variantes_imagenes = array_values($this->variantes_imagenes);
            }
        }
    }

    // ─── Save ──────────────────────────────────────────────────────
    public function save()
    {
        $this->isEdit
            ? $this->authorize('edit productos')
            : $this->authorize('create productos');

        $data = $this->validate();

        // Imagen principal (legacy)
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
            ->except([
                'stock_inicial', 'almacen_inicial_id', 'imagen',
                'imagenes_nuevas', 'variantes', 'variantes_imagenes',
            ])
            ->merge([
                'empresa_id'        => auth()->user()->empresa_id,
                'sucursal_id'       => auth()->user()->sucursal_id,
                'imagen'            => $imagenPath,
                'fecha_vencimiento' => $this->es_medicamento && !empty($this->fecha_vencimiento)
                                        ? $this->fecha_vencimiento
                                        : null,
            ])
            ->toArray();

        if ($this->isEdit) {
            $this->producto->update($payload);
            $producto = $this->producto;
            $msg = "Producto '{$this->nombre}' actualizado.";
        } else {

             $data = $this->validate();

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

        $this->syncImagenes($producto);
        $this->syncVariantes($producto);

        $this->dispatch('notify', ['type' => 'success', 'message' => $msg, 'duration' => 4000]);
        return redirect()->route('admin.inventario.productos.index');
    }

    protected function syncImagenes(Producto $producto)
    {
        // Eliminar marcadas
        foreach ($this->imagenes_a_eliminar as $id) {
            $img = ProductoImagen::find($id);
            if ($img) {
                Storage::disk('public')->delete($img->imagen);
                $img->delete();
            }
        }

        // Actualizar principal y orden de existentes
        foreach ($this->imagenes_existentes as $idx => $img) {
            ProductoImagen::where('id', $img['id'])->update([
                'orden'     => $idx,
                'principal' => ($img['id'] == $this->imagen_principal_id),
            ]);
        }

        // Subir nuevas
        foreach ($this->imagenes_nuevas as $idx => $file) {
            if ($file) {
                $path = $file->store('productos', 'public');
                $producto->imagenes()->create([
                    'imagen'    => $path,
                    'titulo'    => null,
                    'orden'     => count($this->imagenes_existentes) + $idx,
                    'principal' => false,
                ]);
            }
        }
    }

    protected function syncVariantes(Producto $producto)
    {
        $variantesInput = collect($this->variantes ?? [])->filter(function ($v) {
            return !empty($v['atributo']) || !empty($v['valor']) || !empty($v['tipo_variante_id']);
        });

        $idsExistentes = $variantesInput->pluck('id')->filter()->all();
        $producto->variantes()->whereNotIn('id', $idsExistentes)->delete();

        foreach ($variantesInput as $index => $v) {
            $imgPath = $v['imagen_existente'] ?? null;
            if (!empty($this->variantes_imagenes[$index])) {
                $imgPath = $this->variantes_imagenes[$index]->store('productos', 'public');
            }

            $data = [
                'tipo_variante_id'  => $v['tipo_variante_id'] ?? null,
                'valor_variante_id' => $v['valor_variante_id'] ?? null,
                'sku_variante'      => $v['sku_variante'] ?? null,
                'atributo'          => $v['atributo'] ?? null,
                'valor'             => $v['valor'] ?? null,
                'codigo_barras'     => $v['codigo_barras'] ?? null,
                'precio_costo'      => $v['precio_costo'] ?: null,
                'precio_venta'      => $v['precio_venta'] ?: null,
                'stock'             => (int) ($v['stock'] ?? 0),
                'tamano'            => $v['tamano'] ?? null,
                'peso'              => $v['peso'] ?: null,
                'presentacion'      => $v['presentacion'] ?? null,
                'unidad_medida'     => $v['unidad_medida'] ?? null,
                'alt'               => $v['alt'] ?? null,
                'orden'             => $index,
                'status'            => (bool) ($v['status'] ?? true),
                'empresa_id'        => auth()->user()->empresa_id,
                'sucursal_id'       => auth()->user()->sucursal_id,
                'imagen'            => $imgPath,
            ];

            if (!empty($v['id'])) {
                $producto->variantes()->where('id', $v['id'])->update($data);
            } else {
                $producto->variantes()->create($data);
            }
        }
    }

    public function getCategoriasProperty()  { return CategoriaProducto::forUser()->activas()->orderBy('nombre')->get(); }
    public function getMarcasProperty()      { return Marca::forUser()->activas()->orderBy('nombre')->get(); }
    public function getProveedoresProperty() { return Proveedor::forUser()->activos()->orderBy('nombre')->get(); }
    public function getAlmacenesProperty()   { return Almacen::forUser()->activos()->orderBy('nombre')->get(); }

    public function render()
    {
        return view('livewire.admin.inventario.productos.form', [
            'categorias'           => $this->categorias,
            'categorias_filtradas' => $this->categorias_filtradas,
            'categoria_seleccionada' => $this->categoria_seleccionada,
            'marcas'               => $this->marcas,
            'marcas_filtradas'     => $this->marcas_filtradas,
            'marca_seleccionada'   => $this->marca_seleccionada,
            'proveedores'          => $this->proveedores,
            'proveedores_filtrados'=> $this->proveedores_filtrados,
            'proveedor_seleccionado' => $this->proveedor_seleccionado,
            'almacenes'            => $this->almacenes,
            'unidades'             => self::UNIDADES,
            'tiposVariantes'       => $this->tiposVariantes,
        ])->layout($this->getLayout());
    }
}
