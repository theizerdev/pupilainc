<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\ExchangeRate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CajaExportController extends Controller
{
    public function export(Caja $caja)
    {
        $caja->load(['usuario', 'sucursal', 'pagos.consulta.paciente', 'pagos.clienteFiscal', 'pagos.detalles.baremo']);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configurar encabezado
        $sheet->setCellValue('A1', 'REPORTE DETALLADO DE CAJA');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Información de la caja
        $row = 3;
        $sheet->setCellValue('A' . $row, 'Fecha:');
        $sheet->setCellValue('B' . $row, $caja->fecha->format('d/m/Y'));
        $sheet->setCellValue('D' . $row, 'Usuario:');
        $sheet->setCellValue('E' . $row, $caja->usuario->name);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Sucursal:');
        $sheet->setCellValue('B' . $row, $caja->sucursal->nombre);
        $sheet->setCellValue('D' . $row, 'Estado:');
        $sheet->setCellValue('E' . $row, ucfirst($caja->estado));
        
        // Tasa de cambio
        $tasaCambio = ExchangeRate::whereDate('created_at', $caja->fecha)->first();
        if ($tasaCambio) {
            $row++;
            $sheet->setCellValue('A' . $row, 'Tasa de Cambio:');
            $sheet->setCellValue('B' . $row, number_format($tasaCambio->usd_rate, 4) . ' Bs/$');
        }
        
        // Resumen financiero
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RESUMEN FINANCIERO');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $datos = [
            ['Concepto', 'Monto USD', 'Monto Bs'],
            ['Monto Inicial', number_format($caja->monto_inicial, 2), $tasaCambio ? number_format($caja->monto_inicial * $tasaCambio->usd_rate, 2) : '-'],
            ['Total Efectivo', number_format($caja->total_efectivo, 2), $tasaCambio ? number_format($caja->total_efectivo * $tasaCambio->usd_rate, 2) : '-'],
            ['Total Transferencias', number_format($caja->total_transferencias, 2), $tasaCambio ? number_format($caja->total_transferencias * $tasaCambio->usd_rate, 2) : '-'],
            ['Total Ingresos', number_format($caja->total_ingresos, 2), $tasaCambio ? number_format($caja->total_ingresos * $tasaCambio->usd_rate, 2) : '-'],
            ['Monto Final', number_format($caja->monto_final, 2), $tasaCambio ? number_format($caja->monto_final * $tasaCambio->usd_rate, 2) : '-']
        ];
        
        foreach ($datos as $fila) {
            $sheet->setCellValue('A' . $row, $fila[0]);
            $sheet->setCellValue('B' . $row, $fila[1]);
            $sheet->setCellValue('C' . $row, $fila[2]);
            $row++;
        }
        
        // Detalle de pagos
        $row += 2;
        $sheet->setCellValue('A' . $row, 'DETALLE DE PAGOS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $encabezados = ['N° Factura', 'Control Fiscal', 'Paciente', 'Método', 'Monto USD', 'Monto Bs', 'Referencia', 'Hora'];
        foreach ($encabezados as $col => $encabezado) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $encabezado);
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        
        $row++;
        foreach ($caja->pagos()->where('estado', 'aprobado')->get() as $pago) {
            $paciente = $pago->consulta && $pago->consulta->paciente 
                ? $pago->consulta->paciente->nombre_completo 
                : ($pago->clienteFiscal ? $pago->clienteFiscal->razon_social : 'N/A');
            
            if ($pago->es_pago_mixto && $pago->detalles_pago_mixto) {
                foreach ($pago->detalles_pago_mixto as $detalle) {
                    $montoBolivares = isset($detalle['monto_bs']) 
                        ? number_format($detalle['monto_bs'], 2) 
                        : '-';
                    
                    $sheet->setCellValue('A' . $row, $pago->numero_completo);
                    $sheet->setCellValue('B' . $row, $pago->numero_control_fiscal ?? '-');
                    $sheet->setCellValue('C' . $row, $paciente);
                    $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $detalle['metodo'])));
                    $sheet->setCellValue('E' . $row, number_format($detalle['monto_usd'] ?? $detalle['monto'] ?? 0, 2));
                    $sheet->setCellValue('F' . $row, $montoBolivares);
                    $sheet->setCellValue('G' . $row, $detalle['referencia'] ?? '-');
                    $sheet->setCellValue('H' . $row, $pago->created_at->format('H:i'));
                    $row++;
                }
            } else {
                $montoBolivares = $pago->total_bs ? number_format($pago->total_bs, 2) : '-';
                
                $sheet->setCellValue('A' . $row, $pago->numero_completo);
                $sheet->setCellValue('B' . $row, $pago->numero_control_fiscal ?? '-');
                $sheet->setCellValue('C' . $row, $paciente);
                $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $pago->metodo_pago)));
                $sheet->setCellValue('E' . $row, number_format($pago->total_usd ?? $pago->total, 2));
                $sheet->setCellValue('F' . $row, $montoBolivares);
                $sheet->setCellValue('G' . $row, $pago->referencia ?? '-');
                $sheet->setCellValue('H' . $row, $pago->created_at->format('H:i'));
                $row++;
            }
        }
        
        // Aplicar estilos
        $sheet->getStyle('A1:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(10);
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'caja_' . $caja->fecha->format('Y-m-d') . '_' . $caja->numero_corte . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
}