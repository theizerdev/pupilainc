<?php

namespace App\Livewire\Admin\NotaDebito;

use Livewire\Component;
use App\Models\Pago;
use App\Models\TipoNotaDebito;
use App\Models\ExchangeRate;
use App\Models\ImpuestoConfiguracion;
use App\Services\Seniat\FiscalCalculator;
use App\Services\Seniat\FiscalNumberingService;
use App\Events\PagoCreated;

class CrearNotaDebito extends Component
{
    public $buscar_serie;
    public $pago_origen;
    public $nota_credito_origen; // Nueva propiedad para NC
    public $tipo_nota_debito_id;
    public $motivo_nota;
    public $detalles = [];
    public $descripcion_temp;
    public $cantidad_temp = 1;
    public $precio_temp;
    public $tasa_usd;
    public $subtotal_bs = 0;
    public $base_imponible_bs = 0;
    public $iva_monto_bs = 0;
    public $igtf_monto_bs = 0;
    public $monto_exento_bs = 0;
    public $total_bs = 0;

    protected $rules = [
        'tipo_nota_debito_id' => 'required',
        'motivo_nota' => 'required',
        'detalles' => 'required|array|min:1'
    ];

    public function mount($nota_credito_id = null)
    {
        $this->tasa_usd = ExchangeRate::getLatestRate('USD') ?? ExchangeRate::latest()->value('usd_rate') ?? 1;
        
        // Si viene una NC para anular, cargarla automáticamente
        if ($nota_credito_id) {
            $this->cargarNotaCredito($nota_credito_id);
        }
    }
    
    private function cargarNotaCredito($id)
    {
        $this->nota_credito_origen = Pago::where('id', $id)
            ->where('tipo_pago', Pago::TIPO_NOTA_CREDITO)
            ->where('estado', 'aprobado')
            ->with(['detalles', 'clienteFiscal', 'consulta.paciente', 'pagoOrigen'])
            ->first();
            
        if (!$this->nota_credito_origen) {
            session()->flash('error', 'Nota de Crédito no encontrada o ya anulada');
            return;
        }
        
        // Usar la factura original como origen
        $this->pago_origen = $this->nota_credito_origen->pagoOrigen;
        $this->tasa_usd = $this->nota_credito_origen->tasa_cambio_usd ?? $this->tasa_usd;
        
        // Cargar detalles de la NC para anularla
        foreach ($this->nota_credito_origen->detalles as $detalle) {
            $this->detalles[] = [
                'descripcion' => 'Anulación NC: ' . $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'precio_unitario_usd' => $detalle->precio_unitario / $this->tasa_usd,
                'precio_unitario_bs' => $detalle->precio_unitario,
                'subtotal_bs' => $detalle->subtotal,
                'aplica_iva' => $detalle->aplica_iva,
                'exento_iva' => $detalle->exento_iva,
                'iva_alicuota' => $detalle->iva_alicuota
            ];
        }
        
        $this->motivo_nota = 'Anulación de Nota de Crédito ' . $this->nota_credito_origen->numero_completo;
        $this->calcularTotales();
    }

    public function buscarFactura()
    {
        $this->validate(['buscar_serie' => 'required']);
        
        // Buscar primero en facturas
        $this->pago_origen = Pago::where(function($q) {
                $q->where('serie', 'like', "%{$this->buscar_serie}%")
                  ->orWhere('numero', $this->buscar_serie);
            })
            ->whereIn('tipo_pago', ['factura', 'boleta', 'recibo'])
            ->where('estado', 'aprobado')
            ->with('detalles', 'clienteFiscal', 'consulta.paciente')
            ->first();
        
        // Si no encuentra factura, buscar en notas de crédito
        if (!$this->pago_origen) {
            $this->nota_credito_origen = Pago::where(function($q) {
                    $q->where('serie', 'like', "%{$this->buscar_serie}%")
                      ->orWhere('numero', $this->buscar_serie);
                })
                ->where('tipo_pago', Pago::TIPO_NOTA_CREDITO)
                ->where('estado', 'aprobado')
                ->with(['detalles', 'clienteFiscal', 'consulta.paciente', 'pagoOrigen'])
                ->first();
                
            if ($this->nota_credito_origen) {
                $this->cargarNotaCredito($this->nota_credito_origen->id);
                session()->flash('info', 'Se encontró una Nota de Crédito. Esta ND la anulará.');
                return;
            }
        }
            
        if (!$this->pago_origen && !$this->nota_credito_origen) {
            session()->flash('error', 'No se encontró el documento');
            return;
        }
    }

