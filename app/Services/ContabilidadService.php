<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\AsientoDetalle;
use App\Models\CuentaContable;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class ContabilidadService
{
    private function getCodigoCuenta(string $clave): string
    {
        return config('contabilidad.cuentas.' . $clave);
    }

    private function getCuentaProducto(string $tipo): string
    {
        $map = [
            'venta_productos' => '4.1.03',
            'venta_medicamentos' => '4.1.04',
            'costo_productos' => '5.1.01.002',
            'costo_medicamentos' => '5.1.01.003',
            'inventario_productos' => '1.1.03.001',
            'inventario_medicamentos' => '1.1.03.002',
        ];

        return $map[$tipo] ?? '4.1.03';
    }

    public function generarAsientoFactura(Pago $pago)
    {
        if (!in_array($pago->tipo_pago, ['factura', 'boleta', 'recibo'])) {
            return null;
        }

        \Log::info('Generando asiento para pago', [
            'pago_id' => $pago->id,
            'total_bs' => $pago->total_bs,
            'subtotal_bs' => $pago->subtotal_bs,
            'subtotal' => $pago->subtotal,
            'tasa_cambio_usd' => $pago->tasa_cambio_usd,
            'metodo_pago' => $pago->metodo_pago
        ]);

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

            // DEBE: Cuenta según condición de pago
            $esCredito = strtolower($pago->condicion_pago ?? 'contado') === 'credito';

            $codigoCuenta = $esCredito ? $this->getCodigoCuenta('cxc') : $this->getCodigoCuentaPago($pago->metodo_pago);

            $cuentaPago = CuentaContable::where('codigo', $codigoCuenta)
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            $totalDebe = $pago->total_bs;
            if (($pago->igtf_monto ?? 0) > 0) {
                $totalDebe = ($pago->total_con_impuestos ?? $pago->total_bs) ?: $pago->total_bs;
            }

            \Log::info('Calculando montos del asiento', [
                'total_debe' => $totalDebe,
                'cuenta_pago_encontrada' => $cuentaPago ? $cuentaPago->codigo : 'NO ENCONTRADA',
                'codigo_cuenta_buscado' => $codigoCuenta
            ]);

            if ($cuentaPago) {
                $descripcionDebe = $esCredito
                    ? "Cuenta por cobrar factura {$pago->numero_completo}"
                    : "Cobro factura {$pago->numero_completo}";

                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaPago->id,
                    'debe' => $totalDebe,
                    'haber' => 0,
                    'descripcion' => $descripcionDebe
                ]);
            }

            // HABER: Ingresos por Consultas (excluyendo ventas de productos)
            $cuentaIngreso = CuentaContable::where('codigo', $this->getCodigoCuenta('ingreso'))
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            $subtotalTotal = $pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd);
            
            // Calcular subtotal de ventas de productos para restarlo del ingreso por consultas
            $subtotalVentasProductos = 0;
            if ($pago->ventasProductos()->exists()) {
                $subtotalVentasProductos = $pago->ventasProductos()->sum('subtotal');
            }
            
            // El ingreso por consulta es el total menos las ventas de productos
            $subtotalConsultas = $subtotalTotal - $subtotalVentasProductos;

            \Log::info('Calculando ingresos', [
                'subtotal_total' => $subtotalTotal,
                'subtotal_ventas_productos' => $subtotalVentasProductos,
                'subtotal_consultas' => $subtotalConsultas,
                'cuenta_ingreso_encontrada' => $cuentaIngreso ? $cuentaIngreso->codigo : 'NO ENCONTRADA'
            ]);

            if ($cuentaIngreso && $subtotalConsultas > 0) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaIngreso->id,
                    'debe' => 0,
                    'haber' => $subtotalConsultas,
                    'descripcion' => 'Ingreso por consulta médica'
                ]);
            }

            // HABER: IVA por Pagar
            if (($pago->iva_monto ?? 0) > 0) {
                $cuentaIVA = CuentaContable::where('codigo', $this->getCodigoCuenta('iva'))
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
                $cuentaIGTF = CuentaContable::where('codigo', $this->getCodigoCuenta('igtf'))
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

            // Generar asiento de honorarios médicos si es una consulta
            if ($pago->consulta_id) {
                $this->generarAsientoHonorarios($asiento, $pago);
            }

            // Generar asientos de ventas de productos si existen
            if ($pago->ventasProductos()->exists()) {
                $this->generarAsientosVentasProductos($asiento, $pago);
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
            $cuentaCaja = CuentaContable::where('codigo', $this->getCodigoCuenta('caja'))
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
            $cuentaPorCobrar = CuentaContable::where('codigo', $this->getCodigoCuenta('cxc'))
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
            $cuentaIVA = CuentaContable::where('codigo', $this->getCodigoCuenta('iva'))
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
            $cuentaIGTF = CuentaContable::where('codigo', $this->getCodigoCuenta('igtf'))
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
            $cuentaPorCobrar = CuentaContable::where('codigo', $this->getCodigoCuenta('cxc'))
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
            $cuentaPorCobrar = CuentaContable::where('codigo', $this->getCodigoCuenta('cxc'))
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
            $cuentaIngreso = CuentaContable::where('codigo', $this->getCodigoCuenta('ingreso'))
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
            $cuentaIVA = CuentaContable::where('codigo', $this->getCodigoCuenta('iva'))
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
            $cuentaIGTF = CuentaContable::where('codigo', $this->getCodigoCuenta('igtf'))
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
        $map = config('contabilidad.metodos_pago');
        return $map[$metodoPago] ?? $this->getCodigoCuenta('caja');
    }

    /**
     * Generar asientos de honorarios médicos
     */
    private function generarAsientoHonorarios(AsientoContable $asiento, Pago $pago): void
    {
        if (!$pago->consulta_id) {
            return;
        }

        // Buscar si ya se calcularon los honorarios
        $honorarios = \App\Models\ConsultaHonorario::where('pago_id', $pago->id)->first();

        if (!$honorarios || $honorarios->total_honorarios_medico_bs <= 0) {
            return;
        }

        // DEBE: Gastos de Honorarios Médicos
        $cuentaHonorarios = CuentaContable::where('codigo', '5.1.01.001')
            ->where('empresa_id', $pago->empresa_id)
            ->first();

        if ($cuentaHonorarios) {
            $asiento->detalles()->create([
                'cuenta_id' => $cuentaHonorarios->id,
                'debe' => $honorarios->total_honorarios_medico_bs,
                'haber' => 0,
                'descripcion' => "Honorarios médicos - {$pago->consulta->medico->nombre_completo}"
            ]);
        }

        // HABER: Cuentas por Pagar - Médicos
        $cuentaPorPagarMedicos = CuentaContable::where('codigo', '2.1.02.001')
            ->where('empresa_id', $pago->empresa_id)
            ->first();

        if ($cuentaPorPagarMedicos) {
            $asiento->detalles()->create([
                'cuenta_id' => $cuentaPorPagarMedicos->id,
                'debe' => 0,
                'haber' => $honorarios->total_honorarios_medico_bs,
                'descripcion' => "Cuenta por pagar - Dr. {$pago->consulta->medico->nombre_completo}"
            ]);
        }
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
            $cuentaResultado = CuentaContable::where('codigo', $this->getCodigoCuenta('resultado_ejercicio'))
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

            $cuentaResultado = CuentaContable::where('codigo', $this->getCodigoCuenta('resultado_ejercicio'))
                ->where('empresa_id', $empresaId)
                ->first();

            $cuentaResultadoAcum = CuentaContable::where('codigo', $this->getCodigoCuenta('resultado_acumulado'))
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
     * Generar asientos de ventas de productos
     */
    private function generarAsientosVentasProductos(AsientoContable $asiento, Pago $pago): void
    {
        $ventasProductos = $pago->ventasProductos()->with('producto')->get();

        foreach ($ventasProductos as $venta) {
            $producto = $venta->producto;

            // Determinar cuentas según tipo de producto
            $cuentaVenta = $producto->es_medicamento ?
                $this->getCuentaProducto('venta_medicamentos') :
                $this->getCuentaProducto('venta_productos');

            $cuentaCosto = $producto->es_medicamento ?
                $this->getCuentaProducto('costo_medicamentos') :
                $this->getCuentaProducto('costo_productos');

            $cuentaInventario = $producto->es_medicamento ?
                $this->getCuentaProducto('inventario_medicamentos') :
                $this->getCuentaProducto('inventario_productos');

            // HABER: Ventas de Productos
            $cuentaVentaObj = CuentaContable::where('codigo', $cuentaVenta)
                ->where('empresa_id', $pago->empresa_id)
                ->first();

            if ($cuentaVentaObj) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaVentaObj->id,
                    'debe' => 0,
                    'haber' => $venta->subtotal,
                    'descripcion' => "Venta de {$producto->nombre} (Cant: {$venta->cantidad})"
                ]);
            }

            // Asiento de costo de ventas si hay costo unitario
            if ($venta->costo_total && $venta->costo_total > 0) {
                // DEBE: Costo de Ventas
                $cuentaCostoObj = CuentaContable::where('codigo', $cuentaCosto)
                    ->where('empresa_id', $pago->empresa_id)
                    ->first();

                if ($cuentaCostoObj) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaCostoObj->id,
                        'debe' => $venta->costo_total,
                        'haber' => 0,
                        'descripcion' => "Costo de venta - {$producto->nombre}"
                    ]);
                }

                // HABER: Inventario
                $cuentaInventarioObj = CuentaContable::where('codigo', $cuentaInventario)
                    ->where('empresa_id', $pago->empresa_id)
                    ->first();

                if ($cuentaInventarioObj) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaInventarioObj->id,
                        'debe' => 0,
                        'haber' => $venta->costo_total,
                        'descripcion' => "Salida de inventario - {$producto->nombre}"
                    ]);
                }
            }
        }
    }

    /**
     * Generar asiento contable para egreso de caja
     */
    public function generarAsientoEgresoCaja(\App\Models\GastoCaja $gasto)
    {
        return DB::transaction(function () use ($gasto) {
            $asiento = AsientoContable::create([
                'numero' => AsientoContable::generarNumero($gasto->empresa_id),
                'fecha' => $gasto->fecha_gasto,
                'tipo' => 'diario',
                'descripcion' => "Egreso de caja - {$gasto->concepto}",
                'estado' => 'aprobado',
                'referencia_tipo' => 'egreso_caja',
                'referencia_id' => $gasto->id,
                'user_id' => $gasto->user_id,
                'empresa_id' => $gasto->empresa_id,
                'sucursal_id' => $gasto->sucursal_id
            ]);

            // DEBE: Cuenta de gasto según categoría
            $cuentaGasto = $this->obtenerCuentaGastoPorCategoria($gasto->categoria, $gasto->empresa_id);

            if ($cuentaGasto) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaGasto->id,
                    'debe' => $gasto->monto_bs,
                    'haber' => 0,
                    'descripcion' => "{$gasto->concepto} - {$gasto->metodo_pago}"
                ]);
            } else {
                // Si no hay cuenta específica, usar cuenta genérica de gastos
                $cuentaGenerica = CuentaContable::where('codigo', $this->getCodigoCuenta('gastos_operativos'))
                    ->where('empresa_id', $gasto->empresa_id)
                    ->first();

                if ($cuentaGenerica) {
                    $asiento->detalles()->create([
                        'cuenta_id' => $cuentaGenerica->id,
                        'debe' => $gasto->monto_bs,
                        'haber' => 0,
                        'descripcion' => "{$gasto->concepto} - {$gasto->metodo_pago} (sin categoría específica)"
                    ]);
                }
            }

            // HABER: Caja
            $cuentaCaja = CuentaContable::where('codigo', $this->getCodigoCuenta('caja'))
                ->where('empresa_id', $gasto->empresa_id)
                ->first();

            if ($cuentaCaja) {
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaCaja->id,
                    'debe' => 0,
                    'haber' => $gasto->monto_bs,
                    'descripcion' => "Salida de caja - {$gasto->metodo_pago}"
                ]);
            }

            // Validar partida doble
            self::validarPartidaDoble($asiento);

            return $asiento;
        });
    }

    /**
     * Obtener cuenta contable de gasto según categoría
     */
    private function obtenerCuentaGastoPorCategoria(?string $categoria, int $empresaId): ?CuentaContable
    {
        // Mapeo de categorías a cuentas contables
        $mapeoCategorias = [
            'combustible' => '5.1.04.001',      // Gastos de combustible
            'materiales' => '5.1.05.001',       // Materiales y suministros
            'servicios' => '5.1.06.001',        // Servicios públicos
            'mantenimiento' => '5.1.07.001',    // Mantenimiento y reparaciones
            'transporte' => '5.1.08.001',       // Gastos de transporte
            'alimentacion' => '5.1.09.001',     // Alimentación
            'papeleria' => '5.1.10.001',        // Papelería y útiles
            'otros' => '5.1.99.001',            // Otros gastos
        ];

        $codigoCuenta = $mapeoCategorias[strtolower($categoria ?? '')] ?? null;

        if (!$codigoCuenta) {
            return null;
        }

        return CuentaContable::where('codigo', $codigoCuenta)
            ->where('empresa_id', $empresaId)
            ->first();
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
