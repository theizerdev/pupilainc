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

class CrearFactura extends Component
{
    use HasDynamicLayout;

    public $search_consulta = '';
    public $search_servicio = '';
    public $buscar_baremo = '';
    public $baremos_filtrados = [];

    public $consulta_id;
    public $consulta_seleccionada;

    public $carrito = [];
    public $detalles = [];
    public $baremo_id;
    public $descripcion;
    public $cantidad = 1;
    public $precio_unitario = 0;

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

    public function mount()
    {
        $this->tasa_usd = ExchangeRate::getLatestRate('USD') ?? 1;
        $this->obtenerProximoNumero();
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

        $baremo = $this->baremo_id ? Baremo::find($this->baremo_id) : null;

        $this->detalles[] = [
            'baremo_id' => $this->baremo_id,
            'descripcion' => $this->descripcion,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'subtotal' => $this->cantidad * $this->precio_unitario,
            'aplica_iva' => $baremo ? $baremo->aplica_iva : true,
            'exento_iva' => $baremo ? $baremo->exento_iva : false,
            'iva_alicuota' => ($baremo && $baremo->exento_iva) ? 0 : 16.00,
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
                'tipo' => 'baremo',
                'id' => $baremoId,
                'codigo' => $baremo->codigo,
                'descripcion' => $baremo->nombre_servicio,
                'especialidad' => $baremo->especialidad->nombre ?? '',
                'cantidad' => 1,
                'precio_unitario' => $baremo->costo_usd,
                'aplica_iva' => $baremo->aplica_iva,
                'exento_iva' => $baremo->exento_iva,
            ];
        }

        $this->calcularTotales();
        $this->search_servicio = '';
    }

    public function actualizarCantidad($key, $cantidad)
    {
        if ($cantidad > 0) {
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
        $this->subtotal = 0;
        $baseImponible = 0;
        $montoExento = 0;

        foreach ($this->detalles as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio_unitario'];
            $this->subtotal += $subtotalItem;

            if ($item['exento_iva']) {
                $montoExento += $subtotalItem;
            } elseif ($item['aplica_iva']) {
                $baseImponible += $subtotalItem;
            } else {
                $montoExento += $subtotalItem;
            }
        }

        $this->subtotal -= $this->descuento;
        $this->monto_exento = $montoExento * $this->tasa_usd;
        $this->base_imponible = $baseImponible * $this->tasa_usd;

        if ($this->es_factura_fiscal) {
            $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')->where('activo', true)->first();
            $ivaPorcentaje = $ivaConfig ? $ivaConfig->porcentaje : 16;
            $this->iva_monto = $this->base_imponible * ($ivaPorcentaje / 100);
        } else {
            $this->iva_monto = 0;
        }

        $aplicaIGTF = in_array($this->metodo_pago, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal']);
        $this->igtf_monto = $aplicaIGTF ? (($this->subtotal * $this->tasa_usd) + $this->iva_monto) * 0.03 : 0;

        $this->total = ($this->subtotal * $this->tasa_usd) + $this->iva_monto + $this->igtf_monto;
    }

    public function guardar()
    {
        $this->validate([
            'consulta_id' => 'required',
            'tipo_pago' => 'required',
            'metodo_pago' => 'required',
            'detalles' => 'required|array|min:1'
        ], [
            'consulta_id.required' => 'Debe seleccionar una consulta',
            'detalles.required' => 'Debe agregar al menos un servicio',
            'detalles.min' => 'Debe agregar al menos un servicio'
        ]);

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
                'total' => $this->total / $this->tasa_usd,
                'tasa_cambio_usd' => $this->tasa_usd,
                'total_usd' => $this->total / $this->tasa_usd,
                'total_bs' => $this->total,
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
            ]);

            foreach ($this->detalles as $item) {
                $pago->detalles()->create([
                    'baremo_id' => $item['baremo_id'] ?? null,
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'] * $this->tasa_usd,
                    'subtotal' => $item['subtotal'] * $this->tasa_usd,
                    'aplica_iva' => $item['aplica_iva'],
                    'exento_iva' => $item['exento_iva'],
                    'iva_alicuota' => $item['iva_alicuota'] ?? 16,
                ]);
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
