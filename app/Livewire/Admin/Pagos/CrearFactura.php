<?php

namespace App\Livewire\Admin\Pagos;

use Livewire\Component;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Consulta;
use App\Models\Baremo;
use App\Models\ExchangeRate;
use App\Models\ImpuestoConfiguracion;
use App\Traits\HasDynamicLayout;
use App\Events\PagoCreated;
use App\Services\Seniat\FiscalCalculator;

class CrearFactura extends Component
{
    use HasDynamicLayout;

    public $search_consulta = '';
    public $search_servicio = '';
    public $search_producto = '';
    public $buscar_baremo = '';
    public $baremos_filtrados = [];
    public $productos_filtrados = [];

    public $consulta_id;
    public $consulta_seleccionada;

    public $carrito = [];
    public $detalles = [];
    public $baremo_id;
    public $descripcion;
    public $cantidad = 1;
    public $precio_unitario = 0;
    public $pagos_mixtos = [];

    public $tipo_pago = 'factura';
    public $metodo_pago = 'efectivo_bs';
    public $es_factura_fiscal = false;
    public $observaciones;
    public $descuento = 0;
    public $proximo_numero = '';

    // Datos fiscales
    public $fiscal_tipo_documento = 'V';
    public $fiscal_numero_documento = '';
    public $fiscal_razon_social = '';
    public $fiscal_direccion = '';
    public $fiscal_telefono = '';
    public $fiscal_email = '';
    public $paciente_tiene_fiscal = false;
    public $cliente_fiscal_id;
    public $condicion_pago = 'contado';

    public $subtotal = 0;
    public $base_imponible = 0;
    public $monto_exento = 0;
    public $iva_monto = 0;
    public $igtf_monto = 0;
    public $total = 0;
    public $tasa_usd;
    public $proximo_control_fiscal = '';
    public $es_venezuela = false;

    public function mount()
    {
        // Cargar tasa directamente por pais_id de la empresa del usuario
        $paisId = auth()->user()->empresa?->pais_id;
        $this->tasa_usd = \App\Models\ExchangeRate::getLatestRate('USD', $paisId) ?? 1;
        $this->es_venezuela = is_venezuela_company();
        
        // Si la sesión aún no tiene la config regional, forzar detección por moneda
        if (!$this->es_venezuela && $paisId) {
            $pais = \App\Models\Pais::find($paisId);
            $this->es_venezuela = $pais && $pais->moneda_principal === 'VES';
        }
        $this->obtenerProximoNumero();
        $this->pagos_mixtos = [];
        if ($this->tipo_pago === 'factura') {
            $this->es_factura_fiscal = true;
        }
    }

    public function updatedTipoPago($value)
    {
        $this->obtenerProximoNumero();
        if ($value === 'factura') {
            $this->es_factura_fiscal = true;
        } else {
            $this->es_factura_fiscal = false;
        }
        $this->calcularTotales();
    }

    public function obtenerProximoNumero()
    {
        $serie = \App\Models\Serie::where('tipo_documento', $this->tipo_pago)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', auth()->user()->sucursal_id)
            ->where('activo', true)
            ->first();

        if ($serie) {
            $proximoNumero = $serie->correlativo_actual + 1;
            $this->proximo_numero = $serie->serie . '-' . str_pad($proximoNumero, $serie->longitud_correlativo, '0', STR_PAD_LEFT);
            
            // Obtener próximo número de control fiscal
            $proximoControl = $serie->control_fiscal_actual ? intval($serie->control_fiscal_actual) + 1 : 1;
            $longitudControl = $serie->longitud_control_fiscal ?? 8;
            $this->proximo_control_fiscal = str_pad($proximoControl, $longitudControl, '0', STR_PAD_LEFT);
        } else {
            $prefijos = [
                'factura' => 'F001',
                'boleta' => 'B001',
                'recibo' => 'R001'
            ];
            $this->proximo_numero = ($prefijos[$this->tipo_pago] ?? 'DOC1') . '-00000001';
            $this->proximo_control_fiscal = '00000001';
        }
    }

