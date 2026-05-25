<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\ExchangeRate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CajaExportController extends Controller
{
    public function export(Caja $caja)
    {
        $caja->load([
            'usuario',
            'sucursal',
            'empresa.pais',
            'pagos.consulta.paciente',
            'pagos.clienteFiscal',
            'pagos.detalles.baremo',
            'pagos.ventasProductos.producto'
        ]);

        $spreadsheet = new Spreadsheet();

        // Get company info
        $empresa = $caja->empresa;
        $pais = $empresa->pais;
        $esVenezuela = strtolower($pais->nombre) === 'venezuela';
        $codigoMoneda = $esVenezuela ? 'Bs' : $pais->codigo_moneda;
        $tasaCambio = $empresa->tasa_cambio ?? 1;

        // Calculate KPIs
        $pagosAprobados = $caja->pagos()->where('estado', 'aprobado')->get();
        $totalTransacciones = $pagosAprobados->count();
        $granTotalUSD = $pagosAprobados->sum('total_usd');
        $granTotalBS = $pagosAprobados->sum('total_bs');
        $ticketPromedio = $totalTransacciones > 0 ? $granTotalUSD / $totalTransacciones : 0;

        // Group by payment method categories
        $categorias = $this->agruparPorCategoria($pagosAprobados);

        // Find dominant payment method
        $metodoDominante = $pagosAprobados->groupBy('metodo_pago')
            ->map(fn($group) => $group->sum('total_usd'))
            ->sortDesc()
            ->first();
        $metodoDominanteNombre = $this->getNombreAmigableMetodo(
            $pagosAprobados->groupBy('metodo_pago')
                ->map(fn($group) => $group->sum('total_usd'))
                ->sortDesc()
                ->keys()
                ->first() ?? ''
        );

        // =====================================================
        // SHEET 1: EXECUTIVE SUMMARY
        // =====================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Resumen Ejecutivo');

        // Title
        $sheet1->setCellValue('A1', 'REPORTE CONGLOMERADO DE CAJA');
        $sheet1->mergeCells('A1:D1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2E7D32');
        $sheet1->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // General Information
        $row = 3;
        $infoData = [
            ['Fecha:', $caja->fecha->format('d/m/Y'), 'Caja N°:', $caja->numero_corte],
            ['Sucursal:', $caja->sucursal->nombre, 'Estado:', ucfirst($caja->estado)],
            ['Usuario:', $caja->usuario->name, 'Tasa de Cambio:', $tasaCambio . ' ' . $codigoMoneda . '/$'],
        ];

        foreach ($infoData as $data) {
            $sheet1->setCellValue('A' . $row, $data[0]);
            $sheet1->setCellValue('B' . $row, $data[1]);
            $sheet1->setCellValue('C' . $row, $data[2]);
            $sheet1->setCellValue('D' . $row, $data[3]);
            $sheet1->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
            $row++;
        }

        // KPIs Section
        $row += 2;
        $sheet1->setCellValue('A' . $row, 'INDICADORES CLAVE (KPIs)');
        $sheet1->mergeCells('A' . $row . ':D' . $row);
        $sheet1->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1976D2');
        $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;

        $kpis = [
            ['Total Ingresos USD:', '$ ' . number_format($granTotalUSD, 2), 'Total Ingresos ' . $codigoMoneda . ':', $codigoMoneda . ' ' . number_format($granTotalBS, 2)],
            ['Total Transacciones:', $totalTransacciones, 'Ticket Promedio:', '$ ' . number_format($ticketPromedio, 2)],
            ['Método Dominante:', $metodoDominanteNombre, 'Monto Método:', '$ ' . number_format($metodoDominante, 2)],
            ['Monto Inicial:', '$ ' . number_format($caja->monto_inicial, 2), 'Monto Final:', '$ ' . number_format($caja->monto_final, 2)],
        ];

        foreach ($kpis as $kpi) {
            $sheet1->setCellValue('A' . $row, $kpi[0]);
            $sheet1->setCellValue('B' . $row, $kpi[1]);
            $sheet1->setCellValue('C' . $row, $kpi[2]);
            $sheet1->setCellValue('D' . $row, $kpi[3]);
            $sheet1->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Summary by Category
        $row += 2;
        $sheet1->setCellValue('A' . $row, 'RESUMEN POR CATEGORÍA DE PAGO');
        $sheet1->mergeCells('A' . $row . ':E' . $row);
        $sheet1->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF388E3C');
        $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;
        $catHeaders = ['Categoría', 'Transacciones', 'Total USD', 'Total ' . $codigoMoneda, '% del Total'];
        foreach ($catHeaders as $col => $header) {
            $sheet1->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet1->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC8E6C9');

        $row++;

        foreach ($categorias as $categoria => $datos) {
            $porcentaje = $granTotalUSD > 0 ? ($datos['total_usd'] / $granTotalUSD) * 100 : 0;

            $sheet1->setCellValue('A' . $row, $categoria);
            $sheet1->setCellValue('B' . $row, $datos['cantidad']);
            $sheet1->setCellValue('C' . $row, number_format($datos['total_usd'], 2));
            $sheet1->setCellValue('D' . $row, number_format($datos['total_bs'], 2));
            $sheet1->setCellValue('E' . $row, number_format($porcentaje, 2) . '%');

            // Color based on percentage
            if ($porcentaje >= 50) {
                $color = 'FFC8E6C9';
            } elseif ($porcentaje >= 20) {
                $color = 'FFFFF9C4';
            } else {
                $color = 'FFFFCDD2';
            }
            $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);

            $row++;
        }

        // Totals
        $sheet1->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet1->setCellValue('B' . $row, $totalTransacciones);
        $sheet1->setCellValue('C' . $row, number_format($granTotalUSD, 2));
        $sheet1->setCellValue('D' . $row, number_format($granTotalBS, 2));
        $sheet1->setCellValue('E' . $row, '100.00%');
        $sheet1->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF81C784');

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // =====================================================
        // SHEET 2: BREAKDOWN BY SERVICES AND PRODUCTS
        // =====================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Servicios y Productos');

        $sheet2->setCellValue('A1', 'DESGLOSE POR SERVICIOS Y PRODUCTOS');
        $sheet2->mergeCells('A1:F1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Services Summary
        $row = 3;
        $sheet2->setCellValue('A' . $row, 'RESUMEN DE SERVICIOS MÉDICOS');
        $sheet2->mergeCells('A' . $row . ':F' . $row);
        $sheet2->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet2->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2196F3');
        $sheet2->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;
        $servHeaders = ['Concepto/Servicio', 'Cantidad', 'Precio Unit. USD', 'Subtotal USD', 'Subtotal ' . $codigoMoneda, 'Pacientes'];
        foreach ($servHeaders as $col => $header) {
            $sheet2->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE3F2FD');

        $row++;

        // Group services by baremo/concept
        $serviciosAgrupados = [];
        foreach ($pagosAprobados as $pago) {
            foreach ($pago->detalles as $detalle) {
                $concepto = $detalle->baremo ? $detalle->baremo->nombre : 'Servicio Médico';
                if (!isset($serviciosAgrupados[$concepto])) {
                    $serviciosAgrupados[$concepto] = [
                        'cantidad' => 0,
                        'subtotal' => 0,
                        'pacientes' => collect()
                    ];
                }
                $serviciosAgrupados[$concepto]['cantidad']++;
                $serviciosAgrupados[$concepto]['subtotal'] += $detalle->subtotal;
                $serviciosAgrupados[$concepto]['pacientes']->push($pago->consulta?->paciente?->id);
            }
        }

        $totalServiciosUSD = 0;
        foreach ($serviciosAgrupados as $concepto => $datos) {
            $precioPromedio = $datos['cantidad'] > 0 ? $datos['subtotal'] / $datos['cantidad'] : 0;
            $subtotalBs = $datos['subtotal'] * $tasaCambio;
            $pacientesUnicos = $datos['pacientes']->unique()->count();

            $sheet2->setCellValue('A' . $row, $concepto);
            $sheet2->setCellValue('B' . $row, $datos['cantidad']);
            $sheet2->setCellValue('C' . $row, number_format($precioPromedio, 2));
            $sheet2->setCellValue('D' . $row, number_format($datos['subtotal'], 2));
            $sheet2->setCellValue('E' . $row, number_format($subtotalBs, 2));
            $sheet2->setCellValue('F' . $row, $pacientesUnicos);

            $totalServiciosUSD += $datos['subtotal'];
            $row++;
        }

        // Services Total
        $sheet2->setCellValue('A' . $row, 'TOTAL SERVICIOS');
        $sheet2->setCellValue('D' . $row, number_format($totalServiciosUSD, 2));
        $sheet2->setCellValue('E' . $row, number_format($totalServiciosUSD * $tasaCambio, 2));
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBBDEFB');

        // Products Summary
        $row += 3;
        $sheet2->setCellValue('A' . $row, 'RESUMEN DE VENTAS DE PRODUCTOS');
        $sheet2->mergeCells('A' . $row . ':F' . $row);
        $sheet2->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet2->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF9800');
        $sheet2->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;
        $prodHeaders = ['Producto', 'Cantidad Vendida', 'Precio Unit. USD', 'Subtotal USD', 'Subtotal ' . $codigoMoneda, 'Ventas'];
        foreach ($prodHeaders as $col => $header) {
            $sheet2->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFE0B2');

        $row++;

        // Group products
        $productosAgrupados = [];
        foreach ($pagosAprobados as $pago) {
            foreach ($pago->ventasProductos as $venta) {
                $producto = $venta->producto ? $venta->producto->nombre : 'Producto';
                if (!isset($productosAgrupados[$producto])) {
                    $productosAgrupados[$producto] = [
                        'cantidad' => 0,
                        'subtotal' => 0,
                        'ventas_count' => 0
                    ];
                }
                $productosAgrupados[$producto]['cantidad'] += $venta->cantidad;
                $productosAgrupados[$producto]['subtotal'] += $venta->subtotal;
                $productosAgrupados[$producto]['ventas_count']++;
            }
        }

        $totalProductosUSD = 0;
        foreach ($productosAgrupados as $producto => $datos) {
            $precioPromedio = $datos['cantidad'] > 0 ? $datos['subtotal'] / $datos['cantidad'] : 0;
            $subtotalBs = $datos['subtotal'] * $tasaCambio;

            $sheet2->setCellValue('A' . $row, $producto);
            $sheet2->setCellValue('B' . $row, $datos['cantidad']);
            $sheet2->setCellValue('C' . $row, number_format($precioPromedio, 2));
            $sheet2->setCellValue('D' . $row, number_format($datos['subtotal'], 2));
            $sheet2->setCellValue('E' . $row, number_format($subtotalBs, 2));
            $sheet2->setCellValue('F' . $row, $datos['ventas_count']);

            $totalProductosUSD += $datos['subtotal'];
            $row++;
        }

        // Products Total
        $sheet2->setCellValue('A' . $row, 'TOTAL PRODUCTOS');
        $sheet2->setCellValue('D' . $row, number_format($totalProductosUSD, 2));
        $sheet2->setCellValue('E' . $row, number_format($totalProductosUSD * $tasaCambio, 2));
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFCC80');

        // Grand Total Comparison
        $row += 3;
        $sheet2->setCellValue('A' . $row, 'COMPARATIVO TOTAL');
        $sheet2->mergeCells('A' . $row . ':F' . $row);
        $sheet2->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet2->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4CAF50');
        $sheet2->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;
        $compHeaders = ['Concepto', '', 'Monto USD', 'Monto ' . $codigoMoneda, '% del Total', ''];
        foreach ($compHeaders as $col => $header) {
            if ($header) {
                $sheet2->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
        }
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);

        $row++;
        $granTotalCalculado = $totalServiciosUSD + $totalProductosUSD;

        $comparativo = [
            ['Servicios Médicos', '', number_format($totalServiciosUSD, 2), number_format($totalServiciosUSD * $tasaCambio, 2),
             $granTotalCalculado > 0 ? number_format(($totalServiciosUSD / $granTotalCalculado) * 100, 2) . '%' : '0%', ''],
            ['Ventas de Productos', '', number_format($totalProductosUSD, 2), number_format($totalProductosUSD * $tasaCambio, 2),
             $granTotalCalculado > 0 ? number_format(($totalProductosUSD / $granTotalCalculado) * 100, 2) . '%' : '0%', ''],
            ['TOTAL GENERAL', '', number_format($granTotalCalculado, 2), number_format($granTotalCalculado * $tasaCambio, 2), '100.00%', ''],
        ];

        foreach ($comparativo as $data) {
            $sheet2->setCellValue('A' . $row, $data[0]);
            $sheet2->setCellValue('C' . $row, $data[2]);
            $sheet2->setCellValue('D' . $row, $data[3]);
            $sheet2->setCellValue('E' . $row, $data[4]);
            $sheet2->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // =====================================================
        // SHEET 3: COMPLETE TRANSACTION LOG
        // =====================================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Transacciones');

        $sheet3->setCellValue('A1', 'DETALLE COMPLETO DE TRANSACCIONES');
        $sheet3->mergeCells('A1:I1');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet3->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 3;
        $transHeaders = ['N° Documento', 'Control Fiscal', 'Paciente/Cliente', 'Tipo', 'Método', 'Monto USD', 'Monto ' . $codigoMoneda, 'Referencia', 'Hora'];
        foreach ($transHeaders as $col => $header) {
            $sheet3->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $sheet3->getStyle('A' . $row . ':I' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2196F3');
        $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row++;

        foreach ($pagosAprobados as $pago) {
            $paciente = $pago->consulta && $pago->consulta->paciente
                ? $pago->consulta->paciente->nombre_completo
                : ($pago->clienteFiscal ? $pago->clienteFiscal->razon_social : 'N/A');

            $tieneNotaCredito = $pago->notasCredito()->where('estado', 'aprobado')->exists();

            // Determine transaction type
            $tieneServicios = $pago->detalles->count() > 0;
            $tieneProductos = $pago->ventasProductos->count() > 0;
            $tipo = '';
            if ($tieneServicios && $tieneProductos) {
                $tipo = 'Mixto';
            } elseif ($tieneServicios) {
                $tipo = 'Servicio';
            } elseif ($tieneProductos) {
                $tipo = 'Producto';
            }

            if ($pago->es_pago_mixto && $pago->detalles_pago_mixto) {
                foreach ($pago->detalles_pago_mixto as $detalle) {
                    $montoBs = isset($detalle['monto_bs']) ? format_money($detalle['monto_bs'], 2) : '-';

                    $sheet3->setCellValue('A' . $row, $pago->numero_completo . ($tieneNotaCredito ? ' (ANULADA)' : ''));
                    $sheet3->setCellValue('B' . $row, $pago->numero_control_fiscal ?? '-');
                    $sheet3->setCellValue('C' . $row, $paciente);
                    $sheet3->setCellValue('D' . $row, $tipo);
                    $sheet3->setCellValue('E' . $row, $this->getNombreAmigableMetodo($detalle['metodo']));
                    $sheet3->setCellValue('F' . $row, format_money($detalle['monto_usd'] ?? $detalle['monto'] ?? 0, 2));
                    $sheet3->setCellValue('G' . $row, $montoBs);
                    $sheet3->setCellValue('H' . $row, $detalle['referencia'] ?? '-');
                    $sheet3->setCellValue('I' . $row, $pago->created_at->format('H:i:s'));

                    if ($tieneNotaCredito) {
                        $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                        $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->setStrikethrough(true);
                    }

                    $row++;
                }
            } else {
                $montoBs = $pago->total_bs ? format_money($pago->total_bs, 2) : '-';

                $sheet3->setCellValue('A' . $row, $pago->numero_completo . ($tieneNotaCredito ? ' (ANULADA)' : ''));
                $sheet3->setCellValue('B' . $row, $pago->numero_control_fiscal ?? '-');
                $sheet3->setCellValue('C' . $row, $paciente);
                $sheet3->setCellValue('D' . $row, $tipo);
                $sheet3->setCellValue('E' . $row, $this->getNombreAmigableMetodo($pago->metodo_pago));
                $sheet3->setCellValue('F' . $row, format_money($pago->total_usd ?? $pago->total, 2));
                $sheet3->setCellValue('G' . $row, $montoBs);
                $sheet3->setCellValue('H' . $row, $pago->referencia ?? '-');
                $sheet3->setCellValue('I' . $row, $pago->created_at->format('H:i:s'));

                if ($tieneNotaCredito) {
                    $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                    $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->setStrikethrough(true);
                }

                $row++;
            }
        }

        // Borders and auto-size
        $lastRow = $row;
        $sheet3->getStyle('A3:I' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'I') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // =====================================================
        // Save and download
        // =====================================================
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_conglomerado_caja_' . $caja->sucursal->nombre . '_' .
                    $caja->fecha->format('Y-m-d') . '_corte' . $caja->numero_corte . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    /**
     * Get friendly payment method name
     */
    private function getNombreAmigableMetodo(string $metodo): string
    {
        $nombres = [
            'efectivo' => 'Efectivo',
            'efectivo_bs' => 'Efectivo Bs',
            'efectivo_usd' => 'Efectivo USD',
            'transferencia' => 'Transferencia',
            'transferencia_bs' => 'Transferencia Bs',
            'transferencia_usd' => 'Transferencia USD',
            'pago_movil' => 'Pago Móvil',
            'zelle' => 'Zelle',
            'paypal' => 'PayPal',
            'usdt' => 'USDT',
            'tarjeta' => 'Tarjeta',
            'tarjeta_debito' => 'Tarjeta Débito',
            'tarjeta_credito' => 'Tarjeta Crédito',
            'bbva_dr' => 'BBVA Débito',
            'bbva_cr' => 'BBVA Crédito',
            'mercantil_dr' => 'Mercantil Débito',
            'mercantil_cr' => 'Mercantil Crédito',
            'banesco_dr' => 'Banesco Débito',
            'banesco_cr' => 'Banesco Crédito',
            'provincial_dr' => 'Provincial Débito',
            'provincial_cr' => 'Provincial Crédito',
            'bod_dr' => 'BOD Débito',
            'bod_cr' => 'BOD Crédito',
        ];

        return $nombres[$metodo] ?? ucfirst(str_replace('_', ' ', $metodo));
    }

    /**
     * Group payments by category
     */
    private function agruparPorCategoria($pagos): array
    {
        $categorias = [
            'EFECTIVO' => ['cantidad' => 0, 'total_usd' => 0, 'total_bs' => 0],
            'TRANSFERENCIAS' => ['cantidad' => 0, 'total_usd' => 0, 'total_bs' => 0],
            'TARJETAS/BANCOS' => ['cantidad' => 0, 'total_usd' => 0, 'total_bs' => 0],
            'OTROS' => ['cantidad' => 0, 'total_usd' => 0, 'total_bs' => 0],
        ];

        foreach ($pagos as $pago) {
            $categoria = $this->getCategoriaMetodo($pago->metodo_pago);
            $categorias[$categoria]['cantidad']++;
            $categorias[$categoria]['total_usd'] += $pago->total_usd ?? $pago->total;
            $categorias[$categoria]['total_bs'] += $pago->total_bs ?? 0;
        }

        return array_filter($categorias, fn($cat) => $cat['cantidad'] > 0);
    }

    /**
     * Get payment method category
     */
    private function getCategoriaMetodo(string $metodo): string
    {
        $efectivo = ['efectivo', 'efectivo_bs', 'efectivo_usd'];
        $transferencias = ['transferencia', 'transferencia_bs', 'transferencia_usd', 'pago_movil', 'zelle', 'paypal', 'usdt'];
        $tarjetas = ['tarjeta', 'tarjeta_debito', 'tarjeta_credito', 'bbva_dr', 'bbva_cr',
                     'mercantil_dr', 'mercantil_cr', 'banesco_dr', 'banesco_cr',
                     'provincial_dr', 'provincial_cr', 'bod_dr', 'bod_cr'];

        if (in_array($metodo, $efectivo)) return 'EFECTIVO';
        if (in_array($metodo, $transferencias)) return 'TRANSFERENCIAS';
        if (in_array($metodo, $tarjetas)) return 'TARJETAS/BANCOS';

        return 'OTROS';
    }
}