    public function agregarDetalle()
    {
        $this->validate([
            'descripcion_temp' => 'required',
            'cantidad_temp' => 'required|numeric|min:0.01',
            'precio_temp' => 'required|numeric|min:0'
        ]);

        $precio_bs = $this->precio_temp * $this->tasa_usd;
        $subtotal_bs = $this->cantidad_temp * $precio_bs;

        $this->detalles[] = [
            'descripcion' => $this->descripcion_temp,
            'cantidad' => $this->cantidad_temp,
            'precio_unitario_usd' => $this->precio_temp,
            'precio_unitario_bs' => $precio_bs,
            'subtotal_bs' => $subtotal_bs,
            'aplica_iva' => true,
            'exento_iva' => false,
            'iva_alicuota' => 16
        ];

        $this->reset(['descripcion_temp', 'cantidad_temp', 'precio_temp']);
        $this->calcularTotales();
    }

    public function eliminarDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        $baseImponibleGeneralBs = 0;
        $baseImponibleReducidaBs = 0;
        $montoExentoBs = 0;

        foreach ($this->detalles as $detalle) {
            $subtotalBs = (float) ($detalle['subtotal_bs'] ?? 0);

            if ($detalle['exento_iva'] ?? false) {
                $montoExentoBs += $subtotalBs;
                continue;
            }

            $alicuota = (float) ($detalle['iva_alicuota'] ?? 16);

            if ($alicuota == 8) {
                $baseImponibleReducidaBs += $subtotalBs;
            } else {
                $baseImponibleGeneralBs += $subtotalBs;
            }
        }

        $this->monto_exento_bs = round($montoExentoBs, 2);
        $this->base_imponible_bs = round($baseImponibleGeneralBs + $baseImponibleReducidaBs, 2);
        $this->subtotal_bs = round($this->base_imponible_bs + $this->monto_exento_bs, 2);

        // IVA
        $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->first();

        $ivaPctGeneral = $ivaConfig ? (float) $ivaConfig->porcentaje : 16;
        $ivaMontoGeneralBs = $baseImponibleGeneralBs * ($ivaPctGeneral / 100);
        $ivaMontoReducidaBs = $baseImponibleReducidaBs * (8 / 100);
        $this->iva_monto_bs = round($ivaMontoGeneralBs + $ivaMontoReducidaBs, 2);

