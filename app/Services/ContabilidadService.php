<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\CuentaContable;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class ContabilidadService
{
    public function generarAsientoFactura(Pago $pago)
    {
        if (!in_array($pago->tipo_pago, ['factura', 'boleta', 'recibo'])) {
            return null;
        }

        return DB::transaction(function () use ($pago) {
            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($pago->empresa_id),
                'fecha' => $pago->fecha,
                'tipo' => 'diario',
                'descripcion' => "Factura {$pago->numero_completo}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'factura',
                'referencia_id' => $pago->id,
                'user_id' => $pago->user_id,
                'empresa_id' => $pago->empresa_id,
                'sucursal_id' => $pago->sucursal_id
            ]);

            // DEBE: Caja (pago de contado)
            $cuentaCaja = CuentaContable::where('codigo', '1.1.01.001')
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            if ($cuentaCaja) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaCaja->id,
                    'debe' => $pago->total_bs,
                    'haber' => 0,
                    'descripcion' => "Cobro factura {$pago->numero_completo}"
                ]);
            }

            // HABER: Ingresos por Consultas
            $cuentaIngreso = CuentaContable::where('codigo', '4.1.01')
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            $subtotal = $pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd);
            if ($cuentaIngreso) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaIngreso->id,
                    'debe' => 0,
                    'haber' => $subtotal,
                    'descripcion' => 'Ingreso por consulta médica'
                ]);
            }

            // HABER: IVA por Pagar
            if (($pago->iva_monto ?? 0) > 0) {
                $cuentaIVA = CuentaContable::where('codigo', '2.1.01.001')
                    ->where('empresa_id', $pago->empresa_id)
                    ->first();

                if ($cuentaIVA) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIVA->id,
                        'debe' => 0,
                        'haber' => $pago->iva_monto,
                        'descripcion' => 'IVA por pagar'
                    ]);
                }
            }

            return $asiento;
        });
    }

    public function generarAsientoPago(Pago $pago)
    {
        return DB::transaction(function () use ($pago) {
            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($pago->empresa_id),
                'fecha' => $pago->fecha,
                'tipo' => 'diario',
                'descripcion' => "Pago recibido - {$pago->metodo_pago}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'pago',
                'referencia_id' => $pago->id,
                'user_id' => $pago->user_id,
                'empresa_id' => $pago->empresa_id,
                'sucursal_id' => $pago->sucursal_id
            ]);

            // DEBE: Caja/Banco
            $cuentaCaja = CuentaContable::where('codigo', '1.1.01.001')
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            if ($cuentaCaja) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaCaja->id,
                    'debe' => $pago->total_bs,
                    'haber' => 0,
                    'descripcion' => "Pago recibido - {$pago->metodo_pago}"
                ]);
            }

            // HABER: Cuentas por Cobrar
            $cuentaPorCobrar = CuentaContable::where('codigo', '1.1.02.001')
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            if ($cuentaPorCobrar) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaPorCobrar->id,
                    'debe' => 0,
                    'haber' => $pago->total_bs,
                    'descripcion' => 'Cobro de cuenta por cobrar'
                ]);
            }

            return $asiento;
        });
    }

    public function generarAsientoNotaCredito(Pago $notaCredito)
    {
        if ($notaCredito->tipo_pago !== Pago::TIPO_NOTA_CREDITO) {
            return null;
        }

        return DB::transaction(function () use ($notaCredito) {
            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($notaCredito->empresa_id),
                'fecha' => $notaCredito->fecha,
                'tipo' => 'diario',
                'descripcion' => "Nota de Crédito {$notaCredito->numero_completo}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'nota_credito',
                'referencia_id' => $notaCredito->id,
                'user_id' => $notaCredito->user_id,
                'empresa_id' => $notaCredito->empresa_id,
                'sucursal_id' => $notaCredito->sucursal_id
            ]);

            // DEBE: Devoluciones y Rebajas
            $cuentaDevolucion = CuentaContable::where('codigo', '4.2.01')
                ->where('empresa_id', $notaCredito->empresa_id)
                ->first();

            if ($cuentaDevolucion) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaDevolucion->id,
                    'debe' => abs($notaCredito->subtotal_bs ?? 0),
                    'haber' => 0,
                    'descripcion' => 'Devolución en venta'
                ]);
            }

            // DEBE: IVA por Pagar
            if (abs($notaCredito->iva_monto ?? 0) > 0) {
                $cuentaIVA = CuentaContable::where('codigo', '2.1.01.001')
                    ->where('empresa_id', $notaCredito->empresa_id)
                    ->first();

                if ($cuentaIVA) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIVA->id,
                        'debe' => abs($notaCredito->iva_monto),
                        'haber' => 0,
                        'descripcion' => 'Reversión IVA'
                    ]);
                }
            }

            // HABER: Cuentas por Cobrar
            $cuentaPorCobrar = CuentaContable::where('codigo', '1.1.02.001')
                ->where('empresa_id', $notaCredito->empresa_id)
                ->first();

            if ($cuentaPorCobrar) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaPorCobrar->id,
                    'debe' => 0,
                    'haber' => abs($notaCredito->total_bs ?? 0),
                    'descripcion' => 'Reducción cuenta por cobrar'
                ]);
            }

            return $asiento;
        });
    }

    public function generarAsientoNotaDebito(Pago $notaDebito)
    {
        if ($notaDebito->tipo_pago !== Pago::TIPO_NOTA_DEBITO) {
            return null;
        }

        return DB::transaction(function () use ($notaDebito) {
            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($notaDebito->empresa_id),
                'fecha' => $notaDebito->fecha,
                'tipo' => 'diario',
                'descripcion' => "Nota de Débito {$notaDebito->numero_completo}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'nota_debito',
                'referencia_id' => $notaDebito->id,
                'user_id' => $notaDebito->user_id,
                'empresa_id' => $notaDebito->empresa_id,
                'sucursal_id' => $notaDebito->sucursal_id
            ]);

            // DEBE: Cuentas por Cobrar
            $cuentaPorCobrar = CuentaContable::where('codigo', '1.1.02.001')
                ->where('empresa_id', $notaDebito->empresa_id)
                ->first();

            if ($cuentaPorCobrar) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaPorCobrar->id,
                    'debe' => $notaDebito->total_bs,
                    'haber' => 0,
                    'descripcion' => 'Aumento cuenta por cobrar'
                ]);
            }

            // HABER: Devoluciones y Rebajas (reversión)
            $cuentaDevolucion = CuentaContable::where('codigo', '4.2.01')
                ->where('empresa_id', $notaDebito->empresa_id)
                ->first();

            if ($cuentaDevolucion) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaDevolucion->id,
                    'debe' => 0,
                    'haber' => $notaDebito->subtotal_bs,
                    'descripcion' => 'Reversión de devolución'
                ]);
            }

            // HABER: IVA por Pagar
            if ($notaDebito->iva_monto > 0) {
                $cuentaIVA = CuentaContable::where('codigo', '2.1.01.001')
                    ->where('empresa_id', $notaDebito->empresa_id)
                    ->first();

                if ($cuentaIVA) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIVA->id,
                        'debe' => 0,
                        'haber' => $notaDebito->iva_monto,
                        'descripcion' => 'IVA por pagar'
                    ]);
                }
            }

            return $asiento;
        });
    }
}
