<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;

class LibroVentasController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $documentos = Pago::where('empresa_id', $empresaId)
            ->where('es_factura_fiscal', true)
            ->whereIn('tipo_pago', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->with(['clienteFiscal', 'pagoOrigen', 'consulta.paciente', 'caja', 'user'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();

        $totales = [
            'base_imponible' => $documentos->sum(function ($doc) {
                return $doc->tipo_pago === 'nota_credito' ? -$doc->base_imponible : $doc->base_imponible;
            }),
            'iva_monto' => $documentos->sum(function ($doc) {
                return $doc->tipo_pago === 'nota_credito' ? -$doc->iva_monto : $doc->iva_monto;
            }),
            'monto_exento' => $documentos->sum(function ($doc) {
                return $doc->tipo_pago === 'nota_credito' ? -$doc->monto_exento : $doc->monto_exento;
            }),
            'total' => $documentos->sum(function ($doc) {
                return $doc->tipo_pago === 'nota_credito' ? -((float) $doc->total_con_impuestos) : ((float) $doc->total_con_impuestos);
            }),
            'igtf_monto' => $documentos->sum(function ($doc) {
                return $doc->tipo_pago === 'nota_credito' ? -$doc->igtf_monto : $doc->igtf_monto;
            }),
        ];

        return view('admin.seniat.libro-ventas', compact('documentos', 'totales', 'desde', 'hasta'));
    }

    public function exportTxt(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;
        $empresa = \App\Models\Empresa::find($empresaId);

        $documentos = Pago::where('empresa_id', $empresaId)
            ->where('es_factura_fiscal', true)
            ->whereIn('tipo_pago', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->with(['clienteFiscal', 'pagoOrigen'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();

        $lines = [];

        foreach ($documentos as $doc) {
            $rifContribuyente = str_replace('-', '', $empresa->rif_fiscal ?? $empresa->documento ?? '');
            $periodo = $doc->fecha->format('Ym');
            $fechaDoc = $doc->fecha->format('Y-m-d');

            // Tipo operación: V = Venta
            $tipoOperacion = 'V';

            // Tipo documento: 01=Factura, 02=ND, 03=NC
            $tipoDoc = $doc->seniat_tipo_documento ?? \App\Services\Seniat\FiscalCalculator::getSeniatTipoDocumento($doc->tipo_pago);

            // RIF comprador
            $rifComprador = '0';
            if ($doc->clienteFiscal) {
                $rifComprador = str_replace('-', '', $doc->clienteFiscal->tipo_documento . $doc->clienteFiscal->numero_documento);
            }

            $numDoc = $doc->numero_completo ?? '0';
            $numControl = $doc->numero_control_fiscal ?? '0';
            $montoDoc = format_money($doc->total_con_impuestos ?? $doc->total_usd, 2, '.', '');
            $baseImponible = format_money($doc->base_imponible ?? 0, 2, '.', '');
            $montoIva = format_money($doc->iva_monto ?? 0, 2, '.', '');

            // Documento afectado (para NC/ND)
            $docAfectado = '0';
            $controlAfectado = '0';
            $fechaAfectado = '0';
            if ($doc->pagoOrigen) {
                $docAfectado = $doc->pagoOrigen->numero_completo ?? '0';
                $controlAfectado = $doc->pagoOrigen->numero_control_fiscal ?? '0';
                $fechaAfectado = $doc->pagoOrigen->fecha ? $doc->pagoOrigen->fecha->format('Y-m-d') : '0';
            }

            $montoExento = format_money($doc->monto_exento ?? 0, 2, '.', '');
            $alicuota = format_money($doc->iva_porcentaje ?? 16, 2, '.', '');
            $igtfMonto = format_money($doc->igtf_monto ?? 0, 2, '.', '');

            // Format: RIF|Periodo|Fecha|TipoOp|TipoDoc|RifComprador|NumDoc|NumControl|MontoDoc|BaseImp|MontoIVA|DocAfectado|ControlAfectado|FechaAfectado|MontoExento|Alicuota|IGTF
            $lines[] = implode("\t", [
                $rifContribuyente,
                $periodo,
                $fechaDoc,
                $tipoOperacion,
                $tipoDoc,
                $rifComprador,
                $numDoc,
                $numControl,
                $montoDoc,
                $baseImponible,
                $montoIva,
                $docAfectado,
                $controlAfectado,
                $fechaAfectado,
                $montoExento,
                $alicuota,
                $igtfMonto,
            ]);
        }

        $content = implode("\n", $lines);
        $filename = 'libro_ventas_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.txt';

        return response($content, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
