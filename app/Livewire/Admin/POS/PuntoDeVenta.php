<?php

namespace App\Livewire\Admin\POS;

use Livewire\Component;
use App\Models\Caja;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\VentaProducto;
use App\Models\Producto;
use App\Models\Baremo;
use App\Models\ClienteFiscal;
use App\Models\Almacen;
use App\Models\ExchangeRate;
use App\Models\CategoriaProducto;
use App\Models\Especialidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PuntoDeVenta extends Component
{
    // ── Caja ─────────────────────────────────────────────────────────────────
    public $caja = null;

    // ── Cliente ───────────────────────────────────────────────────────────────
    public $cliente_id       = null;
    public $cliente_display  = '';
    public $busqueda_cliente = '';
    public $clientes_encontrados = [];
    public $mostrar_form_cliente = false;

    // Formulario nuevo cliente
    public $cf_nombre          = '';
    public $cf_tipo_documento  = 'V';
    public $cf_numero_documento = '';
    public $cf_razon_social    = '';
    public $cf_telefono        = '';
    public $cf_email           = '';
    public $cf_es_rapido       = true; // por defecto cliente rápido

    // ── Búsqueda / catálogo de items ──────────────────────────────────────────
    public $busqueda_item    = '';
    public $tab_busqueda     = 'todos'; // todos | productos | servicios
    public $categoria_filtro = null;    // CategoriaProducto.id
    public $especialidad_filtro = null; // Especialidad.id
    public $items_encontrados = [];

    // ── Carrito ───────────────────────────────────────────────────────────────
    // Cada línea: [tipo, id, nombre, precio, cantidad, aplica_iva, exento_iva, iva_alicuota, subtotal, iva_monto]
    public $carrito = [];

    // ── Pago ──────────────────────────────────────────────────────────────────
    public $tipo_documento  = 'recibo'; // recibo | factura | boleta
    public $metodo_pago     = 'efectivo_usd';
    public $es_pago_mixto   = false;
    public $pagos_mixtos    = []; // [{metodo, monto_usd}]
    public $observaciones   = '';
    public $tasa_usd        = 1;

    // ── UI ────────────────────────────────────────────────────────────────────
    public $mostrar_modal_pago    = false;
    public $mostrar_modal_cliente = false;
    public $procesando            = false;

    public function mount()
    {
        $user = auth()->user();

        $this->caja = Caja::obtenerCajaAbierta(
            $user->empresa_id,
            $user->sucursal_id
        );

        $this->tasa_usd = ExchangeRate::getLatestRate('USD') ?? 1;

        // Cliente por defecto: Consumidor Final
        $this->setClienteConsumidorFinal();

        // Cargar catálogo inicial (productos + servicios) sin búsqueda
        $this->cargarItems();
    }

    // ── Cliente ───────────────────────────────────────────────────────────────

    private function setClienteConsumidorFinal(): void
    {
        $user = auth()->user();

        $cf = ClienteFiscal::firstOrCreate(
            [
                'empresa_id'       => $user->empresa_id,
                'sucursal_id'      => $user->sucursal_id,
                'es_cliente_rapido'=> true,
                'nombre'           => 'Consumidor Final',
            ],
            [
                'tipo_documento'   => 'V',
                'numero_documento' => '00000000',
                'razon_social'     => 'Consumidor Final',
                'direccion_fiscal' => 'N/A',
                'telefono'         => '00000000',
                'email'            => '',
            ]
        );

        $this->cliente_id      = $cf->id;
        $this->cliente_display = 'Consumidor Final';
    }

    public function updatedBusquedaCliente($value): void
    {
        if (strlen($value) < 2) {
            $this->clientes_encontrados = [];
            return;
        }

        $this->clientes_encontrados = ClienteFiscal::where('empresa_id', auth()->user()->empresa_id)
            ->where(function ($q) use ($value) {
                $q->where('razon_social', 'like', "%{$value}%")
                  ->orWhere('nombre', 'like', "%{$value}%")
                  ->orWhere('numero_documento', 'like', "%{$value}%")
                  ->orWhere('telefono', 'like', "%{$value}%");
            })
            ->limit(8)
            ->get(['id', 'razon_social', 'nombre', 'tipo_documento', 'numero_documento', 'telefono', 'es_cliente_rapido'])
            ->toArray();
    }

    public function seleccionarCliente(int $id): void
    {
        $cf = ClienteFiscal::find($id);
        if (!$cf) return;

        $this->cliente_id      = $cf->id;
        $this->cliente_display = $cf->nombre_display;
        $this->busqueda_cliente     = '';
        $this->clientes_encontrados = [];
    }

    public function guardarCliente(): void
    {
        $rules = ['cf_nombre' => 'required|string|max:255'];

        if (!$this->cf_es_rapido) {
            $rules['cf_numero_documento'] = 'required|string|max:20';
            $rules['cf_razon_social']     = 'required|string|max:255';
        }

        $this->validate($rules, [
            'cf_nombre.required'           => 'El nombre es obligatorio',
            'cf_numero_documento.required' => 'El número de documento es obligatorio',
            'cf_razon_social.required'     => 'La razón social es obligatoria',
        ]);

        $user = auth()->user();

        $cf = ClienteFiscal::create([
            'empresa_id'        => $user->empresa_id,
            'sucursal_id'       => $user->sucursal_id,
            'nombre'            => $this->cf_nombre,
            'es_cliente_rapido' => $this->cf_es_rapido,
            'tipo_documento'    => $this->cf_tipo_documento,
            'numero_documento'  => $this->cf_es_rapido ? '00000000' : $this->cf_numero_documento,
            'razon_social'      => $this->cf_es_rapido ? $this->cf_nombre : $this->cf_razon_social,
            'direccion_fiscal'  => 'N/A',
            'telefono'          => $this->cf_telefono ?: '00000000',
            'email'             => $this->cf_email ?: '',
        ]);

        $this->seleccionarCliente($cf->id);
        $this->resetFormCliente();
        $this->mostrar_modal_cliente = false;
        $this->dispatch('notify', ['message' => 'Cliente creado', 'type' => 'success']);
    }

    private function resetFormCliente(): void
    {
        $this->cf_nombre           = '';
        $this->cf_tipo_documento   = 'V';
        $this->cf_numero_documento = '';
        $this->cf_razon_social     = '';
        $this->cf_telefono         = '';
        $this->cf_email            = '';
        $this->cf_es_rapido        = true;
    }

    // ── Búsqueda / catálogo de items ──────────────────────────────────────────

    public function cargarItems(): void
    {
        $user  = auth()->user();
        $value = trim((string) $this->busqueda_item);
        $items = [];

        // Productos
        if (in_array($this->tab_busqueda, ['todos', 'productos'])) {
            $productos = Producto::where('empresa_id', $user->empresa_id)
                ->where('status', true)
                ->when($this->categoria_filtro, fn($q) =>
                    $q->where('categoria_producto_id', $this->categoria_filtro))
                ->when(strlen($value) >= 1, fn($q) => $q->where(function ($qq) use ($value) {
                    $qq->where('nombre', 'like', "%{$value}%")
                       ->orWhere('codigo', 'like', "%{$value}%")
                       ->orWhere('codigo_barras', 'like', "%{$value}%");
                }))
                ->with(['stocks', 'categoria'])
                ->orderBy('nombre')
                ->limit(24)
                ->get();

            foreach ($productos as $p) {
                $items[] = [
                    'tipo'         => 'producto',
                    'id'           => $p->id,
                    'nombre'       => $p->nombre,
                    'codigo'       => $p->codigo,
                    'precio'       => (float) $p->precio_venta,
                    'aplica_iva'   => $p->aplica_iva,
                    'exento_iva'   => $p->exento_iva,
                    'iva_alicuota' => (float) $p->iva_alicuota,
                    'stock'        => $p->stockTotal(),
                    'unidad'       => $p->unidad_medida ?? 'und',
                    'imagen'       => $p->imagen ? Storage::url($p->imagen) : null,
                    'cat_nombre'   => $p->categoria?->nombre,
                    'cat_color'    => $p->categoria?->color ?? '#0d6efd',
                    'cat_icono'    => $p->categoria?->icono ?? 'ri-box-3-line',
                ];
            }
        }

        // Servicios (baremos)
        if (in_array($this->tab_busqueda, ['todos', 'servicios'])) {
            $baremos = Baremo::where('empresa_id', $user->empresa_id)
                ->where('activo', true)
                ->when($this->especialidad_filtro, fn($q) =>
                    $q->where('especialidad_id', $this->especialidad_filtro))
                ->when(strlen($value) >= 1, fn($q) => $q->where(function ($qq) use ($value) {
                    $qq->where('nombre_servicio', 'like', "%{$value}%")
                       ->orWhere('codigo', 'like', "%{$value}%");
                }))
                ->with('especialidad')
                ->orderBy('nombre_servicio')
                ->limit(24)
                ->get();

            foreach ($baremos as $b) {
                $items[] = [
                    'tipo'         => 'servicio',
                    'id'           => $b->id,
                    'nombre'       => $b->nombre_servicio,
                    'codigo'       => $b->codigo,
                    'precio'       => (float) $b->costo_usd,
                    'aplica_iva'   => $b->aplica_iva,
                    'exento_iva'   => $b->exento_iva,
                    'iva_alicuota' => 16.0,
                    'stock'        => null,
                    'unidad'       => 'srv',
                    'imagen'       => null,
                    'cat_nombre'   => $b->especialidad?->nombre,
                    'cat_color'    => $b->especialidad?->color ?? '#6f42c1',
                    'cat_icono'    => $b->especialidad?->icono ?? 'fa-stethoscope',
                ];
            }
        }

        $this->items_encontrados = $items;
    }

    public function updatedBusquedaItem(): void
    {
        $this->cargarItems();
    }

    public function updatedTabBusqueda(): void
    {
        // Resetear filtro contrario al cambiar de pestaña
        if ($this->tab_busqueda === 'productos')      $this->especialidad_filtro = null;
        elseif ($this->tab_busqueda === 'servicios')  $this->categoria_filtro = null;
        $this->cargarItems();
    }

    public function updatedCategoriaFiltro(): void
    {
        $this->cargarItems();
    }

    public function updatedEspecialidadFiltro(): void
    {
        $this->cargarItems();
    }

    public function agregarItem(string $tipo, int $id): void
    {
        // Buscar si ya está en el carrito
        foreach ($this->carrito as $i => $linea) {
            if ($linea['tipo'] === $tipo && $linea['id'] === $id) {
                $this->carrito[$i]['cantidad']++;
                $this->recalcularLinea($i);
                return;
            }
        }

        // Buscar el item
        $item = collect($this->items_encontrados)
            ->firstWhere(fn($i) => $i['tipo'] === $tipo && $i['id'] === $id);

        if (!$item) return;

        $linea = [
            'tipo'         => $item['tipo'],
            'id'           => $item['id'],
            'nombre'       => $item['nombre'],
            'precio'       => $item['precio'],
            'precio_original' => $item['precio'],
            'cantidad'     => 1,
            'aplica_iva'   => $item['aplica_iva'],
            'exento_iva'   => $item['exento_iva'],
            'iva_alicuota' => $item['iva_alicuota'],
            'subtotal'     => $item['precio'],
            'iva_monto'    => 0,
            'stock'        => $item['stock'],
            'unidad'       => $item['unidad'],
            'editando_precio' => false,
        ];

        $this->carrito[] = $linea;
        $idx = count($this->carrito) - 1;
        $this->recalcularLinea($idx);
    }

    public function quitarItem(int $index): void
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);
    }

    public function actualizarCantidad(int $index, $cantidad): void
    {
        $cantidad = max(1, (int) $cantidad);

        if ($this->carrito[$index]['tipo'] === 'producto'
            && $this->carrito[$index]['stock'] !== null
            && $cantidad > $this->carrito[$index]['stock']) {
            $this->dispatch('notify', ['message' => 'Stock insuficiente', 'type' => 'warning']);
            $cantidad = $this->carrito[$index]['stock'];
        }

        $this->carrito[$index]['cantidad'] = $cantidad;
        $this->recalcularLinea($index);
    }

    public function actualizarPrecio(int $index, $precio): void
    {
        $precio = max(0, (float) $precio);
        $this->carrito[$index]['precio'] = $precio;
        $this->recalcularLinea($index);
    }

    private function recalcularLinea(int $index): void
    {
        $l       = $this->carrito[$index];
        $subtotal = $l['precio'] * $l['cantidad'];
        $iva      = 0;

        if ($l['aplica_iva'] && !$l['exento_iva']) {
            $iva = $subtotal * ($l['iva_alicuota'] / 100);
        }

        $this->carrito[$index]['subtotal']  = round($subtotal, 2);
        $this->carrito[$index]['iva_monto'] = round($iva, 2);
    }

    // ── Totales ───────────────────────────────────────────────────────────────

    public function getTotalesProperty(): array
    {
        $subtotal  = 0;
        $iva       = 0;
        $exento    = 0;

        foreach ($this->carrito as $l) {
            $subtotal += $l['subtotal'];
            $iva      += $l['iva_monto'];
            if ($l['exento_iva']) {
                $exento += $l['subtotal'];
            }
        }

        $total = $subtotal + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'iva'      => round($iva, 2),
            'exento'   => round($exento, 2),
            'total'    => round($total, 2),
            'total_bs' => round($total * $this->tasa_usd, 2),
        ];
    }

    // ── Pago ──────────────────────────────────────────────────────────────────

    public function abrirModalPago(): void
    {
        if (empty($this->carrito)) {
            $this->dispatch('notify', ['message' => 'El carrito está vacío', 'type' => 'warning']);
            return;
        }
        if (!$this->caja) {
            $this->dispatch('notify', ['message' => 'No hay caja abierta', 'type' => 'error']);
            return;
        }
        $this->mostrar_modal_pago = true;
    }

    public function confirmarVenta(): void
    {
        if ($this->procesando) return;
        $this->procesando = true;

        try {
            DB::transaction(function () {
                $user    = auth()->user();
                $totales = $this->totales;

                // Crear Pago
                $pago = Pago::create([
                    'caja_id'          => $this->caja->id,
                    'cliente_fiscal_id'=> $this->cliente_id,
                    'tipo_pago'        => $this->tipo_documento,
                    'fecha'            => now()->toDateString(),
                    'user_id'          => $user->id,
                    'subtotal'         => $totales['subtotal'],
                    'total'            => $totales['total'],
                    'tasa_cambio_usd'  => $this->tasa_usd,
                    'total_usd'        => $totales['total'],
                    'total_bs'         => $totales['total_bs'],
                    'metodo_pago'      => $this->es_pago_mixto ? 'mixto' : $this->metodo_pago,
                    'es_pago_mixto'    => $this->es_pago_mixto,
                    'detalles_pago_mixto' => $this->es_pago_mixto ? $this->pagos_mixtos : null,
                    'estado'           => Pago::ESTADO_APROBADO,
                    'observaciones'    => $this->observaciones ?: null,
                    'base_imponible'   => $totales['subtotal'] - $totales['exento'],
                    'monto_exento'     => $totales['exento'],
                    'iva_monto'        => $totales['iva'],
                    'empresa_id'       => $user->empresa_id,
                    'sucursal_id'      => $user->sucursal_id,
                ]);

                $almacen = Almacen::where('empresa_id', $user->empresa_id)->where('activo', true)->first();

                foreach ($this->carrito as $linea) {
                    if ($linea['tipo'] === 'producto') {
                        VentaProducto::create([
                            'pago_id'        => $pago->id,
                            'producto_id'    => $linea['id'],
                            'cantidad'       => $linea['cantidad'],
                            'precio_unitario'=> $linea['precio'],
                            'aplica_iva'     => $linea['aplica_iva'],
                            'exento_iva'     => $linea['exento_iva'],
                            'iva_alicuota'   => $linea['iva_alicuota'],
                            'almacen_id'     => $almacen?->id,
                        ]);
                    } else {
                        PagoDetalle::create([
                            'pago_id'        => $pago->id,
                            'baremo_id'      => $linea['id'],
                            'descripcion'    => $linea['nombre'],
                            'cantidad'       => $linea['cantidad'],
                            'precio_unitario'=> $linea['precio'],
                            'aplica_iva'     => $linea['aplica_iva'],
                            'exento_iva'     => $linea['exento_iva'],
                            'iva_alicuota'   => $linea['iva_alicuota'],
                        ]);
                    }
                }

                // Recalcular totales del pago
                $pago->calcularTotales();

                $this->dispatch('venta-completada', ['pago_id' => $pago->id]);
            });

            $this->carrito            = [];
            $this->mostrar_modal_pago = false;
            $this->observaciones      = '';
            $this->setClienteConsumidorFinal();
            $this->dispatch('notify', ['message' => 'Venta registrada exitosamente', 'type' => 'success']);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        } finally {
            $this->procesando = false;
        }
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->setClienteConsumidorFinal();
    }

    public function render()
    {
        $user = auth()->user();

        $categorias = CategoriaProducto::where('empresa_id', $user->empresa_id)
            ->where('status', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'color', 'icono']);

        $especialidades = Especialidad::where('empresa_id', $user->empresa_id)
            ->where('status', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'color', 'icono']);

        return view('livewire.admin.p-o-s.punto-de-venta', [
            'totales'        => $this->totales,
            'categorias'     => $categorias,
            'especialidades' => $especialidades,
        ])->layout('components.layouts.auth-basic', ['title' => 'Punto de Venta']);
    }
}
