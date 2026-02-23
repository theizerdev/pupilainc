<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\AsientoDetalle;
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

            // DEBE: Cuenta según método de pago
            $codigoCuenta = $this->getCodigoCuentaPago($pago->metodo_pago);
            $cuentaPago = CuentaContable::where('codigo', $codigoCuenta)
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            $totalDebe = $pago->total_bs;
            if (($pago->igtf_monto ?? 0) > 0) {
                $totalDebe = ($pago->total_con_impuestos ?? $pago->total_bs) ?: $pago->total_bs;
            }

            if ($cuentaPago) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaPago->id,
                    'debe' => $totalDebe,
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

            // HABER: IGTF por Pagar
            if (($pago->igtf_monto ?? 0) > 0) {
                $cuentaIGTF = CuentaContable::where('codigo', '2.1.01.002')
                    ->where('empresa_id', $pago->empresa_id)
                    ->first();

                if ($cuentaIGTF) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIGTF->id,
                        'debe' => 0,
                        'haber' => $pago->igtf_monto,
                        'descripcion' => 'IGTF por pagar'
                    ]);
                }
            }

            self::validarPartidaDoble($asiento);
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

            self::validarPartidaDoble($asiento);
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

            // DEBE: IGTF por Pagar (reversión)
            if (abs($notaCredito->igtf_monto ?? 0) > 0) {
                $cuentaIGTF = CuentaContable::where('codigo', '2.1.01.002')
                    ->where('empresa_id', $notaCredito->empresa_id)
                    ->first();

                if ($cuentaIGTF) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIGTF->id,
                        'debe' => abs($notaCredito->igtf_monto),
                        'haber' => 0,
                        'descripcion' => 'Reversión IGTF'
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

            self::validarPartidaDoble($asiento);
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

            // HABER: Ingresos (cargo adicional)
            $cuentaIngreso = CuentaContable::where('codigo', '4.1.01')
                ->where('empresa_id', $notaDebito->empresa_id)
                ->first();

            $subtotalBs = $notaDebito->subtotal_bs ?? ($notaDebito->subtotal * ($notaDebito->tasa_cambio_usd ?: 1));
            if ($cuentaIngreso) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaIngreso->id,
                    'debe' => 0,
                    'haber' => $subtotalBs,
                    'descripcion' => 'Ingreso adicional'
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

            // HABER: IGTF por Pagar
            if (($notaDebito->igtf_monto ?? 0) > 0) {
                $cuentaIGTF = CuentaContable::where('codigo', '2.1.01.002')
                    ->where('empresa_id', $notaDebito->empresa_id)
                    ->first();

                if ($cuentaIGTF) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaIGTF->id,
                        'debe' => 0,
                        'haber' => $notaDebito->igtf_monto,
                        'descripcion' => 'IGTF por pagar'
                    ]);
                }
            }

            self::validarPartidaDoble($asiento);
            return $asiento;
        });
    }

    private function getCodigoCuentaPago(?string $metodoPago): string
    {
        return match ($metodoPago) {
            'efectivo_bs', 'efectivo_usd' => '1.1.01.001',
            'transferencia_bs' => '1.1.01.002',
            'transferencia_usd' => '1.1.01.003',
            'pago_movil' => '1.1.01.004',
            'zelle', 'paypal' => '1.1.01.005',
            default => '1.1.01.001',
        };
    }

    /**
     * Cierre contable mensual: genera asiento de cierre que traslada saldos de
     * cuentas de ingreso/egreso/costo a Resultado del Ejercicio (3.2.02)
     */
    public function cierreMensual(int $empresaId, int $mes, int $anio, int $userId, ?int $sucursalId = null): ?AsientoContable
    {
        $desde = \Carbon\Carbon::create($anio, $mes, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();

        $existe = AsientoContable::where('empresa_id', $empresaId)
            ->where('tipo', 'cierre')
            ->where('descripcion', 'like', "Cierre mensual {$mes}/{$anio}%")
            ->where('estado', '!=', 'anulado')
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe un cierre contable para {$mes}/{$anio}");
        }

        return DB::transaction(function () use ($empresaId, $mes, $anio, $userId, $sucursalId, $desde, $hasta) {
            $cuentaResultado = CuentaContable::where('codigo', '3.2.02')
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$cuentaResultado) {
                throw new \Exception('No se encontró la cuenta Resultado del Ejercicio (3.2.02). Ejecute el seeder de plan de cuentas.');
            }

            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($empresaId),
                'fecha' => $hasta,
                'tipo' => 'cierre',
                'descripcion' => "Cierre mensual {$mes}/{$anio}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'cierre_mensual',
                'referencia_id' => null,
                'user_id' => $userId,
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
            ]);

            $totalIngresos = 0;
            $totalEgresos = 0;

            // Cerrar cuentas de ingreso (naturaleza acreedora → debitar para cerrar)
            $cuentasIngreso = CuentaContable::where('empresa_id', $empresaId)
                ->where('tipo', 'ingreso')
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->get();

            foreach ($cuentasIngreso as $cuenta) {
                $saldo = $this->getSaldoPeriodo($cuenta, $desde, $hasta);
                if (abs($saldo) > 0.01) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuenta->id,
                        'debe' => $saldo,
                        'haber' => 0,
                        'descripcion' => "Cierre {$cuenta->codigo} - {$cuenta->nombre}",
                    ]);
                    $totalIngresos += $saldo;
                }
            }

            // Cerrar cuentas de egreso y costo (naturaleza deudora → acreditar para cerrar)
            $cuentasGasto = CuentaContable::where('empresa_id', $empresaId)
                ->whereIn('tipo', ['egreso', 'costo'])
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->get();

            foreach ($cuentasGasto as $cuenta) {
                $saldo = $this->getSaldoPeriodo($cuenta, $desde, $hasta);
                if (abs($saldo) > 0.01) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuenta->id,
                        'debe' => 0,
                        'haber' => $saldo,
                        'descripcion' => "Cierre {$cuenta->codigo} - {$cuenta->nombre}",
                    ]);
                    $totalEgresos += $saldo;
                }
            }

            // Trasladar resultado neto a Resultado del Ejercicio
            $resultadoNeto = $totalIngresos - $totalEgresos;
            if (abs($resultadoNeto) > 0.01) {
                if ($resultadoNeto > 0) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultado->id,
                        'debe' => 0,
                        'haber' => $resultadoNeto,
                        'descripcion' => 'Resultado neto del período (utilidad)',
                    ]);
                } else {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultado->id,
                        'debe' => abs($resultadoNeto),
                        'haber' => 0,
                        'descripcion' => 'Resultado neto del período (pérdida)',
                    ]);
                }
            }

            self::validarPartidaDoble($asiento);
            return $asiento;
        });
    }

    /**
     * Cierre contable anual: cierra todos los meses pendientes y genera asiento de apertura del siguiente año
     */
    public function cierreAnual(int $empresaId, int $anio, int $userId, ?int $sucursalId = null): AsientoContable
    {
        $existe = AsientoContable::where('empresa_id', $empresaId)
            ->where('tipo', 'cierre')
            ->where('descripcion', 'like', "Cierre anual {$anio}%")
            ->where('estado', '!=', 'anulado')
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe un cierre contable anual para {$anio}");
        }

        return DB::transaction(function () use ($empresaId, $anio, $userId, $sucursalId) {
            $desde = \Carbon\Carbon::create($anio, 1, 1)->startOfYear();
            $hasta = \Carbon\Carbon::create($anio, 12, 31)->endOfYear();

            $cuentaResultado = CuentaContable::where('codigo', '3.2.02')
                ->where('empresa_id', $empresaId)
                ->first();

            $cuentaResultadoAcum = CuentaContable::where('codigo', '3.2.01')
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$cuentaResultado) {
                throw new \Exception('No se encontró la cuenta Resultado del Ejercicio (3.2.02).');
            }

            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($empresaId),
                'fecha' => $hasta,
                'tipo' => 'cierre',
                'descripcion' => "Cierre anual {$anio}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'cierre_anual',
                'referencia_id' => null,
                'user_id' => $userId,
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
            ]);

            $totalIngresos = 0;
            $totalEgresos = 0;

            // Cerrar cuentas de ingreso
            $cuentasIngreso = CuentaContable::where('empresa_id', $empresaId)
                ->where('tipo', 'ingreso')
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->get();

            foreach ($cuentasIngreso as $cuenta) {
                $saldo = $this->getSaldoPeriodo($cuenta, $desde, $hasta);
                if (abs($saldo) > 0.01) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuenta->id,
                        'debe' => $saldo,
                        'haber' => 0,
                        'descripcion' => "Cierre anual {$cuenta->codigo}",
                    ]);
                    $totalIngresos += $saldo;
                }
            }

            // Cerrar cuentas de egreso y costo
            $cuentasGasto = CuentaContable::where('empresa_id', $empresaId)
                ->whereIn('tipo', ['egreso', 'costo'])
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->get();

            foreach ($cuentasGasto as $cuenta) {
                $saldo = $this->getSaldoPeriodo($cuenta, $desde, $hasta);
                if (abs($saldo) > 0.01) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuenta->id,
                        'debe' => 0,
                        'haber' => $saldo,
                        'descripcion' => "Cierre anual {$cuenta->codigo}",
                    ]);
                    $totalEgresos += $saldo;
                }
            }

            // Trasladar resultado neto
            $resultadoNeto = $totalIngresos - $totalEgresos;
            if (abs($resultadoNeto) > 0.01) {
                if ($resultadoNeto > 0) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultado->id,
                        'debe' => 0,
                        'haber' => $resultadoNeto,
                        'descripcion' => "Resultado neto del ejercicio {$anio} (utilidad)",
                    ]);
                } else {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultado->id,
                        'debe' => abs($resultadoNeto),
                        'haber' => 0,
                        'descripcion' => "Resultado neto del ejercicio {$anio} (pérdida)",
                    ]);
                }
            }

            // Si hay cuenta de Resultados Acumulados, trasladar desde Resultado del Ejercicio
            if ($cuentaResultadoAcum) {
                $saldoResultado = $this->getSaldoAcumulado($cuentaResultado, $hasta);
                if (abs($saldoResultado) > 0.01) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultado->id,
                        'debe' => $saldoResultado > 0 ? $saldoResultado : 0,
                        'haber' => $saldoResultado < 0 ? abs($saldoResultado) : 0,
                        'descripcion' => "Traslado a resultados acumulados",
                    ]);
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaResultadoAcum->id,
                        'debe' => $saldoResultado < 0 ? abs($saldoResultado) : 0,
                        'haber' => $saldoResultado > 0 ? $saldoResultado : 0,
                        'descripcion' => "Resultados acumulados del ejercicio {$anio}",
                    ]);
                }
            }

            self::validarPartidaDoble($asiento);
            return $asiento;
        });
    }

    /**
     * Generar asiento de apertura para un nuevo ejercicio fiscal
     */
    public function generarAsientoApertura(int $empresaId, int $anio, int $userId, ?int $sucursalId = null): AsientoContable
    {
        $existe = AsientoContable::where('empresa_id', $empresaId)
            ->where('tipo', 'apertura')
            ->where('descripcion', 'like', "Asiento de apertura {$anio}%")
            ->where('estado', '!=', 'anulado')
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe un asiento de apertura para {$anio}");
        }

        return DB::transaction(function () use ($empresaId, $anio, $userId, $sucursalId) {
            $fechaApertura = \Carbon\Carbon::create($anio, 1, 1);
            $fechaCierreAnterior = \Carbon\Carbon::create($anio - 1, 12, 31);

            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($empresaId),
                'fecha' => $fechaApertura,
                'tipo' => 'apertura',
                'descripcion' => "Asiento de apertura {$anio}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'apertura',
                'referencia_id' => null,
                'user_id' => $userId,
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
            ]);

            // Traer saldos de cuentas de balance (activo, pasivo, patrimonio)
            $cuentasBalance = CuentaContable::where('empresa_id', $empresaId)
                ->whereIn('tipo', ['activo', 'pasivo', 'patrimonio'])
                ->where('acepta_movimientos', true)
                ->where('activo', true)
                ->get();

            foreach ($cuentasBalance as $cuenta) {
                $saldo = $this->getSaldoAcumulado($cuenta, $fechaCierreAnterior);
                if (abs($saldo) > 0.01) {
                    if ($cuenta->naturaleza === 'deudora') {
                        $asiento->detalles()->create([
                            'cuenta_id' => $cuenta->id,
                            'debe' => $saldo,
                            'haber' => 0,
                            'descripcion' => "Saldo inicial {$cuenta->codigo}",
                        ]);
                    } else {
                        $asiento->detalles()->create([
                            'cuenta_id' => $cuenta->id,
                            'debe' => 0,
                            'haber' => $saldo,
                            'descripcion' => "Saldo inicial {$cuenta->codigo}",
                        ]);
                    }
                }
            }

            self::validarPartidaDoble($asiento);
            return $asiento;
        });
    }

    private function getSaldoPeriodo(CuentaContable $cuenta, $desde, $hasta): float
    {
        $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->where('tipo', '!=', 'cierre')
                ->whereBetween('fecha', [$desde, $hasta]));

        $debe = (float) $query->sum('debe');
        $haber = (float) (clone $query)->sum('haber');

        return $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    private function getSaldoAcumulado(CuentaContable $cuenta, $hastaFecha): float
    {
        $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereDate('fecha', '<=', $hastaFecha));

        $debe = (float) $query->sum('debe');
        $haber = (float) (clone $query)->sum('haber');

        return $cuenta->naturaleza === 'deudora' ? ($debe - $haber) : ($haber - $debe);
    }

    /**
     * Validar que un asiento contable cumpla la partida doble (Debe = Haber).
     * Lanza excepción si no está balanceado, causando rollback de la transacción.
     */
    public static function validarPartidaDoble(AsientoContable $asiento): void
    {
        $asiento->load('detalles');
        $totalDebe = round((float) $asiento->detalles->sum('debe'), 2);
        $totalHaber = round((float) $asiento->detalles->sum('haber'), 2);

        if ($totalDebe !== $totalHaber) {
            throw new \Exception(
                "Asiento {$asiento->numero} desbalanceado: Debe={$totalDebe}, Haber={$totalHaber}. " .
                "Diferencia: " . round(abs($totalDebe - $totalHaber), 2)
            );
        }
    }
}
