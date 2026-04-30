<?php

namespace App\Livewire\Admin\NotaCredito;

use Livewire\Component;
use App\Models\Pago;
use App\Models\ExchangeRate;
use App\Models\ImpuestoConfiguracion;
use App\Models\TipoNotaCredito;
use App\Services\Seniat\FiscalCalculator;
use App\Services\Seniat\FiscalNumberingService;
use App\Events\PagoCreated;

class CrearNotaCredito extends Component
{
    // Búsqueda de factura origen
    public $buscar_serie;
    public $pago_origen;

    // Datos de la nota
    public $tipo_nota_credito_id;
    public $motivo_nota;

    // Items del documento original
    public $items_seleccionados = [];
    public $detalles = [];

    // Campos temporales para agregar detalle manual
    public $descripcion_temp;
    public $cantidad_temp = 1;
    public $precio_temp = 0;

    // Tasa de cambio BCV
    public $tasa_usd;

    // Porcentajes fiscales configurables (dinámicos por país)
    public $iva_pct = 16;
    public $iva_reducida_pct = 8;
    public $igtf_pct = 0;

    // Totales en Bolívares
    public $subtotal_bs = 0;
    public $base_imponible_bs = 0;
    public $monto_exento_bs = 0;
    public $iva_monto_bs = 0;
    public $igtf_monto_bs = 0;
    public $total_bs = 0;

    protected $rules = [
        'tipo_nota_credito_id' => 'required',
        'motivo_nota' => 'required|string|min:3',
        'detalles' => 'required|array|min:1',
    ];

    protected $messages = [
        'tipo_nota_credito_id.required' => 'Debe seleccionar el tipo de nota de crédito.',
        'motivo_nota.required' => 'El motivo de la nota es obligatorio.',
        'detalles.required' => 'Debe incluir al menos un detalle.',
        'detalles.min' => 'Debe incluir al menos un detalle.',
    ];

    public function mount()
    {
        $this->tasa_usd = ExchangeRate::getLatestRate('USD')
            ?: ExchangeRate::latest()->value('usd_rate')
            ?: 1;

        $this->cargarConfigImpuestos();
    }

    private function cargarConfigImpuestos(): void
    {
        $empresaId = auth()->user()->empresa_id;

        $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();
        $this->iva_pct = $ivaConfig ? (float) $ivaConfig->porcentaje : 16;

        $ivaRedConfig = ImpuestoConfiguracion::where('codigo', 'IVA_REDUCIDA')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();
        $this->iva_reducida_pct = $ivaRedConfig ? (float) $ivaRedConfig->porcentaje : 8;

        $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();
        $this->igtf_pct = $igtfConfig ? (float) $igtfConfig->porcentaje : 0;
    }

    /**
     * Busca la factura/boleta/recibo original por serie o número.
     */
    public function buscarFactura()
    {
        $this->validate(['buscar_serie' => 'required']);

        $this->reset(['pago_origen', 'items_seleccionados', 'detalles']);
        $this->resetTotales();

        $this->pago_origen = Pago::where(function ($q) {
                $q->where('serie', 'like', "%{$this->buscar_serie}%")
                  ->orWhere('numero', $this->buscar_serie);
            })
            ->whereIn('tipo_pago', ['factura', 'boleta', 'recibo'])
            ->where('estado', Pago::ESTADO_APROBADO)
            ->with(['detalles', 'clienteFiscal', 'consulta.paciente'])
            ->first();

        if (!$this->pago_origen) {
            session()->flash('error', 'No se encontró la factura con esa serie/número.');
            return;
        }

        // Usar la tasa de la factura original si está disponible
        if ($this->pago_origen->tasa_cambio_usd && $this->pago_origen->tasa_cambio_usd > 0) {
            $this->tasa_usd = (float) $this->pago_origen->tasa_cambio_usd;
        }

        // Pre-seleccionar todos los items del documento original
        $this->cargarItemsOrigen();
    }

    /**
     * Carga los items del documento original como detalles seleccionables.
     */
    private function cargarItemsOrigen()
    {
        if (!$this->pago_origen || !$this->pago_origen->detalles) {
            return;
        }

        $this->items_seleccionados = [];
        $this->detalles = [];

        foreach ($this->pago_origen->detalles as $index => $detalle) {
            $precioBs = (float) $detalle->precio_unitario;
            $cantidad = (float) $detalle->cantidad;
            $subtotalBs = (float) $detalle->subtotal;
            $precioUsd = $this->tasa_usd > 0 ? round($precioBs / $this->tasa_usd, 2) : 0;

            $this->items_seleccionados[$index] = true;

            $this->detalles[] = [
                'detalle_origen_id' => $detalle->id,
                'baremo_id' => $detalle->baremo_id,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $cantidad,
                'cantidad_max' => $cantidad,
                'precio_unitario_usd' => $precioUsd,
                'precio_unitario_bs' => $precioBs,
                'subtotal_bs' => $subtotalBs,
                'aplica_iva' => $detalle->aplica_iva ?? true,
                'exento_iva' => $detalle->exento_iva ?? false,
                'iva_alicuota' => $detalle->iva_alicuota ?? $this->iva_pct,
            ];
        }

        $this->calcularTotales();
    }

