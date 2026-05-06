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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrearFactura extends Component
{
    use HasDynamicLayout;

    // Buscador unificado (consultas + clientes)
    public $search_principal = '';
    public $resultados_busqueda = [];
    public $mostrar_form_cliente_nuevo = false;

    public $search_servicio = '';
    public $search_producto = '';
    public $buscar_baremo = '';
    public $baremos_filtrados = [];
    public $productos_filtrados = [];

    public $consulta_id;
    public $consulta_seleccionada;
    public $modo_venta_directa = false;
    public $cliente_seleccionado = null;

    public $carrito = [];
    public $detalles = [];
    public $baremo_id;
    public $descripcion;
    public $cantidad = 1;
    public $precio_unitario = 0;
    public $pagos_mixtos = [];

    public $tipo_pago = 'recibo';
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

    // Validación en tiempo real
    public $referencia_validada = true;
    public $caja_abierta = null;

    public function mount()
    {
        // Detectar si es Venezuela PRIMERO
        $this->es_venezuela = is_venezuela_company();
        $paisId = auth()->user()->empresa?->pais_id;

        // Si NO es Venezuela, usar tasa 1 y no cargar exchange rates
        if (!$this->es_venezuela) {
            $this->tasa_usd = 1;
        } else {
            // Solo para Venezuela: cargar tasa de cambio con caché
            $cacheKey = "tasa_cambio_usd_{$paisId}";
            $this->tasa_usd = Cache::remember($cacheKey, now()->addHour(), function () use ($paisId) {
                return \App\Models\ExchangeRate::getLatestRate('USD', $paisId) ?? 1;
            });
        }

        // Doble verificación por moneda principal (fallback)
        if (!$this->es_venezuela && $paisId) {
            $pais = \App\Models\Pais::find($paisId);
            $this->es_venezuela = $pais && $pais->moneda_principal === 'VES';

            // Si detectamos VES, recargar la tasa
            if ($this->es_venezuela) {
                $cacheKey = "tasa_cambio_usd_{$paisId}";
                $this->tasa_usd = Cache::remember($cacheKey, now()->addHour(), function () use ($paisId) {
                    return \App\Models\ExchangeRate::getLatestRate('USD', $paisId) ?? 1;
                });
            }
        }

        $this->obtenerProximoNumero();
        $this->pagos_mixtos = [];

        if ($this->tipo_pago === 'factura') {
            $this->es_factura_fiscal = true;
        }

        // Verificar caja abierta al montar
        $this->verificarCajaAbierta();
    }

    /**
     * Verificar que exista una caja abierta
     */
    public function verificarCajaAbierta()
    {
        $this->caja_abierta = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->latest()
            ->first();

        return $this->caja_abierta !== null;
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

    /**
     * Buscar con debounce optimizado y caché de resultados
     */
    public function updatedSearchPrincipal($value)
    {
        if (strlen($value) < 2) {
            $this->resultados_busqueda = [];
            return;
        }

        $cacheKey = "busqueda_pagos_" . md5($value . '_' . auth()->user()->empresa_id);

        $this->resultados_busqueda = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($value) {
            $consultas = Consulta::with(['medico', 'paciente'])
                ->where('estado', 'finalizada')
                ->where(function($q) use ($value) {
                    $q->whereHas('paciente', fn($q2) =>
                        $q2->where('nombres', 'like', "%{$value}%")
                           ->orWhere('apellidos', 'like', "%{$value}%")
                           ->orWhere('documento_identidad', 'like', "%{$value}%")
                    )
                    ->orWhere('codigo', 'like', "%{$value}%");
                })
                ->latest()->limit(6)->get()
                ->map(fn($c) => [
                    '_tipo'       => 'consulta',
                    'id'          => $c->id,
                    'titulo'      => $c->paciente->nombre_completo ?? 'Sin paciente',
                    'subtitulo'   => 'Consulta #' . ($c->codigo ?? $c->id) . ' · ' . ($c->medico ? 'Dr. ' . $c->medico->apellidos : ''),
                    'documento'   => $c->paciente->documento_identidad ?? '',
                    'fecha'       => $c->fecha_consulta->format('d/m/Y'),
                ])->toArray();

            $clientes = \App\Models\ClienteFiscal::where('empresa_id', auth()->user()->empresa_id)
                ->where(function($q) use ($value) {
                    $q->where('razon_social', 'like', "%{$value}%")
                      ->orWhere('nombre', 'like', "%{$value}%")
                      ->orWhere('numero_documento', 'like', "%{$value}%")
                      ->orWhere('telefono', 'like', "%{$value}%");
                })
                ->limit(6)->get()
                ->map(fn($c) => [
                    '_tipo'     => 'cliente',
                    'id'        => $c->id,
                    'titulo'    => $c->razon_social ?? $c->nombre,
                    'subtitulo' => ($c->tipo_documento . '-' . $c->numero_documento) . ($c->telefono ? ' · ' . $c->telefono : ''),
                    'documento' => $c->numero_documento,
                    'tipo_doc'  => $c->tipo_documento,
                    'direccion' => $c->direccion_fiscal,
                    'telefono'  => $c->telefono,
                    'email'     => $c->email ?? '',
                ])->toArray();

            return array_merge($consultas, $clientes);
        });
    }

    public function seleccionarConsultaOCliente($index)
    {
        $item = $this->resultados_busqueda[$index] ?? null;
        if (!$item) return;

        $this->resultados_busqueda = [];
        $this->search_principal = '';
        $this->mostrar_form_cliente_nuevo = false;

        if ($item['_tipo'] === 'consulta') {
            $this->modo_venta_directa = false;
            $this->cliente_seleccionado = null;
            $this->cliente_fiscal_id = null;
            $this->seleccionarConsulta($item['id']);
        } else {
            $this->modo_venta_directa = true;
            $this->consulta_id = null;
            $this->consulta_seleccionada = null;
            $cliente = \App\Models\ClienteFiscal::find($item['id']);
            $this->cliente_seleccionado = $cliente->toArray();
            $this->cliente_fiscal_id = $cliente->id;
            $this->fiscal_tipo_documento = $cliente->tipo_documento;
            $this->fiscal_numero_documento = $cliente->numero_documento;
            $this->fiscal_razon_social = $cliente->razon_social ?? $cliente->nombre;
            $this->fiscal_direccion = $cliente->direccion_fiscal;
            $this->fiscal_telefono = $cliente->telefono;
            $this->fiscal_email = $cliente->email ?? '';
        }
    }

    public function mostrarFormNuevoCliente()
    {
        $this->mostrar_form_cliente_nuevo = true;
        $this->resultados_busqueda = [];
        // Pre-rellenar el nombre con lo que escribió
        $this->fiscal_razon_social = $this->search_principal;
        $this->search_principal = '';
    }

    public function crearYSeleccionarCliente()
    {
        $this->validate([
            'fiscal_numero_documento' => 'required|string|min:3',
            'fiscal_razon_social'     => 'required|string|min:3',
            'fiscal_direccion'        => 'required|string|min:5',
            'fiscal_telefono'         => 'required|string|min:7',
        ]);

        $cliente = \App\Models\ClienteFiscal::firstOrCreate(
            [
                'empresa_id'       => auth()->user()->empresa_id,
                'tipo_documento'   => $this->fiscal_tipo_documento,
                'numero_documento' => $this->fiscal_numero_documento,
            ],
            [
                'razon_social'    => $this->fiscal_razon_social,
                'nombre'          => $this->fiscal_razon_social,
                'direccion_fiscal'=> $this->fiscal_direccion,
                'telefono'        => $this->fiscal_telefono,
                'email'           => $this->fiscal_email,
                'sucursal_id'     => auth()->user()->sucursal_id,
            ]
        );

        $this->cliente_seleccionado = $cliente->toArray();
        $this->cliente_fiscal_id = $cliente->id;
        $this->modo_venta_directa = true;
        $this->mostrar_form_cliente_nuevo = false;
        $this->dispatch('notify', type: 'success', message: 'Cliente guardado correctamente');
    }

    public function limpiarSeleccion()
    {
        $this->consulta_id = null;
        $this->consulta_seleccionada = null;
        $this->cliente_seleccionado = null;
        $this->cliente_fiscal_id = null;
        $this->modo_venta_directa = false;
        $this->mostrar_form_cliente_nuevo = false;
        $this->search_principal = '';
        $this->resultados_busqueda = [];
        $this->reset(['fiscal_tipo_documento', 'fiscal_numero_documento', 'fiscal_razon_social',
                      'fiscal_direccion', 'fiscal_telefono', 'fiscal_email']);
        $this->fiscal_tipo_documento = 'V';
    }

    public function updatedBuscarBaremo($value)
    {
        if (strlen($value) >= 2) {
            $servicios = Baremo::where('activo', true)
                ->where(function($q) use ($value) {
                    $q->where('nombre_servicio', 'like', "%{$value}%")
                      ->orWhere('codigo', 'like', "%{$value}%")
                      ->orWhere('descripcion', 'like', "%{$value}%");
                })
                ->limit(8)
                ->get()
                ->map(fn($b) => array_merge($b->toArray(), ['_tipo' => 'servicio']))
                ->toArray();

            $productos = \App\Models\Producto::where('status', true)
                ->where('empresa_id', auth()->user()->empresa_id)
                ->where(function($q) use ($value) {
                    $q->where('nombre', 'like', "%{$value}%")
                      ->orWhere('codigo', 'like', "%{$value}%")
                      ->orWhere('sku', 'like', "%{$value}%");
                })
                ->with(['categoria', 'marca'])
                ->limit(8)
                ->get()
                ->map(fn($p) => [
                    'id'             => $p->id,
                    'nombre_servicio'=> $p->nombre,
                    'codigo'         => $p->codigo ?? $p->sku ?? '',
                    'costo_usd'      => $p->precio_venta,
                    'exento_iva'     => $p->exento_iva,
                    'aplica_iva'     => $p->aplica_iva,
                    'categoria'      => $p->categoria->nombre ?? '',
                    'marca'          => $p->marca->nombre ?? '',
                    'stock'          => $p->stockTotal(),
                    '_tipo'          => 'producto',
                ])
                ->toArray();

            $this->baremos_filtrados = array_merge($servicios, $productos);
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

    public function seleccionarResultado($id, $tipo)
    {
        if ($tipo === 'producto') {
            $this->agregarProducto($id);
            $this->buscar_baremo = '';
            $this->baremos_filtrados = [];
        } else {
            $this->seleccionarBaremo($id);
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

public function seleccionarConsulta($id)
    {
        $this->consulta_id = $id;
        $this->consulta_seleccionada = Consulta::with(['medico', 'paciente'])->find($id);

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

    /**
     * Agregar servicio/producto con validación mejorada
     */
    public function agregarDetalle()
    {
        $this->validate([
            'descripcion' => 'required|string|min:3',
            'cantidad' => 'required|numeric|min:1',
            'precio_unitario' => 'required|numeric|min:0.01'
        ], [
            'precio_unitario.min' => 'El precio debe ser mayor a cero',
            'descripcion.min' => 'La descripción es muy corta',
        ]);

        $baremo = $this->baremo_id ? Baremo::find($this->baremo_id) : null;

        $this->detalles[] = [
            'baremo_id'       => $this->baremo_id,
            'descripcion'     => $this->descripcion,
            'cantidad'        => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'subtotal'        => $this->cantidad * $this->precio_unitario,
            'aplica_iva'      => $baremo ? (bool) $baremo->aplica_iva : false,
            'exento_iva'      => $baremo ? (bool) $baremo->exento_iva : false,
            'iva_alicuota'    => $baremo ? (($baremo->exento_iva || !$baremo->aplica_iva) ? 0 : (float) ($baremo->iva_alicuota ?? 16)) : 0,
        ];

        $this->reset(['baremo_id', 'descripcion', 'precio_unitario', 'buscar_baremo', 'baremos_filtrados']);
        $this->cantidad = 1;
        $this->calcularTotales();

        $this->dispatch('notify', type: 'success', message: 'Servicio agregado correctamente');
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

            $esExento  = (bool) ($item['exento_iva'] ?? false);
            $aplicaIva = (bool) ($item['aplica_iva'] ?? false); // false por defecto: no asumir IVA
            $alicuota  = (float) ($item['iva_alicuota'] ?? 0);

            if (!$esExento && $aplicaIva && $alicuota > 0) {
                $baseImponibleUsd += $subtotalItem;
                $ivaMonto         += $subtotalItem * ($alicuota / 100);
            } else {
                $montoExentoUsd += $subtotalItem;
            }
        }

        $subtotalUsd -= (float) $this->descuento;

        $this->subtotal       = $subtotalUsd;
        $this->base_imponible = $baseImponibleUsd;
        $this->monto_exento   = $montoExentoUsd;
        $this->iva_monto      = $this->es_factura_fiscal ? $ivaMonto : 0;

        // IGTF solo aplica en Venezuela
        if ($this->es_venezuela) {
            $fiscalIgtf = FiscalCalculator::calcularIgtfDesdeDatos(
                auth()->user()->empresa_id,
                $this->metodo_pago,
                $this->metodo_pago === 'mixto',
                $this->pagos_mixtos,
                $subtotalUsd
            );
            $this->igtf_monto = ($fiscalIgtf['aplica_igtf'] ?? false) ? $fiscalIgtf['igtf_monto'] : 0;
        } else {
            $this->igtf_monto = 0;
        }

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

    /**
     * Guardar pago con validaciones robustas y manejo de errores mejorado
     */
    public function guardar()
    {
        // Validación inicial
        if (!$this->validarAntesDeGuardar()) {
            return;
        }

        try {
            \DB::beginTransaction();

            // Verificar caja abierta (doble verificación)
            if (!$this->verificarCajaAbierta()) {
                \DB::rollBack();
                session()->flash('error', 'Debe aperturar una caja antes de registrar pagos.');
                return redirect()->route('admin.cajas.create');
            }

            // Generar numeración y control fiscal
            $numeracion = Pago::generarNumero(
                $this->tipo_pago,
                auth()->user()->empresa_id,
                auth()->user()->sucursal_id
            );

            // Validar que no exista el número de factura (con retry)
            $numeroCompleto = $numeracion['serie'] . '-' . $numeracion['numero'];
            $existe = Pago::where('serie', $numeracion['serie'])
                ->where('numero', $numeracion['numero'])
                ->where('empresa_id', auth()->user()->empresa_id)
                ->exists();

            if ($existe) {
                \DB::rollBack();
                Log::warning('Número de factura duplicado detectado', [
                    'numero' => $numeroCompleto,
                    'user_id' => auth()->id(),
                    'timestamp' => now()
                ]);
                session()->flash('error', 'El número de factura ' . $numeroCompleto . ' ya existe. Por favor, intente nuevamente.');
                return;
            }

            // Resolver cliente_fiscal_id: venta directa ya lo tiene, consulta puede tenerlo también
            $clienteFiscalId = $this->cliente_fiscal_id;
            if (!$clienteFiscalId && $this->consulta_seleccionada?->paciente) {
                $paciente = $this->consulta_seleccionada->paciente;
                $clienteFiscalId = \App\Models\ClienteFiscal::where('empresa_id', auth()->user()->empresa_id)
                    ->where('paciente_id', $paciente->id)
                    ->value('id');
            }

            $pago = Pago::create([
                'consulta_id' => $this->consulta_id,
                'cliente_fiscal_id' => $clienteFiscalId,
                'caja_id' => $this->caja_abierta->id,
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

            // Guardar servicios (detalles)
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

            // Guardar productos y servicios del carrito
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

            // Recalcular totales del pago (incluye productos)
            $pago->calcularTotales();

            // Actualizar totales de la caja inmediatamente
            if ($this->caja_abierta) {
                $this->caja_abierta->calcularTotales();
            }

            \DB::commit();

            // Disparar evento para notificaciones WhatsApp
            event(new PagoCreated($pago));

            // Limpiar caché de búsqueda
            Cache::forget("busqueda_pagos_" . md5($this->search_principal . '_' . auth()->user()->empresa_id));

            session()->flash('success', '✅ Pago registrado exitosamente: ' . $numeroCompleto);
            return redirect()->route('admin.pagos.index');

        } catch (\Exception $e) {
            \DB::rollBack();

            Log::error('Error al registrar pago', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'tipo_pago' => $this->tipo_pago,
                    'total' => $this->total,
                    'consulta_id' => $this->consulta_id,
                ]
            ]);

            session()->flash('error', '❌ Error al registrar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Validaciones antes de guardar
     */
    private function validarAntesDeGuardar(): bool
    {
        $rules = [
            'tipo_pago'    => 'required',
            'metodo_pago'  => 'required',
        ];

        if (!$this->modo_venta_directa) {
            $rules['consulta_id'] = 'required';
        }

        try {
            $this->validate($rules, [
                'consulta_id.required' => 'Debe seleccionar una consulta',
            ]);
        } catch (\Exception $e) {
            return false;
        }

        // Validar que haya al menos un item (detalles o carrito)
        if (empty($this->detalles) && empty($this->carrito)) {
            session()->flash('error', '⚠️ Debe agregar al menos un servicio o producto');
            return false;
        }

        if ($this->modo_venta_directa && !$this->cliente_fiscal_id) {
            session()->flash('error', '⚠️ Debe seleccionar o crear un cliente para continuar.');
            return false;
        }

        // Validar que el total no sea cero o negativo
        if ($this->total <= 0) {
            session()->flash('error', '⚠️ El total del pago no puede ser cero. Agregue servicios con precio.');
            return false;
        }

        // Validar descuentos negativos
        if ($this->descuento < 0) {
            session()->flash('error', '⚠️ El descuento no puede ser negativo.');
            return false;
        }

        // Validar que el descuento no exceda el subtotal
        if ($this->descuento > $this->subtotal) {
            session()->flash('error', '⚠️ El descuento no puede ser mayor al subtotal.');
            return false;
        }

        if ($this->es_factura_fiscal && !$this->modo_venta_directa) {
            try {
                $this->validate([
                    'fiscal_numero_documento' => 'required|string|min:3',
                    'fiscal_razon_social' => 'required|string|min:3',
                    'fiscal_direccion' => 'required|string|min:5',
                    'fiscal_telefono' => 'required|string|min:7',
                ]);
            } catch (\Exception $e) {
                session()->flash('error', '⚠️ Complete todos los datos fiscales requeridos.');
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        return view('livewire.admin.pagos.crear-factura')->layout($this->getLayout());
    }
}
