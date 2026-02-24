<?php

namespace App\Livewire\Admin\Pagos;

use Livewire\Component;
use App\Models\Pago;
use App\Models\Serie;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\DB;

class CrearNotaCredito extends Component
{
    use HasDynamicLayout;

    public $pago_id;
    public $factura;
    public $tipo_nota = 'total'; // total, parcial
    public $motivo;
    public $monto_nota;
    public $tipo_motivo_id;
    
    protected $rules = [
        'pago_id' => 'required|exists:pagos,id',
        'tipo_nota' => 'required|in:total,parcial',
        'motivo' => 'required|string|min:10',
        'monto_nota' => 'nullable|numeric|min:0.01',
    ];

    public function mount($pagoId = null)
    {
        if ($pagoId) {
            $this->pago_id = $pagoId;
            $this->cargarFactura();
        }
    }

    public function cargarFactura()
    {
        $this->factura = Pago::with(['consulta.paciente', 'clienteFiscal', 'detalles', 'notasCredito'])
            ->where('id', $this->pago_id)
            ->whereIn('tipo_pago', ['factura', 'boleta'])
            ->firstOrFail();

        // Validar que la factura esté aprobada
        if ($this->factura->estado !== Pago::ESTADO_APROBADO) {
            session()->flash('error', 'Solo se pueden anular facturas aprobadas.');
            return redirect()->route('admin.pagos.index');
        }

        // Calcular saldo disponible
        $this->monto_nota = $this->factura->saldo_disponible;
    }

    public function updatedTipoNota($value)
    {
        if ($value === 'total') {
            $this->monto_nota = $this->factura->saldo_disponible;
        } else {
            $this->monto_nota = 0;
        }
    }