    /**
     * Alterna la selección de un item del documento original.
     */
    public function toggleItem($index)
    {
        if (isset($this->items_seleccionados[$index])) {
            unset($this->items_seleccionados[$index]);
        } else {
            $this->items_seleccionados[$index] = true;
        }

        $this->calcularTotales();
    }

    /**
     * Actualiza la cantidad de un detalle y recalcula.
     */
    public function updatedDetalles($value, $key)
    {
        // key format: "0.cantidad"
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'cantidad') {
            $index = (int) $parts[0];
            $cantidad = max(0, (float) $value);

            // No exceder cantidad original
            if (isset($this->detalles[$index]['cantidad_max'])) {
                $cantidad = min($cantidad, $this->detalles[$index]['cantidad_max']);
            }

            $this->detalles[$index]['cantidad'] = $cantidad;
            $precioBs = (float) ($this->detalles[$index]['precio_unitario_bs'] ?? 0);
            $this->detalles[$index]['subtotal_bs'] = round($cantidad * $precioBs, 2);
        }

        $this->calcularTotales();
    }

    /**
     * Agrega un detalle manual a la nota de crédito.
     */
    public function agregarDetalle()
    {
        $this->validate([
            'descripcion_temp' => 'required|string',
            'cantidad_temp' => 'required|numeric|min:1',
            'precio_temp' => 'required|numeric|min:0',
        ]);

        $precioUsd = (float) $this->precio_temp;
        $cantidad = (float) $this->cantidad_temp;
        $subtotalBs = round($cantidad * $precioUsd * $this->tasa_usd, 2);

        $newIndex = count($this->detalles);

        $this->detalles[] = [
            'detalle_origen_id' => null,
            'baremo_id' => null,
            'descripcion' => $this->descripcion_temp,
            'cantidad' => $cantidad,
            'cantidad_max' => $cantidad,
            'precio_unitario_usd' => $precioUsd,
            'precio_unitario_bs' => round($precioUsd * $this->tasa_usd, 2),
            'subtotal_bs' => $subtotalBs,
            'aplica_iva' => true,
            'exento_iva' => false,
            'iva_alicuota' => $this->iva_pct,
        ];

        $this->items_seleccionados[$newIndex] = true;

        $this->reset(['descripcion_temp', 'cantidad_temp', 'precio_temp']);
        $this->cantidad_temp = 1;
        $this->precio_temp = 0;
        $this->calcularTotales();
    }

    /**
     * Elimina un detalle de la lista.
     */
    public function eliminarDetalle($index)
    {
        unset($this->detalles[$index]);
        unset($this->items_seleccionados[$index]);

        $this->detalles = array_values($this->detalles);

        // Reconstruir items_seleccionados con índices correctos
        $nuevosSeleccionados = [];
        foreach ($this->detalles as $i => $detalle) {
            $nuevosSeleccionados[$i] = true;
        }
        $this->items_seleccionados = $nuevosSeleccionados;

        $this->calcularTotales();
    }

    /**
     * Calcula todos los totales fiscales en Bolívares según configuración por país.
     */
    public function calcularTotales()
    {
        $baseImponibleGeneralBs = 0;
        $baseImponibleReducidaBs = 0;
        $montoExentoBs = 0;

        foreach ($this->detalles as $index => $detalle) {
            if (!isset($this->items_seleccionados[$index])) {
                continue;
            }

            $subtotalBs = (float) ($detalle['subtotal_bs'] ?? 0);

            // Si el servicio NO aplica IVA o es exento, va a exentos (no grava)
            if (!($detalle['aplica_iva'] ?? true) || ($detalle['exento_iva'] ?? false)) {
                $montoExentoBs += $subtotalBs;
                continue;
            }

            $alicuota = (float) ($detalle['iva_alicuota'] ?? $this->iva_pct);

            if ($alicuota == $this->iva_reducida_pct) {
                $baseImponibleReducidaBs += $subtotalBs;
            } else {
                $baseImponibleGeneralBs += $subtotalBs;
            }
        }

        $this->monto_exento_bs = round($montoExentoBs, 2);
        $this->base_imponible_bs = round($baseImponibleGeneralBs + $baseImponibleReducidaBs, 2);
        $this->subtotal_bs = round($this->base_imponible_bs + $this->monto_exento_bs, 2);

        // IVA en Bs
        $ivaMontoGeneralBs = $baseImponibleGeneralBs * ($this->iva_pct / 100);
        $ivaMontoReducidaBs = $baseImponibleReducidaBs * ($this->iva_reducida_pct / 100);
        $this->iva_monto_bs = round($ivaMontoGeneralBs + $ivaMontoReducidaBs, 2);

        // IGTF en Bs (aplica si el pago original fue en divisas)
        $this->igtf_monto_bs = 0;
        if ($this->pago_origen && ($this->pago_origen->aplica_igtf ?? false) && $this->igtf_pct > 0) {
            $metodoPagoOriginal = $this->pago_origen->metodo_pago;
            $pagoEnDivisas = false;

            $metodosDivisas = ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'];

            if ($metodoPagoOriginal === 'mixto') {
                $pagosMixtos = $this->pago_origen->detalles_pago_mixto
                    ?? json_decode($this->pago_origen->pagos_mixtos, true) ?? [];
                foreach ($pagosMixtos as $pm) {
                    if (in_array($pm['metodo'] ?? '', $metodosDivisas)) {
                        $pagoEnDivisas = true;
                        break;
                    }
                }
            } elseif (in_array($metodoPagoOriginal, $metodosDivisas)) {
                $pagoEnDivisas = true;
            }

            if ($pagoEnDivisas) {
                // IGTF aplica sobre el total de la transacción (base + exento + IVA)
                $baseIGTF = $this->subtotal_bs + $this->iva_monto_bs;
                $this->igtf_monto_bs = round($baseIGTF * ($this->igtf_pct / 100), 2);
            }
        }

        $this->total_bs = round($this->subtotal_bs + $this->iva_monto_bs + $this->igtf_monto_bs, 2);
    }

    private function resetTotales()
    {
        $this->subtotal_bs = 0;
        $this->base_imponible_bs = 0;
        $this->monto_exento_bs = 0;
        $this->iva_monto_bs = 0;
        $this->igtf_monto_bs = 0;
        $this->total_bs = 0;
    }

    /**
     * Guarda la Nota de Crédito como un Pago con tipo nota_credito.
     */
    public function guardar()
    {
        $this->validate();

        if (!$this->pago_origen) {
            session()->flash('error', 'Debe buscar y seleccionar una factura origen.');
            return;
        }

        // Filtrar solo detalles seleccionados
        $detallesSeleccionados = [];
        foreach ($this->detalles as $index => $detalle) {
            if (isset($this->items_seleccionados[$index])) {
                $detallesSeleccionados[] = $detalle;
            }
        }

        if (empty($detallesSeleccionados)) {
            $this->addError('detalles', 'Debe seleccionar al menos un item.');
            return;
        }

        $pagoOrigen = $this->pago_origen;

        // Validar que el monto de la NC no exceda el saldo disponible de la factura
        if ($this->total_bs > 0 && $pagoOrigen->saldo_disponible !== null) {
            $totalNcUsd = $this->tasa_usd > 0 ? $this->total_bs / $this->tasa_usd : 0;
            if ($totalNcUsd > ($pagoOrigen->saldo_disponible + 0.01)) {
                session()->flash('error', 'El monto de la Nota de Crédito excede el saldo disponible de la factura.');
                return;
            }
        }

        // Obtener caja aperturada
        $caja = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->latest()
            ->first();

        if (!$caja) {
            session()->flash('error', 'Debe aperturar una caja antes de registrar notas de crédito.');
            return;
        }

        $empresaId = auth()->user()->empresa_id;
        $sucursalId = auth()->user()->sucursal_id;

        // Generar número de control fiscal
        $numeroControlFiscal = null;
        if ($pagoOrigen->es_factura_fiscal) {
            try {
                $numeroControlFiscal = FiscalNumberingService::generarNumeroControl(
                    $empresaId,
                    $sucursalId,
                    'nota_credito'
                );
            } catch (\RuntimeException $e) {
                session()->flash('error', $e->getMessage());
                return;
            }
        }

        // Calcular totales USD desde Bs
        $subtotalUsd = $this->tasa_usd > 0 ? round($this->subtotal_bs / $this->tasa_usd, 2) : 0;
        $totalUsd = $this->tasa_usd > 0 ? round($this->total_bs / $this->tasa_usd, 2) : 0;

        // Totales ya calculados en Bs
        $ivaMontoGeneralBs = 0;
        $ivaMontoReducidaBs = 0;
        $baseGeneralBs = 0;
        $baseReducidaBs = 0;
        $montoExentoBs = 0;

        foreach ($detallesSeleccionados as $d) {
            $sub = (float) ($d['subtotal_bs'] ?? 0);
            // Si el servicio NO aplica IVA o es exento, va a exentos (no grava)
            if (!($d['aplica_iva'] ?? true) || ($d['exento_iva'] ?? false)) {
                $montoExentoBs += $sub;
            } elseif (($d['iva_alicuota'] ?? $this->iva_pct) == $this->iva_reducida_pct) {
                $baseReducidaBs += $sub;
            } else {
                $baseGeneralBs += $sub;
            }
        }

        $ivaMontoGeneralBs = round($baseGeneralBs * ($this->iva_pct / 100), 2);
        $ivaMontoReducidaBs = round($baseReducidaBs * ($this->iva_reducida_pct / 100), 2);

        $nota = Pago::create([
            'pago_origen_id' => $pagoOrigen->id,
            'consulta_id' => $pagoOrigen->consulta_id,
            'caja_id' => $caja->id,
            'tipo_pago' => Pago::TIPO_NOTA_CREDITO,
            'tipo_nota_credito_id' => $this->tipo_nota_credito_id,
            'fecha' => now(),
            'user_id' => auth()->id(),
            'subtotal' => $this->subtotal_bs,
            'total' => $this->total_bs,
            'tasa_cambio_usd' => $this->tasa_usd,
            'total_usd' => $totalUsd,
            'total_bs' => $this->total_bs,
            'metodo_pago' => $pagoOrigen->metodo_pago,
            'motivo_nota' => $this->motivo_nota,
            'cliente_fiscal_id' => $pagoOrigen->cliente_fiscal_id,
            'es_factura_fiscal' => $pagoOrigen->es_factura_fiscal,
            'condicion_pago' => $pagoOrigen->condicion_pago ?? 'contado',
            'estado' => Pago::ESTADO_APROBADO,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            // Campos fiscales SENIAT
            'seniat_tipo_documento' => FiscalCalculator::getSeniatTipoDocumento('nota_credito'),
            'fecha_emision_fiscal' => now(),
            'fecha_vencimiento_fiscal' => null,
            'numero_control_fiscal' => $numeroControlFiscal,
            'base_imponible' => $baseGeneralBs + $baseReducidaBs,
            'monto_exento' => $montoExentoBs,
            'base_imponible_general' => $baseGeneralBs,
            'iva_monto_general' => $ivaMontoGeneralBs,
            'base_imponible_reducida' => $baseReducidaBs,
            'iva_monto_reducida' => $ivaMontoReducidaBs,
            'iva_porcentaje' => $ivaPorcentaje,
            'iva_monto' => $this->iva_monto_bs,
            'aplica_igtf' => $this->igtf_monto_bs > 0,
            'igtf_porcentaje' => $this->igtf_monto_bs > 0 ? $igtfPorcentaje : 0,
            'igtf_monto' => $this->igtf_monto_bs,
        ]);

        // Crear detalles de la nota
        foreach ($detallesSeleccionados as $detalle) {
            $nota->detalles()->create([
                'baremo_id' => $detalle['baremo_id'] ?? null,
                'descripcion' => $detalle['descripcion'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario_bs'],
                'subtotal' => $detalle['subtotal_bs'],
                'aplica_iva' => $detalle['aplica_iva'] ?? true,
                'exento_iva' => $detalle['exento_iva'] ?? false,
                'iva_alicuota' => $detalle['iva_alicuota'] ?? $this->iva_pct,
            ]);
        }

        // Recalculate via model to ensure consistency
        $nota->refresh();
        $nota->calcularTotales();

        event(new PagoCreated($nota));

        session()->flash('success', 'Nota de Crédito creada exitosamente.');
        return redirect()->route('admin.notas-credito.index');
    }

    public function render()
    {
        $tipos = TipoNotaCredito::activos()->get();

        return view('livewire.admin.nota-credito.crear-nota-credito', compact('tipos'));
    }

    /**
     * Porcentaje IVA configurado (general).
     */
    public function getIvaPorcentajeProperty(): float
    {
        return $this->iva_pct;
    }

    /**
     * Porcentaje IVA reducido configurado.
     */
    public function getIvaReducidaPorcentajeProperty(): float
    {
        return $this->iva_reducida_pct;
    }

    /**
     * Porcentaje IGTF configurado.
     */
    public function getIgtfPorcentajeProperty(): float
    {
        return $this->igtf_pct;
    }
}
