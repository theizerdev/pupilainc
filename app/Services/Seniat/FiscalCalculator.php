<?php

namespace App\Services\Seniat;

use App\Models\Pago;
use App\Models\ImpuestoConfiguracion;

class FiscalCalculator
{
    private const METODOS_DIVISA = ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal'];

    public static function calcular(Pago $pago): array
    {
        $detalles = $pago->detalles;

        // Si no hay detalles, usar el total del pago como base imponible
        if ($detalles->isEmpty()) {
            // Sin detalles no se puede determinar si aplica IVA, no calcular
            $subtotal = (float) $pago->total;

            // Calcular IGTF
            $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
                ->where('empresa_id', $pago->empresa_id)
                ->where('activo', true)
                ->first();

            $igtfPorcentaje = $igtfConfig ? (float) $igtfConfig->porcentaje : 0;
            $igtfMonto = 0;
            $aplicaIgtf = false;

            if ($igtfConfig) {
                $montoDivisas = self::calcularMontoDivisas($pago);
                if ($montoDivisas > 0) {
                    $aplicaIgtf = true;
                    $igtfMonto = $montoDivisas * ($igtfPorcentaje / 100);
                }
            }

            return [
                'subtotal'               => round($subtotal, 2),
                'base_imponible'         => round($pago->base_imponible ?? 0, 2),
                'base_imponible_general' => round($pago->base_imponible_general ?? 0, 2),
                'iva_monto_general'      => round($pago->iva_monto_general ?? 0, 2),
                'base_imponible_reducida'=> round($pago->base_imponible_reducida ?? 0, 2),
                'iva_monto_reducida'     => round($pago->iva_monto_reducida ?? 0, 2),
                'monto_exento'           => round($pago->monto_exento ?? 0, 2),
                'iva_porcentaje'         => $pago->iva_porcentaje ?? 0,
                'iva_monto'              => round($pago->iva_monto ?? 0, 2),
                'igtf_porcentaje'        => $igtfPorcentaje,
                'igtf_monto'             => round($igtfMonto, 2),
                'aplica_igtf'            => $aplicaIgtf,
                'total_con_impuestos'    => round($subtotal + ($pago->iva_monto ?? 0) + $igtfMonto, 2),
            ];
        }

        // Obtener configuración de IVA
        $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')
            ->where('empresa_id', $pago->empresa_id)
            ->where('activo', true)
            ->first();

        $ivaPorcentajeGeneral = $ivaConfig ? (float) $ivaConfig->porcentaje : 16;

        // Separar items por alícuota
        $baseImponibleGeneral = 0;
        $baseImponibleReducida = 0;
        $montoExento = 0;

        foreach ($detalles as $detalle) {
            $subtotal = (float) $detalle->subtotal;

            if ($detalle->exento_iva) {
                $montoExento += $subtotal;
                continue;
            }

            $alicuota = $detalle->iva_alicuota ?? 16;

            if ($alicuota == 8) {
                $baseImponibleReducida += $subtotal;
            } else {
                $baseImponibleGeneral += $subtotal;
            }
        }

        $ivaMontoGeneral = $baseImponibleGeneral * ($ivaPorcentajeGeneral / 100);
        $ivaMontoReducida = $baseImponibleReducida * (8 / 100);
        $ivaMonto = $ivaMontoGeneral + $ivaMontoReducida;

        $baseImponible = $baseImponibleGeneral + $baseImponibleReducida;
        $subtotal = $baseImponible + $montoExento;

        // Calcular IGTF
        $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
            ->where('empresa_id', $pago->empresa_id)
            ->where('activo', true)
            ->first();

        $igtfPorcentaje = $igtfConfig ? (float) $igtfConfig->porcentaje : 0;
        $igtfMonto = 0;
        $aplicaIgtf = false;

        if ($igtfConfig) {
            $montoDivisas = self::calcularMontoDivisas($pago);

            if ($montoDivisas > 0) {
                $aplicaIgtf = true;
                $igtfMonto = $montoDivisas * ($igtfPorcentaje / 100);
            }
        }

        $totalConImpuestos = $subtotal + $ivaMonto + $igtfMonto;

        return [
            'subtotal' => round($subtotal, 2),
            'base_imponible' => round($baseImponible, 2),
            'base_imponible_general' => round($baseImponibleGeneral, 2),
            'iva_monto_general' => round($ivaMontoGeneral, 2),
            'base_imponible_reducida' => round($baseImponibleReducida, 2),
            'iva_monto_reducida' => round($ivaMontoReducida, 2),
            'monto_exento' => round($montoExento, 2),
            'iva_porcentaje' => $ivaPorcentajeGeneral,
            'iva_monto' => round($ivaMonto, 2),
            'igtf_porcentaje' => $igtfPorcentaje,
            'igtf_monto' => round($igtfMonto, 2),
            'aplica_igtf' => $aplicaIgtf,
            'total_con_impuestos' => round($totalConImpuestos, 2),
        ];
    }

    public static function getSeniatTipoDocumento(string $tipoPago): string
    {
        return match ($tipoPago) {
            'factura' => '01',
            'nota_debito' => '02',
            'nota_credito' => '03',
            default => '01',
        };
    }

    private static function calcularMontoDivisas(Pago $pago): float
    {
        if ($pago->es_pago_mixto && $pago->detalles_pago_mixto) {
            $montoDivisas = 0;

            foreach ($pago->detalles_pago_mixto as $detalle) {
                if (in_array($detalle['metodo'], self::METODOS_DIVISA)) {
                    $montoDivisas += $detalle['monto_usd'] ?? 0;
                }
            }

            return $montoDivisas;
        }

        if (in_array($pago->metodo_pago, self::METODOS_DIVISA)) {
            return (float) $pago->total;
        }

        return 0;
    }

    public static function calcularIgtfDesdeDatos(int $empresaId, ?string $metodoPago, bool $esPagoMixto, ?array $detallesPagoMixto, float $totalUsd): array
    {
        $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();

        $porcentaje = $igtfConfig ? (float) $igtfConfig->porcentaje : 0;
        $baseUsd = 0.0;

        if ($esPagoMixto && $detallesPagoMixto) {
            foreach ($detallesPagoMixto as $detalle) {
                $met = $detalle['metodo'] ?? null;
                if (in_array($met, self::METODOS_DIVISA)) {
                    $baseUsd += (float) ($detalle['monto_usd'] ?? 0);
                }
            }
        } else {
            if (in_array($metodoPago, self::METODOS_DIVISA)) {
                $baseUsd = (float) $totalUsd;
            }
        }

        $aplica = $porcentaje > 0 && $baseUsd > 0;
        $monto = $aplica ? $baseUsd * ($porcentaje / 100) : 0.0;

        return [
            'igtf_porcentaje' => $porcentaje,
            'igtf_monto' => round($monto, 2),
            'aplica_igtf' => $aplica,
        ];
    }
}
