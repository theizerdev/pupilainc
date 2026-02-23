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
            ->with(['clienteFiscal', 'pagoOrigen'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();

        $totales = [
            'base_imponible' => $documentos->where('tipo_pago', 'factura')->sum('base_imponible')
                - $documentos->where('tipo_pago', 'nota_credito')->sum('base_imponible')
                + $documentos->where('tipo_pago', 'nota_debito')->sum('base_imponible'),
            'iva_monto' => $documentos->where('tipo_pago', 'factura')->sum('iva_monto')
                - $documentos->where('tipo_pago', 'nota_credito')->sum('iva_monto')
                + $documentos->where('tipo_pago', 'nota_debito')->sum('iva_monto'),
            'monto_exento' => $documentos->where('tipo_pago', 'factura')->sum('monto_exento')
                - $documentos->where('tipo_pago', 'nota_credito')->sum('monto_exento')
                + $documentos->where('tipo_pago', 'nota_debito')->sum('monto_exento'),
            'total' => $documentos->where('tipo_pago', 'factura')->sum('total_con_impuestos')
                - $documentos->where('tipo_pago', 'nota_credito')->sum('total_con_impuestos')
                + $documentos->where('tipo_pago', 'nota_debito')->sum('total_con_impuestos'),
            'igtf_monto' => $documentos->sum('igtf_monto'),
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
            $montoDoc = number_format($doc->total_con_impuestos ?? $doc->total_usd, 2, '.', '');
            $baseImponible = number_format($doc->base_imponible ?? 0, 2, '.', '');
            $montoIva = number_format($doc->iva_monto ?? 0, 2, '.', '');

            // Documento afectado (para NC/ND)
            $docAfectado = '0';
            if ($doc->pagoOrigen) {
                $docAfectado = $doc->pagoOrigen->numero_completo ?? '0';
            }

            $montoExento = number_format($doc->monto_exento ?? 0, 2, '.', '');
            $alicuota = number_format($doc->iva_porcentaje ?? 16, 2, '.', '');

            // Format: RIF|Periodo|Fecha|TipoOp|TipoDoc|RifComprador|NumDoc|NumControl|MontoDoc|BaseImp|MontoIVA|DocAfectado|MontoExento|Alicuota
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
                $montoExento,
                $alicuota,
            ]);
        }

        $content = implode("\n", $lines);
        $filename = 'libro_ventas_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.txt';

        return response($content, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
