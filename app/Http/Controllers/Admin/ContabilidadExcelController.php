<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaContable;
use App\Models\AsientoContable;
use App\Models\AsientoDetalle;
use App\Models\Empresa;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ContabilidadExcelController extends Controller
{
    private function setupHeader(Spreadsheet $spreadsheet, string $titulo, ?string $periodo = null): int
    {
        $sheet = $spreadsheet->getActiveSheet();
        $empresa = Empresa::find(auth()->user()->empresa_id);

        $sheet->setCellValue('A1', $empresa->razon_social ?? 'Empresa');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        if ($empresa->rif_fiscal) {
            $sheet->setCellValue('A2', 'RIF: ' . $empresa->rif_fiscal);
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row = 3;
        }

        $sheet->setCellValue('A' . $row, $titulo);
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        if ($periodo) {
            $sheet->setCellValue('A' . $row, $periodo);
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        return $row + 1;
    }

    private function styleHeaderRow($sheet, string $range)
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('333333');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function styleTotalsRow($sheet, string $range)
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // ================================================
    // LIBRO DIARIO
    // ================================================
    public function libroDiario()
    {
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Libro Diario');

        $row = $this->setupHeader($spreadsheet, 'LIBRO DIARIO', "Desde: {$desde}  Hasta: {$hasta}");

        $asientos = AsientoContable::with(['detalles.cuenta', 'user'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->orderBy('numero')
            ->get();

        $grandTotalDebe = 0;
        $grandTotalHaber = 0;

        foreach ($asientos as $asiento) {
            // Asiento header row
            $sheet->setCellValue('A' . $row, $asiento->numero);
            $sheet->setCellValue('B' . $row, $asiento->fecha->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, ucfirst($asiento->tipo));
            $sheet->setCellValue('D' . $row, $asiento->descripcion);
            $sheet->mergeCells('D' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8EDF5');
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;

            // Detail header
            $sheet->setCellValue('A' . $row, 'Código');
            $sheet->setCellValue('B' . $row, 'Cuenta');
            $sheet->setCellValue('C' . $row, 'Descripción');
            $sheet->mergeCells('C' . $row . ':D' . $row);
            $sheet->setCellValue('E' . $row, 'Debe');
            $sheet->setCellValue('F' . $row, 'Haber');
            $this->styleHeaderRow($sheet, 'A' . $row . ':F' . $row);
            $row++;

            $asientoDebe = 0;
            $asientoHaber = 0;

            foreach ($asiento->detalles as $det) {
                $d = (float) $det->debe;
                $h = (float) $det->haber;
                $asientoDebe += $d;
                $asientoHaber += $h;

                $sheet->setCellValue('A' . $row, $det->cuenta->codigo ?? '');
                $sheet->setCellValue('B' . $row, $det->cuenta->nombre ?? '');
                $sheet->setCellValue('C' . $row, $det->descripcion ?? '');
                $sheet->mergeCells('C' . $row . ':D' . $row);
                $sheet->setCellValue('E' . $row, $d > 0 ? $d : '');
                $sheet->setCellValue('F' . $row, $h > 0 ? $h : '');
                $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }

            // Asiento totals
            $sheet->setCellValue('D' . $row, 'Totales asiento:');
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('E' . $row, $asientoDebe);
            $sheet->setCellValue('F' . $row, $asientoHaber);
            $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $this->styleTotalsRow($sheet, 'A' . $row . ':F' . $row);
            $row++;

            $grandTotalDebe += $asientoDebe;
            $grandTotalHaber += $asientoHaber;
            $row++; // blank separator
        }

        // Grand totals
        $sheet->setCellValue('D' . $row, 'TOTALES GENERALES (' . $asientos->count() . ' asientos):');
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $grandTotalDebe);
        $sheet->setCellValue('F' . $row, $grandTotalHaber);
        $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('333333');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Generado el: ' . now()->format('d/m/Y H:i:s'));

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);

        $writer = new Xlsx($spreadsheet);
        $filename = 'libro_diario_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ================================================
    // LIBRO MAYOR
    // ================================================
    public function libroMayor()
    {
        $cuentaId = request('cuenta_id');
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));

        if (!$cuentaId) {
            abort(400, 'Debe seleccionar una cuenta');
        }

        $cuenta = CuentaContable::findOrFail($cuentaId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Libro Mayor');

        $row = $this->setupHeader($spreadsheet, 'LIBRO MAYOR', "Cuenta: {$cuenta->codigo} - {$cuenta->nombre}  |  Desde: {$desde}  Hasta: {$hasta}");

        // Saldo anterior
        $queryAnterior = AsientoDetalle::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')->whereDate('fecha', '<', $desde));
        $di = (float) $queryAnterior->sum('debe');
        $hi = (float) (clone $queryAnterior)->sum('haber');
        $saldoAnterior = $cuenta->naturaleza === 'deudora' ? ($di - $hi) : ($hi - $di);
        $saldo = $saldoAnterior;

        // Header row
        $headers = ['Fecha', 'Asiento', 'Descripción', 'Debe', 'Haber', 'Saldo'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . $row, $h);
        }
        $this->styleHeaderRow($sheet, 'A' . $row . ':F' . $row);
        $row++;

        // Saldo anterior row
        $sheet->setCellValue('A' . $row, 'Saldo Anterior');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('F' . $row, $saldoAnterior);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Movimientos
        $movimientos = AsientoDetalle::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->with('asiento')
            ->get()
            ->sortBy('asiento.fecha');

        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($movimientos as $det) {
            $d = (float) $det->debe;
            $h = (float) $det->haber;
            $totalDebe += $d;
            $totalHaber += $h;

            if ($cuenta->naturaleza === 'deudora') {
                $saldo += ($d - $h);
            } else {
                $saldo += ($h - $d);
            }

            $sheet->setCellValue('A' . $row, $det->asiento->fecha->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $det->asiento->numero);
            $sheet->setCellValue('C' . $row, $det->descripcion ?: $det->asiento->descripcion);
            $sheet->setCellValue('D' . $row, $d > 0 ? $d : '');
            $sheet->setCellValue('E' . $row, $h > 0 ? $h : '');
            $sheet->setCellValue('F' . $row, $saldo);
            $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Totals
        $sheet->setCellValue('C' . $row, 'TOTALES PERÍODO');
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $totalDebe);
        $sheet->setCellValue('E' . $row, $totalHaber);
        $sheet->setCellValue('F' . $row, $saldo);
        $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->styleTotalsRow($sheet, 'A' . $row . ':F' . $row);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Generado el: ' . now()->format('d/m/Y H:i:s'));

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);

        $writer = new Xlsx($spreadsheet);
        $filename = 'libro_mayor_' . $cuenta->codigo . '_' . str_replace('-', '', $desde) . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ================================================
    // BALANCE DE COMPROBACIÓN
    // ================================================
    public function balanceComprobacion()
    {
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Balance Comprobación');

        $row = $this->setupHeader($spreadsheet, 'BALANCE DE COMPROBACIÓN', "Desde: {$desde}  Hasta: {$hasta}");

        // Headers
        $sheet->setCellValue('A' . $row, 'Código');
        $sheet->setCellValue('B' . $row, 'Cuenta');
        $sheet->setCellValue('C' . $row, 'Debe');
        $sheet->setCellValue('D' . $row, 'Haber');
        $sheet->setCellValue('E' . $row, 'Saldo Deudor');
        $sheet->setCellValue('F' . $row, 'Saldo Acreedor');
        $this->styleHeaderRow($sheet, 'A' . $row . ':F' . $row);
        $row++;

        $cuentas = CuentaContable::where('empresa_id', $empresaId)
            ->where('acepta_movimientos', true)->where('activo', true)
            ->orderBy('codigo')->get();

        $totDebe = $totHaber = $totSD = $totSA = 0;

        foreach ($cuentas as $cuenta) {
            $detalles = AsientoDetalle::where('cuenta_id', $cuenta->id)
                ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                    ->whereBetween('fecha', [$desde, $hasta]));
            $debe = (float) $detalles->sum('debe');
            $haber = (float) (clone $detalles)->sum('haber');

            if ($debe == 0 && $haber == 0) continue;

            $saldoDeudor = $saldoAcreedor = 0;
            if ($cuenta->naturaleza === 'deudora') {
                $s = $debe - $haber;
                $s >= 0 ? $saldoDeudor = $s : $saldoAcreedor = abs($s);
            } else {
                $s = $haber - $debe;
                $s >= 0 ? $saldoAcreedor = $s : $saldoDeudor = abs($s);
            }

            $totDebe += $debe;
            $totHaber += $haber;
            $totSD += $saldoDeudor;
            $totSA += $saldoAcreedor;

            $sheet->setCellValue('A' . $row, $cuenta->codigo);
            $sheet->setCellValue('B' . $row, $cuenta->nombre);
            $sheet->setCellValue('C' . $row, $debe);
            $sheet->setCellValue('D' . $row, $haber);
            $sheet->setCellValue('E' . $row, $saldoDeudor > 0 ? $saldoDeudor : '');
            $sheet->setCellValue('F' . $row, $saldoAcreedor > 0 ? $saldoAcreedor : '');
            $sheet->getStyle('C' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Totals
        $sheet->setCellValue('B' . $row, 'TOTALES');
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('C' . $row, $totDebe);
        $sheet->setCellValue('D' . $row, $totHaber);
        $sheet->setCellValue('E' . $row, $totSD);
        $sheet->setCellValue('F' . $row, $totSA);
        $sheet->getStyle('C' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->styleTotalsRow($sheet, 'A' . $row . ':F' . $row);

        $row += 2;
        $cuadrado = round($totSD, 2) === round($totSA, 2) ? '✓ Balance Cuadrado' : '✗ Descuadre: ' . number_format(abs($totSD - $totSA), 2, ',', '.');
        $sheet->setCellValue('A' . $row, $cuadrado);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A' . $row, 'Generado el: ' . now()->format('d/m/Y H:i:s'));

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);

        $writer = new Xlsx($spreadsheet);
        $filename = 'balance_comprobacion_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ================================================
    // LIBRO DE VENTAS
    // ================================================
    public function libroVentas()
    {
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Libro de Ventas');

        $row = $this->setupHeader($spreadsheet, 'LIBRO DE VENTAS - SENIAT', "Desde: {$desde}  Hasta: {$hasta}");

        // Headers
        $headers = ['#', 'Fecha', 'Tipo Doc.', 'N° Documento', 'N° Control', 'RIF Cliente', 'Razón Social', 'Base Imponible', 'Monto Exento', 'IVA', 'IGTF', 'Total'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . $row, $h);
        }
        $this->styleHeaderRow($sheet, 'A' . $row . ':L' . $row);
        $row++;

        $documentos = \App\Models\Pago::where('empresa_id', $empresaId)
            ->where('es_factura_fiscal', true)
            ->whereIn('tipo_pago', ['factura', 'nota_credito', 'nota_debito'])
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->with(['clienteFiscal', 'pagoOrigen'])
            ->orderBy('fecha')
            ->orderBy('numero_control_fiscal')
            ->get();

        $totBaseImp = $totExento = $totIVA = $totIGTF = $totTotal = 0;

        foreach ($documentos as $index => $doc) {
            $tipoDoc = match($doc->tipo_pago) {
                'factura' => '01 - Factura',
                'nota_debito' => '02 - N. Débito',
                'nota_credito' => '03 - N. Crédito',
                default => $doc->tipo_pago,
            };

            $baseImp = (float) ($doc->base_imponible ?? 0);
            $exento = (float) ($doc->monto_exento ?? 0);
            $iva = (float) ($doc->iva_monto ?? 0);
            $igtf = (float) ($doc->igtf_monto ?? 0);
            $total = (float) ($doc->total_con_impuestos ?? 0);

            $totBaseImp += $baseImp;
            $totExento += $exento;
            $totIVA += $iva;
            $totIGTF += $igtf;
            $totTotal += $total;

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $doc->fecha->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $tipoDoc);
            $sheet->setCellValue('D' . $row, $doc->numero_completo);
            $sheet->setCellValue('E' . $row, $doc->numero_control_fiscal ?? '-');
            $sheet->setCellValue('F' . $row, $doc->clienteFiscal->documento_completo ?? '-');
            $sheet->setCellValue('G' . $row, $doc->clienteFiscal->razon_social ?? '-');
            $sheet->setCellValue('H' . $row, $baseImp);
            $sheet->setCellValue('I' . $row, $exento);
            $sheet->setCellValue('J' . $row, $iva);
            $sheet->setCellValue('K' . $row, $igtf);
            $sheet->setCellValue('L' . $row, $total);
            $sheet->getStyle('H' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':L' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Totals
        $sheet->setCellValue('G' . $row, 'TOTALES:');
        $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('H' . $row, $totBaseImp);
        $sheet->setCellValue('I' . $row, $totExento);
        $sheet->setCellValue('J' . $row, $totIVA);
        $sheet->setCellValue('K' . $row, $totIGTF);
        $sheet->setCellValue('L' . $row, $totTotal);
        $sheet->getStyle('H' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->styleTotalsRow($sheet, 'A' . $row . ':L' . $row);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Generado el: ' . now()->format('d/m/Y H:i:s'));

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(14);
        $sheet->getColumnDimension('K')->setWidth(14);
        $sheet->getColumnDimension('L')->setWidth(16);

        $writer = new Xlsx($spreadsheet);
        $filename = 'libro_ventas_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