        // IGTF (aplica si el documento origen fue pagado en divisas)
        $this->igtf_monto_bs = 0;
        $documentoRef = $this->nota_credito_origen ?? $this->pago_origen;
        if ($documentoRef && ($documentoRef->aplica_igtf ?? false)) {
            // Verificar si el método de pago original era en divisas
            $metodoPagoOriginal = $documentoRef->metodo_pago;
            $pagoEnDivisas = false;
            
            if ($metodoPagoOriginal === 'mixto') {
                $pagosMixtos = $documentoRef->detalles_pago_mixto ?? json_decode($documentoRef->pagos_mixtos, true) ?? [];
                foreach ($pagosMixtos as $pm) {
                    if (in_array($pm['metodo'] ?? '', ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                        $pagoEnDivisas = true;
                        break;
                    }
                }
            } elseif (in_array($metodoPagoOriginal, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                $pagoEnDivisas = true;
            }
            
            if ($pagoEnDivisas) {
                $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
                    ->where('empresa_id', auth()->user()->empresa_id)
                    ->where('activo', true)
                    ->first();

                if ($igtfConfig) {
                    // IGTF = 3% sobre la base imponible (monto en divisas = subtotal + IVA)
                    $baseImponibleIGTF = $this->subtotal_bs + $this->iva_monto_bs;
                    $this->igtf_monto_bs = round($baseImponibleIGTF * ((float) $igtfConfig->porcentaje / 100), 2);
                }
            }
        }

        $this->total_bs = round($this->subtotal_bs + $this->iva_monto_bs + $this->igtf_monto_bs, 2);
    }

    public function guardar()
    {
        $this->validate();

        $documentoOrigen = $this->nota_credito_origen ?? $this->pago_origen;
        
        if (!$documentoOrigen) {
            session()->flash('error', 'Debe buscar un documento origen.');
            return;
        }

        // Validar caja abierta
        $caja = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->latest()
            ->first();

        if (!$caja) {
            session()->flash('error', 'Debe aperturar una caja antes de registrar notas de débito.');
            return;
        }

        $empresaId = auth()->user()->empresa_id;
        $sucursalId = auth()->user()->sucursal_id;

        // Generar número de control fiscal
        $numeroControlFiscal = null;
        if ($documentoOrigen->es_factura_fiscal) {
            try {
                $numeroControlFiscal = FiscalNumberingService::generarNumeroControl(
                    $empresaId,
                    $sucursalId,
                    'nota_debito'
                );
            } catch (\RuntimeException $e) {
                session()->flash('error', $e->getMessage());
                return;
            }
        }

        // IVA config
        $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();
        $ivaPorcentaje = $ivaConfig ? (float) $ivaConfig->porcentaje : 16;

        $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();
        $igtfPorcentaje = $igtfConfig ? (float) $igtfConfig->porcentaje : 0;

        // Segregar bases imponibles
        $baseGeneralBs = 0;
        $baseReducidaBs = 0;
        $montoExentoBs = 0;

        foreach ($this->detalles as $d) {
            $sub = (float) ($d['subtotal_bs'] ?? 0);
            if ($d['exento_iva'] ?? false) {
                $montoExentoBs += $sub;
            } elseif (($d['iva_alicuota'] ?? 16) == 8) {
                $baseReducidaBs += $sub;
            } else {
                $baseGeneralBs += $sub;
            }
        }

        $ivaMontoGeneralBs = round($baseGeneralBs * ($ivaPorcentaje / 100), 2);
        $ivaMontoReducidaBs = round($baseReducidaBs * (8 / 100), 2);

        // Calcular totales USD
        $totalUsd = $this->tasa_usd > 0 ? round($this->total_bs / $this->tasa_usd, 2) : 0;
        $subtotalUsd = $this->tasa_usd > 0 ? round($this->subtotal_bs / $this->tasa_usd, 2) : 0;

        $nota = Pago::create([
            'pago_origen_id' => $documentoOrigen->id,
            'consulta_id' => $documentoOrigen->consulta_id,
            'caja_id' => $caja->id,
            'tipo_pago' => Pago::TIPO_NOTA_DEBITO,
            'tipo_nota_debito_id' => $this->tipo_nota_debito_id,
            'fecha' => now(),
            'user_id' => auth()->id(),
            'subtotal' => $subtotalUsd,
            'total' => $totalUsd,
            'tasa_cambio_usd' => $this->tasa_usd,
            'total_usd' => $totalUsd,
            'total_bs' => $this->total_bs,
            'metodo_pago' => $documentoOrigen->metodo_pago,
            'motivo_nota' => $this->motivo_nota,
            'cliente_fiscal_id' => $documentoOrigen->cliente_fiscal_id,
            'es_factura_fiscal' => $documentoOrigen->es_factura_fiscal,
            'condicion_pago' => $documentoOrigen->condicion_pago ?? 'contado',
            'estado' => Pago::ESTADO_APROBADO,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            // Campos fiscales SENIAT
            'seniat_tipo_documento' => FiscalCalculator::getSeniatTipoDocumento('nota_debito'),
            'fecha_emision_fiscal' => now(),
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

        foreach ($this->detalles as $detalle) {
            $nota->detalles()->create([
                'descripcion' => $detalle['descripcion'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario_bs'],
                'subtotal' => $detalle['subtotal_bs'],
                'aplica_iva' => $detalle['aplica_iva'],
                'exento_iva' => $detalle['exento_iva'],
                'iva_alicuota' => $detalle['iva_alicuota']
            ]);
        }

        $nota->refresh();
        $nota->calcularTotales();

        event(new PagoCreated($nota));

        session()->flash('success', 'Nota de Débito creada exitosamente');
        return redirect()->route('admin.notas-debito.index');
    }

    public function render()
    {
        $tipos = TipoNotaDebito::where('activo', true)->get();
        return view('livewire.admin.nota-debito.crear-nota-debito', compact('tipos'));
    }
}