    public function generarNotaCredito()
    {
        $this->validate();

        // Validaciones adicionales
        if ($this->tipo_nota === 'parcial' && $this->monto_nota > $this->factura->saldo_disponible) {
            session()->flash('error', 'El monto de la nota de crédito no puede exceder el saldo disponible.');
            return;
        }

        if ($this->tipo_nota === 'total') {
            $this->monto_nota = $this->factura->saldo_disponible;
        }

        DB::beginTransaction();
        try {
            // Obtener numeración para nota de crédito
            $numeracion = Pago::generarNumero(
                'nota_credito',
                $this->factura->empresa_id,
                $this->factura->sucursal_id
            );

            // Obtener número de control fiscal
            $serie = Serie::find($numeracion['serie_id']);
            $numeroControlFiscal = $serie->numero_control_fiscal;

            // CORRECCIÓN: El monto_nota ya está en USD
            $montoNotaUSD = $this->monto_nota;
            $montoNotaBs = $montoNotaUSD * $this->factura->tasa_cambio_usd;
            
            // Calcular proporciones de IVA, exento y base imponible
            $factorProporcional = $montoNotaUSD / abs($this->factura->total_usd);
            
            // Solo calcular si la factura original tiene estos valores
            $baseImponible = 0;
            $montoExento = 0;
            $ivaMonto = 0;
            
            if ($this->factura->base_imponible > 0) {
                $baseImponible = $this->factura->base_imponible * $factorProporcional;
            }
            
            if ($this->factura->monto_exento > 0) {
                $montoExento = $this->factura->monto_exento * $factorProporcional;
            }
            
            if ($this->factura->iva_monto > 0) {
                $ivaMonto = $this->factura->iva_monto * $factorProporcional;
            }
            
            // Calcular bases segregadas proporcionalmente
            $baseImponibleGeneral = 0;
            $ivaMontoGeneral = 0;
            $baseImponibleReducida = 0;
            $ivaMontoReducida = 0;
            $igtfMonto = 0;
            $aplica_igtf = false;

            if ($this->factura->base_imponible_general > 0) {
                $baseImponibleGeneral = $this->factura->base_imponible_general * $factorProporcional;
                $ivaMontoGeneral = $this->factura->iva_monto_general * $factorProporcional;
            }
            if ($this->factura->base_imponible_reducida > 0) {
                $baseImponibleReducida = $this->factura->base_imponible_reducida * $factorProporcional;
                $ivaMontoReducida = $this->factura->iva_monto_reducida * $factorProporcional;
            }
            // Calcular IGTF proporcional basado en el método de pago original
            $igtfMonto = 0;
            $aplica_igtf = false;
            
            if ($this->factura->aplica_igtf && $this->factura->igtf_monto > 0) {
                // Verificar si el método de pago original era en divisas
                $metodoPagoOriginal = $this->factura->metodo_pago;
                $pagoEnDivisas = false;
                
                if ($metodoPagoOriginal === 'mixto') {
                    // Verificar si había pagos en divisas en el mixto
                    $pagosMixtos = $this->factura->detalles_pago_mixto ?? json_decode($this->factura->pagos_mixtos, true) ?? [];
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
                    $aplica_igtf = true;
                    // Calcular IGTF proporcional: 3% sobre la base imponible (monto en divisas)
                    $baseImponibleIGTF = $montoNotaBs; // El monto de la nota ya está en Bs
                    $igtfMonto = $baseImponibleIGTF * 0.03;
                }
            }

            // Crear nota de crédito
            $notaCredito = Pago::create([
                'tipo_pago' => Pago::TIPO_NOTA_CREDITO,
                'pago_origen_id' => $this->factura->id,
                'serie_id' => $numeracion['serie_id'],
                'serie' => $numeracion['serie'],
                'numero' => $numeracion['numero'],
                'numero_control_fiscal' => $numeroControlFiscal,
                'motivo_nota' => $this->motivo,
                'tipo_nota_credito_id' => $this->tipo_motivo_id,
                'consulta_id' => $this->factura->consulta_id,
                'cliente_fiscal_id' => $this->factura->cliente_fiscal_id,
                'caja_id' => $this->factura->caja_id,
                'fecha' => now(),
                'user_id' => auth()->id(),
                'subtotal' => $montoNotaUSD,
                'total' => $montoNotaUSD,
                'total_usd' => $montoNotaUSD,
                'total_bs' => $montoNotaBs,
                'base_imponible' => $baseImponible,
                'monto_exento' => $montoExento,
                'base_imponible_general' => $baseImponibleGeneral,
                'iva_monto_general' => $ivaMontoGeneral,
                'base_imponible_reducida' => $baseImponibleReducida,
                'iva_monto_reducida' => $ivaMontoReducida,
                'iva_monto' => $ivaMonto,
                'iva_porcentaje' => $this->factura->iva_porcentaje,
                'aplica_igtf' => $aplica_igtf,
                'igtf_porcentaje' => $this->factura->igtf_porcentaje ?? 0,
                'igtf_monto' => $igtfMonto,
                'tasa_cambio_usd' => $this->factura->tasa_cambio_usd,
                'metodo_pago' => $this->factura->metodo_pago,
                'estado' => Pago::ESTADO_APROBADO,
                'empresa_id' => $this->factura->empresa_id,
                'sucursal_id' => $this->factura->sucursal_id,
                'es_factura_fiscal' => $this->factura->es_factura_fiscal,
                'seniat_tipo_documento' => '03',
                'fecha_emision_fiscal' => now(),
                'condicion_pago' => $this->factura->condicion_pago ?? 'contado',
            ]);

            // Copiar detalles proporcionalmente
            foreach ($this->factura->detalles as $detalle) {
                $cantidadNota = $detalle->cantidad * $factorProporcional;
                $subtotalNota = $detalle->subtotal * $factorProporcional;
                
                $notaCredito->detalles()->create([
                    'baremo_id' => $detalle->baremo_id,
                    'descripcion' => $detalle->descripcion,
                    'cantidad' => $cantidadNota,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $subtotalNota,
                    'aplica_iva' => $detalle->aplica_iva,
                    'exento_iva' => $detalle->exento_iva,
                    'iva_alicuota' => $detalle->iva_alicuota,
                ]);
            }

            DB::commit();

            session()->flash('success', 'Nota de crédito generada exitosamente: ' . $notaCredito->numero_completo);
            return redirect()->route('admin.pagos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al generar nota de crédito: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $tiposMotivo = \App\Models\TipoNotaCredito::where('activo', true)->get();
        
        return view('livewire.admin.pagos.crear-nota-credito', [
            'tiposMotivo' => $tiposMotivo
        ])->layout($this->getLayout());
    }
}