    public function updatedBuscarBaremo($value)
    {
        if (strlen($value) >= 2) {
            $this->baremos_filtrados = Baremo::where('activo', true)
                ->where(function($q) use ($value) {
                    $q->where('nombre_servicio', 'like', "%{$value}%")
                      ->orWhere('codigo', 'like', "%{$value}%")
                      ->orWhere('descripcion', 'like', "%{$value}%");
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->baremos_filtrados = [];
        }
    }

    public function updatedSearchProducto($value)
    {
        if (strlen($value) >= 2) {
            $this->productos_filtrados = \App\Models\Producto::where('status', true)
                ->where('empresa_id', auth()->user()->empresa_id)
                ->where(function($q) use ($value) {
                    $q->where('nombre', 'like', "%{$value}%")
                      ->orWhere('codigo', 'like', "%{$value}%")
                      ->orWhere('sku', 'like', "%{$value}%");
                })
                ->with(['categoria', 'marca'])
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->productos_filtrados = [];
        }
    }

    public function seleccionarBaremo($baremoId)
    {
        $baremo = Baremo::find($baremoId);
        if ($baremo) {
            $this->baremo_id = $baremo->id;
            $this->descripcion = $baremo->nombre_servicio;
            $this->precio_unitario = $baremo->costo_usd;
            $this->buscar_baremo = $baremo->nombre_servicio;
            $this->baremos_filtrados = [];
        }
    }

    public function limpiarBusqueda()
    {
        $this->reset(['buscar_baremo', 'baremos_filtrados', 'baremo_id', 'descripcion', 'precio_unitario']);
        $this->cantidad = 1;
    }

    public function limpiarBusquedaProducto()
    {
        $this->reset(['search_producto', 'productos_filtrados']);
    }

    public function agregarProducto($productoId)
    {
        $producto = \App\Models\Producto::with(['categoria', 'marca'])->find($productoId);
        
        if (!$producto) {
            $this->dispatch('notify', type: 'error', message: 'Producto no encontrado');
            return;
        }

        // Verificar stock disponible
        $stockDisponible = $producto->stockTotal();
        if ($stockDisponible <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Producto sin stock disponible');
            return;
        }

        $key = 'producto_' . $productoId;

        if (isset($this->carrito[$key])) {
            if ($this->carrito[$key]['cantidad'] < $stockDisponible) {
                $this->carrito[$key]['cantidad']++;
            } else {
                $this->dispatch('notify', type: 'warning', message: 'Stock insuficiente');
                return;
            }
        } else {
            $this->carrito[$key] = [
                'tipo' => 'producto',
                'id' => $productoId,
                'codigo' => $producto->codigo,
                'descripcion' => $producto->nombre,
                'categoria' => $producto->categoria->nombre ?? '',
                'marca' => $producto->marca->nombre ?? '',
                'cantidad' => 1,
                'precio_unitario' => $producto->precio_venta,
                'stock_disponible' => $stockDisponible,
                'aplica_iva' => $producto->aplica_iva,
                'exento_iva' => $producto->exento_iva,
                'iva_alicuota' => $producto->iva_alicuota,
                'costo_unitario' => $producto->precio_costo,
                'es_medicamento' => $producto->es_medicamento,
            ];
        }

        $this->calcularTotales();
        $this->search_producto = '';
        $this->productos_filtrados = [];
    }

    public function buscarConsulta()
    {
        $this->validate(['search_consulta' => 'required']);

        $this->consulta_seleccionada = Consulta::with('paciente', 'medico')
            ->where(function($q) {
                $q->where('codigo', 'like', "%{$this->search_consulta}%")
                  ->orWhereHas('paciente', fn($q) => $q->where('nombres', 'like', "%{$this->search_consulta}%")
                      ->orWhere('apellidos', 'like', "%{$this->search_consulta}%")
                      ->orWhere('documento_identidad', 'like', "%{$this->search_consulta}%"))
                  ->orWhereHas('medico', fn($q) => $q->where('nombres', 'like', "%{$this->search_consulta}%")
                      ->orWhere('apellidos', 'like', "%{$this->search_consulta}%"));
            })
            ->where('estado', 'finalizada')
            ->latest()
            ->first();

        if (!$this->consulta_seleccionada) {
            session()->flash('error', 'No se encontró una consulta finalizada con ese criterio.');
            return;
        }

        $this->consulta_id = $this->consulta_seleccionada->id;
        $this->search_consulta = '';
    }

    public function seleccionarConsulta($id)
    {
        $this->consulta_id = $id;
        $this->consulta_seleccionada = Consulta::with(['medico', 'paciente'])->find($id);
        $this->search_consulta = '';

        if ($this->consulta_seleccionada && $this->consulta_seleccionada->paciente) {
            $paciente = $this->consulta_seleccionada->paciente;
            $this->fiscal_tipo_documento = $paciente->tipo_documento ?? 'V';
            $this->fiscal_numero_documento = $paciente->documento_identidad ?? '';
            $this->fiscal_razon_social = $paciente->nombre_completo ?? '';
            $this->fiscal_direccion = $paciente->direccion ?? '';
            $this->fiscal_telefono = $paciente->telefono ?? '';
            $this->fiscal_email = $paciente->email ?? '';
        }
    }

    public function agregarDetalle()
    {
        $this->validate([
            'descripcion' => 'required',
            'cantidad' => 'required|numeric|min:1',
            'precio_unitario' => 'required|numeric|min:0'
        ]);

        // Validar que el precio no sea cero
        if ($this->precio_unitario <= 0) {
            $this->dispatch('notify', type: 'error', message: 'El precio del servicio no puede ser cero');
            return;
        }

        $baremo = $this->baremo_id ? Baremo::find($this->baremo_id) : null;

        $this->detalles[] = [
            'baremo_id'       => $this->baremo_id,
            'descripcion'     => $this->descripcion,
            'cantidad'        => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'subtotal'        => $this->cantidad * $this->precio_unitario,
            'aplica_iva'      => $baremo ? (bool) $baremo->aplica_iva : true,
            'exento_iva'      => $baremo ? (bool) $baremo->exento_iva : false,
            'iva_alicuota'    => ($baremo && ($baremo->exento_iva || !$baremo->aplica_iva)) ? 0 : 16.00,
        ];

        $this->reset(['baremo_id', 'descripcion', 'precio_unitario', 'buscar_baremo', 'baremos_filtrados']);
        $this->cantidad = 1;
        $this->calcularTotales();
    }

    public function eliminarDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
        $this->calcularTotales();
    }

    public function agregarServicio($baremoId)
    {
        $baremo = Baremo::with('especialidad')->find($baremoId);

        $key = 'baremo_' . $baremoId;

        if (isset($this->carrito[$key])) {
            $this->carrito[$key]['cantidad']++;
        } else {
            $this->carrito[$key] = [
                'tipo'            => 'baremo',
                'id'              => $baremoId,
                'codigo'          => $baremo->codigo,
                'descripcion'     => $baremo->nombre_servicio,
                'especialidad'    => $baremo->especialidad->nombre ?? '',
                'cantidad'        => 1,
                'precio_unitario' => $baremo->costo_usd,
                'aplica_iva'      => (bool) $baremo->aplica_iva,
                'exento_iva'      => (bool) $baremo->exento_iva,
                'iva_alicuota'    => ($baremo->exento_iva || !$baremo->aplica_iva) ? 0 : 16.00,
            ];
        }

        $this->calcularTotales();
        $this->search_servicio = '';
    }

    public function actualizarCantidad($key, $cantidad)
    {
        if ($cantidad > 0) {
            // Verificar stock si es producto
            if (isset($this->carrito[$key]) && $this->carrito[$key]['tipo'] === 'producto') {
                $stockDisponible = $this->carrito[$key]['stock_disponible'] ?? 0;
                if ($cantidad > $stockDisponible) {
                    $this->dispatch('notify', type: 'warning', message: 'Cantidad excede el stock disponible');
                    return;
                }
            }
            $this->carrito[$key]['cantidad'] = $cantidad;
        } else {
            unset($this->carrito[$key]);
        }
        $this->calcularTotales();
    }

    public function eliminarItem($key)
    {
        unset($this->carrito[$key]);
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        // Todos los montos se manejan en USD internamente
        $subtotalUsd      = 0;
        $baseImponibleUsd = 0;
        $montoExentoUsd   = 0;
        $ivaMonto         = 0;

        $ivaConfig     = ImpuestoConfiguracion::where('codigo', 'IVA')->where('activo', true)->first();
        $ivaPorcentaje = $ivaConfig ? (float) $ivaConfig->porcentaje : 16;

        $todosLosItems = array_merge(
            array_values($this->detalles),
            array_values($this->carrito)
        );

        foreach ($todosLosItems as $item) {
            $subtotalItem = (float) $item['cantidad'] * (float) $item['precio_unitario'];
            $subtotalUsd += $subtotalItem;

            $esExento   = (bool) ($item['exento_iva'] ?? false);
            $aplicaIva  = (bool) ($item['aplica_iva'] ?? true);
            $alicuota   = (float) ($item['iva_alicuota'] ?? $ivaPorcentaje);

            if ($esExento || !$aplicaIva) {
                // Exento o no aplica IVA → va a monto exento
                $montoExentoUsd += $subtotalItem;
            } else {
                // Gravado → usar la alícuota del item (puede ser 16%, 8%, etc.)
                $baseImponibleUsd += $subtotalItem;
                $ivaMonto         += $subtotalItem * ($alicuota / 100);
            }
        }

        $subtotalUsd -= (float) $this->descuento;

        $this->subtotal       = $subtotalUsd;
        $this->base_imponible = $baseImponibleUsd;
        $this->monto_exento   = $montoExentoUsd;
        $this->iva_monto      = $this->es_factura_fiscal ? $ivaMonto : 0;

        $fiscalIgtf = FiscalCalculator::calcularIgtfDesdeDatos(
            auth()->user()->empresa_id,
            $this->metodo_pago,
            $this->metodo_pago === 'mixto',
            $this->pagos_mixtos,
            $subtotalUsd
        );
        $this->igtf_monto = ($fiscalIgtf['aplica_igtf'] ?? false) ? $fiscalIgtf['igtf_monto'] : 0;

        $this->total = $subtotalUsd + $this->iva_monto + $this->igtf_monto;
    }

    public function agregarPagoMixto()
    {
        $this->pagos_mixtos[] = [
            'metodo' => 'efectivo_bs',
            'monto_bs' => 0,
            'monto_usd' => 0
        ];
    }

    public function eliminarPagoMixto($index)
    {
        unset($this->pagos_mixtos[$index]);
        $this->pagos_mixtos = array_values($this->pagos_mixtos);
        $this->calcularTotales();
    }

    public function updatedPagosMixtos()
    {
        $this->calcularTotales();
    }

    public function guardar()
    {
        $this->validate([
            'consulta_id' => 'required',
            'tipo_pago' => 'required',
            'metodo_pago' => 'required',
        ], [
            'consulta_id.required' => 'Debe seleccionar una consulta',
        ]);

        // Validar que haya al menos un item (detalles o carrito)
        if (empty($this->detalles) && empty($this->carrito)) {
            session()->flash('error', 'Debe agregar al menos un servicio o producto');
            return;
        }

        // Validar que el total no sea cero
        if ($this->total <= 0) {
            session()->flash('error', 'El total del pago no puede ser cero. Agregue servicios con precio.');
            return;
        }

        if ($this->es_factura_fiscal) {
            $this->validate([
                'fiscal_numero_documento' => 'required|string|min:3',
                'fiscal_razon_social' => 'required|string|min:3',
                'fiscal_direccion' => 'required|string|min:5',
                'fiscal_telefono' => 'required|string|min:7',
            ]);
        }

        try {
            \DB::beginTransaction();

            $caja = \App\Models\Caja::where('user_id', auth()->id())
                ->where('estado', 'abierta')
                ->latest()
                ->first();

            if (!$caja) {
                session()->flash('error', 'Debe aperturar una caja antes de registrar pagos.');
                return;
            }

            // Generar numeración y control fiscal
            $numeracion = Pago::generarNumero(
                $this->tipo_pago,
                auth()->user()->empresa_id,
                auth()->user()->sucursal_id
            );

            // Validar que no exista el número de factura
            $numeroCompleto = $numeracion['serie'] . '-' . $numeracion['numero'];
            $existe = Pago::where('serie', $numeracion['serie'])
                ->where('numero', $numeracion['numero'])
                ->where('empresa_id', auth()->user()->empresa_id)
                ->exists();

            if ($existe) {
                \DB::rollBack();
                session()->flash('error', 'El número de factura ' . $numeroCompleto . ' ya existe. Por favor, intente nuevamente.');
                return;
            }

            $pago = Pago::create([
                'consulta_id' => $this->consulta_id,
                'caja_id' => $caja->id,
                'serie_id' => $numeracion['serie_id'],
                'serie' => $numeracion['serie'],
                'numero' => $numeracion['numero'],
                'numero_control_fiscal' => $numeracion['control_fiscal'],
                'tipo_pago' => $this->tipo_pago,
                'fecha' => now(),
                'user_id' => auth()->id(),
                'subtotal' => $this->subtotal,
                'descuento' => $this->descuento,
                'total' => $this->total,
                'tasa_cambio_usd' => $this->tasa_usd,
                'total_usd' => $this->total,
                'total_bs' => $this->total * $this->tasa_usd,
                'metodo_pago' => $this->metodo_pago,
                'estado' => Pago::ESTADO_APROBADO,
                'observaciones' => $this->observaciones,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'es_factura_fiscal' => $this->es_factura_fiscal,
                'monto_exento' => $this->monto_exento,
                'base_imponible' => $this->base_imponible,
                'iva_monto' => $this->iva_monto,
                'igtf_monto' => $this->igtf_monto,
                'aplica_igtf' => $this->igtf_monto > 0,
                'condicion_pago' => $this->condicion_pago,
                'pagos_mixtos' => $this->metodo_pago === 'mixto' ? json_encode($this->pagos_mixtos) : null,
                'detalles_pago_mixto' => $this->metodo_pago === 'mixto' ? $this->pagos_mixtos : null,
                'total_con_impuestos' => $this->total,
            ]);

            foreach ($this->detalles as $item) {
                $pago->detalles()->create([
                    'baremo_id' => $item['baremo_id'] ?? null,
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'aplica_iva' => $item['aplica_iva'],
                    'exento_iva' => $item['exento_iva'],
                    'iva_alicuota' => $item['iva_alicuota'] ?? 16,
                ]);
            }

            // Guardar productos del carrito
            foreach ($this->carrito as $item) {
                if ($item['tipo'] === 'producto') {
                    \App\Models\VentaProducto::create([
                        'pago_id' => $pago->id,
                        'producto_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'aplica_iva' => $item['aplica_iva'],
                        'exento_iva' => $item['exento_iva'],
                        'iva_alicuota' => $item['iva_alicuota'] ?? 16,
                        'costo_unitario' => $item['costo_unitario'] ?? 0,
                    ]);
                } elseif ($item['tipo'] === 'baremo') {
                    $pago->detalles()->create([
                        'baremo_id' => $item['id'],
                        'descripcion' => $item['descripcion'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                        'aplica_iva' => $item['aplica_iva'],
                        'exento_iva' => $item['exento_iva'],
                        'iva_alicuota' => $item['iva_alicuota'] ?? 16,
                    ]);
                }
            }

            \DB::commit();

            event(new PagoCreated($pago));

            session()->flash('success', 'Pago registrado exitosamente');
            return redirect()->route('admin.pagos.index');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $consultas = [];

        if (strlen($this->search_consulta) >= 2) {
            $consultas = Consulta::with(['medico', 'paciente'])
                ->where('estado', 'finalizada')
                ->where(function($q) {
                    $q->whereHas('paciente', function($q2) {
                        $q2->where('nombres', 'like', "%{$this->search_consulta}%")
                           ->orWhere('apellidos', 'like', "%{$this->search_consulta}%")
                           ->orWhere('documento_identidad', 'like', "%{$this->search_consulta}%");
                    })
                    ->orWhereHas('medico', function($q2) {
                        $q2->where('nombres', 'like', "%{$this->search_consulta}%")
                           ->orWhere('apellidos', 'like', "%{$this->search_consulta}%")
                           ->orWhere('documento_identidad', 'like', "%{$this->search_consulta}%");
                    })
                    ->orWhere('id', 'like', "%{$this->search_consulta}%")
                    ->orWhere('codigo', 'like', "%{$this->search_consulta}%");
                })
                ->latest()
                ->limit(15)
                ->get();
        }

        return view('livewire.admin.pagos.crear-factura', [
            'consultas' => $consultas
        ])->layout($this->getLayout());
    }
}
