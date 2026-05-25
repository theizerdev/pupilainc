<?php

namespace App\Livewire\Admin\Cajas;

use App\Models\Caja;
use App\Models\ExchangeRate;
use App\Models\Empresa;
use App\Helpers\MetodoPagoHelper;
use App\Traits\HasDynamicLayout;
use App\Traits\HasRegionalFormatting;
use App\Traits\HasDualCurrency;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Show extends Component
{
    use HasDynamicLayout, HasRegionalFormatting, HasDualCurrency;


    public Caja $caja;
    public $observaciones_cierre = '';
    public $showCerrarModal = false;
    public $showRecalcularModal = false;

    // Variable para controlar qué conceptos tienen sus detalles expandidos
    public $expandedConceptos = [];

    protected $rules = [
        'observaciones_cierre' => 'nullable|string|max:500',
    ];

    public function mount(Caja $caja)
    {
        $this->caja = $caja->load(['usuario', 'sucursal', 'pagos.consulta.paciente', 'pagos.detalles.baremo']);
    }

    public function toggleConceptoDetails($concepto)
    {
        if (in_array($concepto, $this->expandedConceptos)) {
            $this->expandedConceptos = array_diff($this->expandedConceptos, [$concepto]);
        } else {
            $this->expandedConceptos[] = $concepto;
        }
    }

    public function abrirModalCerrar()
    {
        if ($this->caja->estado === 'cerrada') {
            session()->flash('error', 'La caja ya está cerrada.');
            return;
        }

        $this->caja->calcularTotales();
        $this->showCerrarModal = true;
    }

    public function cerrarCaja()
    {
        $this->validate();

        if ($this->caja->cerrar($this->observaciones_cierre)) {
            $this->showCerrarModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Caja cerrada exitosamente.',
                'duration' => 4000
            ]);
            $this->caja->refresh();

            // Enviar notificación WhatsApp con el reporte Excel
            $this->enviarNotificacionCierreCaja();
        }
 else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se pudo cerrar la caja.',
                'duration' => 4000
            ]);
        }
    }

    public function recalcularMontos()
    {
        if ($this->caja->estado !== 'cerrada') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Solo se pueden recalcular montos de cajas cerradas.',
                'duration' => 4000
            ]);
            return;
        }

        $this->showRecalcularModal = true;
    }

    public function confirmarRecalcular()
    {
        if ($this->caja->estado !== 'cerrada') {
            session()->flash('error', 'Solo se pueden recalcular montos de cajas cerradas.');
            $this->showRecalcularModal = false;
            return;
        }

        try {
            // Recalcular los totales
            $this->caja->calcularTotales();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Montos recalculados exitosamente. El monto de cierre se ha actualizado.',
                'duration' => 4000
            ]);
            $this->caja->refresh();
            $this->showRecalcularModal = false;

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al recalcular los montos: ' . $e->getMessage(),
                'duration' => 5000
            ]);
            $this->showRecalcularModal = false;
        }
    }

    public function getResumenPorMetodoProperty()
    {
        return $this->caja->pagos()
            ->where('estado', 'aprobado')
            ->whereDoesntHave('notasCredito', fn($q) => $q->where('estado', 'aprobado'))
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total_usd) as total_usd, SUM(total_bs) as total_bs')
            ->groupBy('metodo_pago')
            ->get();
    }

    /**
     * Exportar resumen por método de pago a Excel (Versión Mejorada)
     */
    public function exportarResumenPorMetodoExcel()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Obtener información de la empresa
            $empresa = $this->caja->empresa;
            $pais = $empresa->pais;
            $esVenezuela = strtolower($pais->nombre) === 'venezuela';
            $codigoMoneda = $esVenezuela ? 'Bs' : $pais->codigo_moneda;
            $tasaCambio = $empresa->tasa_cambio ?? 1;

            // Obtener datos
            $resumen = $this->resumenPorMetodo;
            $granTotalUSD = $resumen->sum('total_usd');
            $granTotalBS = $resumen->sum('total_bs');
            $totalTransacciones = $resumen->sum('cantidad');

            // =====================================================
            // HOJA 1: RESUMEN EJECUTIVO
            // =====================================================
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Resumen Ejecutivo');

            // Título
            $sheet1->setCellValue('A1', 'RESUMEN EJECUTIVO - CAJA');
            $sheet1->mergeCells('A1:D1');
            $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(18);
            $sheet1->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E7D32');
            $sheet1->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');

            // Información general
            $row = 3;
            $infoData = [
                ['Fecha:', format_date($this->caja->fecha), 'Caja N°:', $this->caja->numero_corte],
                ['Sucursal:', $this->caja->sucursal->nombre, 'Estado:', ucfirst($this->caja->estado)],
                ['Usuario:', $this->caja->usuario->name, 'Hora Apertura:', $this->caja->fecha_apertura?->format('H:i') ?? '-'],
            ];

            foreach ($infoData as $data) {
                $sheet1->setCellValue('A' . $row, $data[0]);
                $sheet1->setCellValue('B' . $row, $data[1]);
                $sheet1->setCellValue('C' . $row, $data[2]);
                $sheet1->setCellValue('D' . $row, $data[3]);
                $sheet1->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                $row++;
            }

            // KPIs Principales
            $row += 2;
            $sheet1->setCellValue('A' . $row, 'INDICADORES CLAVE (KPIs)');
            $sheet1->mergeCells('A' . $row . ':D' . $row);
            $sheet1->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1976D2');
            $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            // Calcular KPIs
            $ticketPromedio = $totalTransacciones > 0 ? $granTotalUSD / $totalTransacciones : 0;

            // Encontrar método dominante
            $metodoDominante = $resumen->sortByDesc('total_usd')->first();
            $metodoNombre = $this->getNombreAmigableMetodo($metodoDominante->metodo_pago ?? '');

            // Método más usado (por cantidad)
            $metodoMasUsado = $resumen->sortByDesc('cantidad')->first();
            $metodoMasUsadoNombre = $this->getNombreAmigableMetodo($metodoMasUsado->metodo_pago ?? '');

            $kpis = [
                ['Total Ingresos USD:', '$ ' . number_format($granTotalUSD, 2), 'Total Ingresos ' . $codigoMoneda . ':', $codigoMoneda . ' ' . number_format($granTotalBS, 2)],
                ['Total Transacciones:', $totalTransacciones, 'Ticket Promedio:', '$ ' . number_format($ticketPromedio, 2)],
                ['Método Mayor Monto:', $metodoNombre . ' ($' . number_format($metodoDominante->total_usd ?? 0, 2) . ')', 'Método Más Usado:', $metodoMasUsadoNombre . ' (' . $metodoMasUsado->cantidad . ' trans.)'],
                ['Monto Inicial:', '$ ' . number_format($this->caja->monto_inicial, 2), 'Monto Final:', '$ ' . number_format($this->caja->monto_final, 2)],
            ];

            foreach ($kpis as $kpi) {
                $sheet1->setCellValue('A' . $row, $kpi[0]);
                $sheet1->setCellValue('B' . $row, $kpi[1]);
                $sheet1->setCellValue('C' . $row, $kpi[2]);
                $sheet1->setCellValue('D' . $row, $kpi[3]);
                $sheet1->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $row++;
            }

            // Resumen por Categoría
            $row += 2;
            $sheet1->setCellValue('A' . $row, 'RESUMEN POR CATEGORÍA');
            $sheet1->mergeCells('A' . $row . ':E' . $row);
            $sheet1->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF388E3C');
            $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;
            $categoriasHeaders = ['Categoría', 'Transacciones', 'Total USD', 'Total ' . $codigoMoneda, '% del Total'];
            foreach ($categoriasHeaders as $col => $header) {
                $sheet1->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet1->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFC8E6C9');

            $row++;

            // Agrupar por categorías
            $categorias = $this->agruparPorCategoria($resumen);
            foreach ($categorias as $categoria => $datos) {
                $porcentaje = $granTotalUSD > 0 ? ($datos['total_usd'] / $granTotalUSD) * 100 : 0;

                $sheet1->setCellValue('A' . $row, $categoria);
                $sheet1->setCellValue('B' . $row, $datos['cantidad']);
                $sheet1->setCellValue('C' . $row, number_format($datos['total_usd'], 2));
                $sheet1->setCellValue('D' . $row, number_format($datos['total_bs'], 2));
                $sheet1->setCellValue('E' . $row, number_format($porcentaje, 2) . '%');

                // Color según porcentaje
                if ($porcentaje >= 50) {
                    $color = 'FFC8E6C9'; // Verde claro
                } elseif ($porcentaje >= 20) {
                    $color = 'FFFFF9C4'; // Amarillo claro
                } else {
                    $color = 'FFFFCDD2'; // Rojo claro
                }
                $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);

                $row++;
            }

            // Totales de categorías
            $sheet1->setCellValue('A' . $row, 'TOTAL GENERAL');
            $sheet1->setCellValue('B' . $row, $totalTransacciones);
            $sheet1->setCellValue('C' . $row, number_format($granTotalUSD, 2));
            $sheet1->setCellValue('D' . $row, number_format($granTotalBS, 2));
            $sheet1->setCellValue('E' . $row, '100.00%');
            $sheet1->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet1->getStyle('A' . $row . ':E' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF81C784');

            // Auto-ajustar columnas
            foreach (range('A', 'E') as $col) {
                $sheet1->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // HOJA 2: DETALLE POR MÉTODO
            // =====================================================
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Detalle por Método');

            $sheet2->setCellValue('A1', 'DETALLE POR MÉTODO DE PAGO');
            $sheet2->mergeCells('A1:F1');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $methodHeaders = ['Método de Pago', 'Categoría', 'Cantidad', 'Total USD', 'Total ' . $codigoMoneda, '% del Total'];
            foreach ($methodHeaders as $col => $header) {
                $sheet2->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4CAF50');
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            foreach ($resumen as $item) {
                $metodoNombre = $this->getNombreAmigableMetodo($item->metodo_pago);
                $categoria = $this->getCategoriaMetodo($item->metodo_pago);
                $porcentaje = $granTotalUSD > 0 ? ($item->total_usd / $granTotalUSD) * 100 : 0;

                $sheet2->setCellValue('A' . $row, $metodoNombre);
                $sheet2->setCellValue('B' . $row, $categoria);
                $sheet2->setCellValue('C' . $row, $item->cantidad);
                $sheet2->setCellValue('D' . $row, number_format($item->total_usd, 2));
                $sheet2->setCellValue('E' . $row, number_format($item->total_bs ?? 0, 2));
                $sheet2->setCellValue('F' . $row, number_format($porcentaje, 2) . '%');

                $row++;
            }

            // Totales
            $sheet2->setCellValue('A' . $row, 'TOTAL');
            $sheet2->setCellValue('C' . $row, $resumen->sum('cantidad'));
            $sheet2->setCellValue('D' . $row, number_format($granTotalUSD, 2));
            $sheet2->setCellValue('E' . $row, number_format($granTotalBS, 2));
            $sheet2->setCellValue('F' . $row, '100.00%');
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);

            // Bordes y auto-ajuste
            $lastRow = $row;
            $sheet2->getStyle('A3:F' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            foreach (range('A', 'F') as $col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // HOJA 3: DETALLE COMPLETO DE TRANSACCIONES
            // =====================================================
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Transacciones');

            $sheet3->setCellValue('A1', 'DETALLE COMPLETO DE TRANSACCIONES');
            $sheet3->mergeCells('A1:H1');
            $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet3->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $transHeaders = ['N° Documento', 'Paciente/Cliente', 'Método', 'Categoría', 'Total USD', 'Total ' . $codigoMoneda, 'Hora', 'Estado'];
            foreach ($transHeaders as $col => $header) {
                $sheet3->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet3->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet3->getStyle('A' . $row . ':H' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2196F3');
            $sheet3->getStyle('A' . $row . ':H' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            // Obtener todas las transacciones
            $pagos = $this->caja->pagos()
                ->where('estado', 'aprobado')
                ->whereDoesntHave('notasCredito', fn($q) => $q->where('estado', 'aprobado'))
                ->with(['consulta.paciente', 'clienteFiscal'])
                ->orderBy('created_at')
                ->get();

            foreach ($pagos as $pago) {
                $paciente = $pago->consulta && $pago->consulta->paciente
                    ? $pago->consulta->paciente->nombre_completo
                    : ($pago->clienteFiscal ? $pago->clienteFiscal->razon_social : 'N/A');

                $metodoNombre = $this->getNombreAmigableMetodo($pago->metodo_pago);
                $categoria = $this->getCategoriaMetodo($pago->metodo_pago);

                $tieneNotaCredito = $pago->notasCredito()->where('estado', 'aprobado')->exists();

                $sheet3->setCellValue('A' . $row, $pago->numero_completo);
                $sheet3->setCellValue('B' . $row, $paciente);
                $sheet3->setCellValue('C' . $row, $metodoNombre);
                $sheet3->setCellValue('D' . $row, $categoria);
                $sheet3->setCellValue('E' . $row, number_format($pago->total_usd ?? $pago->total, 2));
                $sheet3->setCellValue('F' . $row, number_format($pago->total_bs ?? 0, 2));
                $sheet3->setCellValue('G' . $row, $pago->created_at->format('H:i:s'));
                $sheet3->setCellValue('H' . $row, $tieneNotaCredito ? 'ANULADO' : 'Aprobado');

                if ($tieneNotaCredito) {
                    $sheet3->getStyle('A' . $row . ':H' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                    $sheet3->getStyle('A' . $row . ':H' . $row)->getFont()->setStrikethrough(true);
                }

                $row++;
            }

            // Bordes y auto-ajuste
            $lastRow = $row;
            $sheet3->getStyle('A3:H' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            foreach (range('A', 'H') as $col) {
                $sheet3->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // Guardar archivo
            // =====================================================
            $filename = 'resumen_caja_' . $this->caja->id . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempPath = storage_path('app/temp/' . $filename);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error al exportar resumen por método de pago: ' . $e->getMessage());
            session()->flash('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener nombre amigable del método de pago
     * Usa MetodoPagoHelper para mantener consistencia en todo el sistema
     */
    private function getNombreAmigableMetodo(string $metodo): string
    {
        return MetodoPagoHelper::getNombreAmigable($metodo);
    }

    /**
     * Obtener categoría del método de pago
     * Usa MetodoPagoHelper para mantener consistencia en todo el sistema
     */
    private function getCategoriaMetodo(string $metodo): string
    {
        return MetodoPagoHelper::getCategoria($metodo);
    }

    /**
     * Agrupar resumen por categorías
     * Usa MetodoPagoHelper para mantener consistencia
     */
    private function agruparPorCategoria($resumen): array
    {
        $categorias = [];

        foreach ($resumen as $item) {
            $categoria = $this->getCategoriaMetodo($item->metodo_pago);

            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = ['cantidad' => 0, 'total_usd' => 0, 'total_bs' => 0];
            }

            $categorias[$categoria]['cantidad'] += $item->cantidad;
            $categorias[$categoria]['total_usd'] += $item->total_usd;
            $categorias[$categoria]['total_bs'] += $item->total_bs ?? 0;
        }

        // Eliminar categorías vacías
        return array_filter($categorias, fn($cat) => $cat['cantidad'] > 0);
    }

    public function getResumenPorConceptoProperty()
    {
        $pagos = $this->caja->pagos()
            ->where('estado', 'aprobado')
            ->whereDoesntHave('notasCredito', fn($q) => $q->where('estado', 'aprobado'))
            ->with(['detalles.baremo', 'consulta.paciente', 'ventasProductos.producto'])
            ->get();

        $conceptos = collect();

        // Procesar detalles de pago (servicios)
        $pagos->each(function ($pago) use ($conceptos) {
            $tasaCambio = $pago->tasa_cambio_usd ?? 1;

            $pago->detalles->each(function ($detalle) use ($conceptos, $pago, $tasaCambio) {
                // Convertir de Bs a USD si el precio está en Bs
                $precioUSD = $detalle->precio_unitario / $tasaCambio;
                $subtotalUSD = $detalle->subtotal / $tasaCambio;

                $conceptos->push([
                    'concepto' => $detalle->baremo->nombre_servicio ?? $detalle->descripcion ?? 'Sin concepto',
                    'cantidad' => $detalle->cantidad,
                    'precio' => $precioUSD,
                    'subtotal' => $subtotalUSD,
                    'pago_id' => $pago->id,
                    'numero_pago' => $pago->numero_completo,
                    'paciente' => $pago->consulta?->paciente?->nombre_completo ?? 'N/A',
                    'fecha_pago' => $pago->created_at->format('d/m/Y H:i'),
                    'metodo_pago' => ucfirst(str_replace('_', ' ', $pago->metodo_pago)),
                    'referencia' => $pago->referencia ?? 'N/A',
                ]);
            });
        });

        // Procesar ventas de productos
        $pagos->each(function ($pago) use ($conceptos) {
            $tasaCambio = $pago->tasa_cambio_usd ?? 1;

            $pago->ventasProductos->each(function ($ventaProducto) use ($conceptos, $pago, $tasaCambio) {
                // Convertir de Bs a USD si el precio está en Bs
                $precioUSD = $ventaProducto->precio_unitario / $tasaCambio;
                $subtotalUSD = $ventaProducto->subtotal / $tasaCambio;

                $conceptos->push([
                    'concepto' => $ventaProducto->producto->nombre ?? 'Venta de producto',
                    'cantidad' => $ventaProducto->cantidad,
                    'precio' => $precioUSD,
                    'subtotal' => $subtotalUSD,
                    'pago_id' => $pago->id,
                    'numero_pago' => $pago->numero_completo,
                    'paciente' => $pago->consulta?->paciente?->nombre_completo ?? 'N/A',
                    'fecha_pago' => $pago->created_at->format('d/m/Y H:i'),
                    'metodo_pago' => ucfirst(str_replace('_', ' ', $pago->metodo_pago)),
                    'referencia' => $pago->referencia ?? 'N/A',
                ]);
            });
        });

        return $conceptos
            ->groupBy('concepto')
            ->map(function ($items, $concepto) {
                return [
                    'concepto' => $concepto,
                    'cantidad' => $items->sum('cantidad'),
                    'total' => $items->sum('subtotal'),
                    'detalle' => $items->map(function ($item) {
                        return [
                            'pago_id' => $item['pago_id'],
                            'numero_pago' => $item['numero_pago'],
                            'paciente' => $item['paciente'],
                            'fecha_pago' => $item['fecha_pago'],
                            'metodo_pago' => $item['metodo_pago'],
                            'referencia' => $item['referencia'],
                            'cantidad' => $item['cantidad'],
                            'precio' => $item['precio'],
                            'subtotal' => $item['subtotal'],
                        ];
                    }),
                ];
            });
    }

    public function exportarResumenConceptosExcel()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Get company info for currency formatting
            $empresa = $this->caja->empresa;
            $pais = $empresa->pais;
            $esVenezuela = strtolower($pais->nombre) === 'venezuela';
            $codigoMoneda = $esVenezuela ? 'Bs' : $pais->codigo_moneda;
            $tasaCambio = $empresa->tasa_cambio ?? 1;

            // Get concept summary data
            $resumen = $this->resumenPorConcepto;

            // Calculate KPIs
            $totalConceptos = $resumen->count();
            $granTotalUSD = $resumen->sum('total');
            $granTotalBS = $granTotalUSD * $tasaCambio;
            $totalCantidad = $resumen->sum('cantidad');
            $precioPromedio = $totalCantidad > 0 ? $granTotalUSD / $totalCantidad : 0;

            // =====================================================
            // SHEET 1: EXECUTIVE SUMMARY
            // =====================================================
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Resumen Ejecutivo');

            // Title
            $sheet1->setCellValue('A1', 'RESUMEN POR CONCEPTO - CAJA #' . $this->caja->numero_corte);
            $sheet1->mergeCells('A1:D1');
            $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(18);
            $sheet1->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E7D32');
            $sheet1->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');

            // General Information
            $row = 3;
            $infoData = [
                ['Fecha:', format_date($this->caja->fecha), 'Caja N°:', $this->caja->numero_corte],
                ['Sucursal:', $this->caja->sucursal->nombre, 'Estado:', ucfirst($this->caja->estado)],
                ['Usuario:', $this->caja->usuario->name, 'Tasa de Cambio:', $tasaCambio . ' ' . $codigoMoneda . '/$'],
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
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1976D2');
            $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            // Find top concepts
            $topConcepto = $resumen->sortByDesc('total')->first();
            $conceptoMasVendido = $resumen->sortByDesc('cantidad')->first();

            $kpis = [
                ['Total Conceptos:', $totalConceptos, 'Gran Total USD:', '$ ' . number_format($granTotalUSD, 2)],
                ['Total Cantidad:', $totalCantidad, 'Gran Total ' . $codigoMoneda . ':', $codigoMoneda . ' ' . number_format($granTotalBS, 2)],
                ['Precio Promedio:', '$ ' . number_format($precioPromedio, 2), 'Concepto Mayor Ingreso:', substr($topConcepto['concepto'] ?? 'N/A', 0, 30)],
                ['Monto Inicial:', '$ ' . number_format($this->caja->monto_inicial, 2), 'Concepto Más Vendido:', substr($conceptoMasVendido['concepto'] ?? 'N/A', 0, 30)],
            ];

            foreach ($kpis as $kpi) {
                $sheet1->setCellValue('A' . $row, $kpi[0]);
                $sheet1->setCellValue('B' . $row, $kpi[1]);
                $sheet1->setCellValue('C' . $row, $kpi[2]);
                $sheet1->setCellValue('D' . $row, $kpi[3]);
                $sheet1->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $row++;
            }

            // Financial Summary
            $row += 2;
            $sheet1->setCellValue('A' . $row, 'RESUMEN FINANCIERO DE LA CAJA');
            $sheet1->mergeCells('A' . $row . ':C' . $row);
            $sheet1->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF388E3C');
            $sheet1->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;
            $finHeaders = ['Concepto', 'Monto USD', 'Monto ' . $codigoMoneda];
            foreach ($finHeaders as $col => $header) {
                $sheet1->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet1->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            $sheet1->getStyle('A' . $row . ':C' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFC8E6C9');

            $row++;

            $financialData = [
                ['Monto Inicial', format_money($this->caja->monto_inicial, 2), format_money($this->caja->monto_inicial * $tasaCambio, 2)],
                ['Total Efectivo', format_money($this->caja->total_efectivo, 2), format_money($this->caja->total_efectivo * $tasaCambio, 2)],
                ['Total Transferencias', format_money($this->caja->total_transferencias, 2), format_money($this->caja->total_transferencias * $tasaCambio, 2)],
                ['Total Tarjetas', format_money($this->caja->total_tarjetas, 2), format_money($this->caja->total_tarjetas * $tasaCambio, 2)],
                ['Total Ingresos', format_money($this->caja->total_ingresos, 2), format_money($this->caja->total_ingresos * $tasaCambio, 2)],
                ['Monto Final', format_money($this->caja->monto_final, 2), format_money($this->caja->monto_final * $tasaCambio, 2)]
            ];

            foreach ($financialData as $data) {
                $sheet1->setCellValue('A' . $row, $data[0]);
                $sheet1->setCellValue('B' . $row, $data[1]);
                $sheet1->setCellValue('C' . $row, $data[2]);
                $sheet1->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'D') as $col) {
                $sheet1->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // SHEET 2: CONCEPT SUMMARY
            // =====================================================
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Resumen por Concepto');

            $sheet2->setCellValue('A1', 'RESUMEN DETALLADO POR CONCEPTO');
            $sheet2->mergeCells('A1:F1');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $conceptHeaders = ['Concepto/Servicio', 'Cantidad', 'Precio Prom. USD', 'Total USD', 'Total ' . $codigoMoneda, '% del Total'];
            foreach ($conceptHeaders as $col => $header) {
                $sheet2->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4CAF50');
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            // Sort concepts by total (descending)
            $resumenOrdenado = $resumen->sortByDesc('total');

            foreach ($resumenOrdenado as $conceptoData) {
                $porcentaje = $granTotalUSD > 0 ? ($conceptoData['total'] / $granTotalUSD) * 100 : 0;
                $precioPromedioConcepto = $conceptoData['cantidad'] > 0 ? $conceptoData['total'] / $conceptoData['cantidad'] : 0;
                $totalBs = $conceptoData['total'] * $tasaCambio;

                $sheet2->setCellValue('A' . $row, $conceptoData['concepto']);
                $sheet2->setCellValue('B' . $row, $conceptoData['cantidad']);
                $sheet2->setCellValue('C' . $row, number_format($precioPromedioConcepto, 2));
                $sheet2->setCellValue('D' . $row, number_format($conceptoData['total'], 2));
                $sheet2->setCellValue('E' . $row, number_format($totalBs, 2));
                $sheet2->setCellValue('F' . $row, number_format($porcentaje, 2) . '%');

                // Color based on percentage
                if ($porcentaje >= 20) {
                    $color = 'FFC8E6C9'; // Green
                } elseif ($porcentaje >= 10) {
                    $color = 'FFFFF9C4'; // Yellow
                } else {
                    $color = 'FFFFCDD2'; // Red
                }
                $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);

                $row++;
            }

            // Totals row
            $sheet2->setCellValue('A' . $row, 'TOTAL GENERAL');
            $sheet2->setCellValue('B' . $row, $totalCantidad);
            $sheet2->setCellValue('D' . $row, number_format($granTotalUSD, 2));
            $sheet2->setCellValue('E' . $row, number_format($granTotalBS, 2));
            $sheet2->setCellValue('F' . $row, '100.00%');
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF81C784');

            // Borders
            $lastRow = $row;
            $sheet2->getStyle('A3:F' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Auto-size columns
            foreach (range('A', 'F') as $col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // SHEET 3: DETAILED TRANSACTIONS
            // =====================================================
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Detalle de Transacciones');

            $sheet3->setCellValue('A1', 'DETALLE COMPLETO DE TRANSACCIONES POR CONCEPTO');
            $sheet3->mergeCells('A1:I1');
            $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet3->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $detailHeaders = ['Documento', 'Paciente/Cliente', 'Concepto', 'Método de Pago', 'Cantidad', 'Precio Unit. USD', 'Subtotal USD', 'Subtotal ' . $codigoMoneda, 'Fecha/Hora'];
            foreach ($detailHeaders as $col => $header) {
                $sheet3->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
            $sheet3->getStyle('A' . $row . ':I' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2196F3');
            $sheet3->getStyle('A' . $row . ':I' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;

            // Loop through concepts and their details
            foreach ($resumenOrdenado as $conceptoData) {
                // Add concept separator
                $sheet3->setCellValue('A' . $row, '>>> ' . $conceptoData['concepto'] . ' (Total: $' . number_format($conceptoData['total'], 2) . ')');
                $sheet3->mergeCells('A' . $row . ':I' . $row);
                $sheet3->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
                $sheet3->getStyle('A' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE3F2FD');
                $row++;

                foreach ($conceptoData['detalle'] as $detalle) {
                    $subtotalBs = $detalle['subtotal'] * $tasaCambio;

                    $sheet3->setCellValue('A' . $row, $detalle['numero_pago']);
                    $sheet3->setCellValue('B' . $row, $detalle['paciente']);
                    $sheet3->setCellValue('C' . $row, $conceptoData['concepto']);
                    $sheet3->setCellValue('D' . $row, $detalle['metodo_pago']);
                    $sheet3->setCellValue('E' . $row, $detalle['cantidad']);
                    $sheet3->setCellValue('F' . $row, number_format($detalle['precio'], 2));
                    $sheet3->setCellValue('G' . $row, number_format($detalle['subtotal'], 2));
                    $sheet3->setCellValue('H' . $row, number_format($subtotalBs, 2));
                    $sheet3->setCellValue('I' . $row, $detalle['fecha_pago']);

                    $sheet3->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $row++;
                }

                // Add empty row between concepts
                $row++;
            }

            // Borders and auto-size
            $lastRow = $row;
            $sheet3->getStyle('A3:I' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            foreach (range('A', 'I') as $col) {
                $sheet3->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================================================
            // Save and download
            // =====================================================
            $filename = 'resumen_conceptos_caja_' . $this->caja->sucursal->nombre . '_' .
                        $this->caja->fecha->format('Y-m-d') . '_corte' . $this->caja->numero_corte . '.xlsx';
            $tempPath = storage_path('app/temp/' . $filename);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error al exportar resumen por concepto: ' . $e->getMessage());
            session()->flash('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
            return null;
        }
    }

    public function exportarExcel()
    {
        return redirect()->route('admin.cajas.export', $this->caja->id);
    }

    public function render()
    {
        return view('livewire.admin.cajas.show')->layout($this->getLayout());
    }

    /**
     * Enviar notificación WhatsApp con el reporte de cierre de caja
     */
    private function enviarNotificacionCierreCaja()
    {
        try {
            // Obtener la empresa para obtener el teléfono registrado
            $empresa = Empresa::find($this->caja->empresa_id);

            if (!$empresa || !$empresa->telefono) {
                \Log::warning('No se puede enviar notificación WhatsApp: empresa sin teléfono registrado', [
                    'caja_id' => $this->caja->id,
                    'empresa_id' => $this->caja->empresa_id
                ]);
                return;
            }

            // Generar el archivo Excel temporalmente
            $excelPath = $this->generarExcelTemporal();

            if (!$excelPath) {
                \Log::error('Error al generar el archivo Excel para la notificación', [
                    'caja_id' => $this->caja->id
                ]);
                return;
            }

            // Enviar mensaje con archivo adjunto (el mensaje se incluye en el caption)
            $resultado = $this->enviarWhatsAppConArchivo($empresa->telefono, '', $excelPath);

            if ($resultado) {
                session()->flash('success', 'Notificación WhatsApp enviada exitosamente');
            } else {
                session()->flash('warning', 'Caja cerrada, pero no se pudo enviar la notificación WhatsApp');
            }

            // Limpiar archivo temporal
            if (file_exists($excelPath)) {
                Storage::delete($excelPath);
            }

        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación WhatsApp de cierre de caja: ' . $e->getMessage(), [
                'caja_id' => $this->caja->id,
                'empresa_id' => $this->caja->empresa_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('warning', 'Caja cerrada, pero ocurrió un error al enviar la notificación WhatsApp');
        }
    }

    /**
     * Generar archivo Excel temporal del reporte de caja
     */
    private function generarExcelTemporal()
    {
        try {
            $this->caja->load([
                'usuario',
                'sucursal',
                'empresa.pais',
                'pagos.consulta.paciente',
                'pagos.clienteFiscal',
                'pagos.detalles.baremo',
                'pagos.ventasProductos.producto'
            ]);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Get company info
            $empresa = $this->caja->empresa;
            $pais = $empresa->pais;
            $esVenezuela = strtolower($pais->nombre) === 'venezuela';
            $codigoMoneda = $esVenezuela ? 'Bs' : $pais->codigo_moneda;
            $tasaCambio = $empresa->tasa_cambio ?? 1;

            // Configurar encabezado
            $sheet->setCellValue('A1', 'REPORTE DE CIERRE DE CAJA');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E7D32');
            $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');

            // Información de la caja
            $row = 3;
            $infoData = [
                ['Fecha:', $this->caja->fecha->format('d/m/Y'), 'Caja N°:', $this->caja->numero_corte],
                ['Sucursal:', $this->caja->sucursal->nombre, 'Estado:', ucfirst($this->caja->estado)],
                ['Usuario:', $this->caja->usuario->name, 'Tasa:', $tasaCambio . ' ' . $codigoMoneda . '/$'],
            ];

            foreach ($infoData as $data) {
                $sheet->setCellValue('A' . $row, $data[0]);
                $sheet->setCellValue('B' . $row, $data[1]);
                $sheet->setCellValue('C' . $row, $data[2]);
                $sheet->setCellValue('D' . $row, $data[3]);
                $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                $row++;
            }

            // Resumen financiero mejorado
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RESUMEN FINANCIERO');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1976D2');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;
            $datos = [
                ['Concepto', 'Monto USD', 'Monto ' . $codigoMoneda],
                ['Monto Inicial', format_money($this->caja->monto_inicial, 2), format_money($this->caja->monto_inicial * $tasaCambio, 2)],
                ['Total Efectivo', format_money($this->caja->total_efectivo, 2), format_money($this->caja->total_efectivo * $tasaCambio, 2)],
                ['Total Transferencias', format_money($this->caja->total_transferencias, 2), format_money($this->caja->total_transferencias * $tasaCambio, 2)],
                ['Total Tarjetas', format_money($this->caja->total_tarjetas, 2), format_money($this->caja->total_tarjetas * $tasaCambio, 2)],
                ['Total Ingresos', format_money($this->caja->total_ingresos, 2), format_money($this->caja->total_ingresos * $tasaCambio, 2)],
                ['Monto Final', format_money($this->caja->monto_final, 2), format_money($this->caja->monto_final * $tasaCambio, 2)]
            ];

            foreach ($datos as $fila) {
                $sheet->setCellValue('A' . $row, $fila[0]);
                $sheet->setCellValue('B' . $row, $fila[1]);
                $sheet->setCellValue('C' . $row, $fila[2]);
                if ($fila[0] !== 'Concepto') {
                    $sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                $row++;
            }

            // Detalle de pagos mejorado
            $row += 2;
            $sheet->setCellValue('A' . $row, 'DETALLE DE TRANSACCIONES');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF388E3C');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;
            $encabezados = ['Documento', 'Paciente/Cliente', 'Tipo', 'Método', 'Monto USD', 'Monto ' . $codigoMoneda, 'Referencia', 'Hora'];
            foreach ($encabezados as $col => $encabezado) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row, $encabezado);
            }
            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFC8E6C9');

            $row++;
            $pagosAprobados = $this->caja->pagos()->where('estado', 'aprobado')->get();

            foreach ($pagosAprobados as $pago) {
                $paciente = $pago->consulta && $pago->consulta->paciente
                    ? $pago->consulta->paciente->nombre_completo
                    : ($pago->clienteFiscal ? $pago->clienteFiscal->razon_social : 'N/A');

                // Verificar si tiene nota de crédito
                $tieneNotaCredito = $pago->notasCredito()->where('estado', 'aprobado')->exists();

                // Determinar tipo de transacción
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
                        $montoBolivares = isset($detalle['monto_bs'])
                            ? format_money($detalle['monto_bs'], 2)
                            : '-';

                        $sheet->setCellValue('A' . $row, $pago->numero_completo . ($tieneNotaCredito ? ' (ANULADA)' : ''));
                        $sheet->setCellValue('B' . $row, $paciente);
                        $sheet->setCellValue('C' . $row, $tipo);
                        $sheet->setCellValue('D' . $row, $this->getNombreAmigableMetodoPago($detalle['metodo']));
                        $sheet->setCellValue('E' . $row, format_money($detalle['monto_usd'] ?? $detalle['monto'] ?? 0, 2));
                        $sheet->setCellValue('F' . $row, $montoBolivares);
                        $sheet->setCellValue('G' . $row, $detalle['referencia'] ?? '-');
                        $sheet->setCellValue('H' . $row, $pago->created_at->format('H:i:s'));

                        // Aplicar color rojo si tiene nota de crédito
                        if ($tieneNotaCredito) {
                            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setStrikethrough(true);
                        }

                        $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN);

                        $row++;
                    }
                } else {
                    $montoBolivares = $pago->total_bs ? format_money($pago->total_bs, 2) : '-';

                    $sheet->setCellValue('A' . $row, $pago->numero_completo . ($tieneNotaCredito ? ' (ANULADA)' : ''));
                    $sheet->setCellValue('B' . $row, $paciente);
                    $sheet->setCellValue('C' . $row, $tipo);
                    $sheet->setCellValue('D' . $row, $this->getNombreAmigableMetodoPago($pago->metodo_pago));
                    $sheet->setCellValue('E' . $row, format_money($pago->total_usd ?? $pago->total, 2));
                    $sheet->setCellValue('F' . $row, $montoBolivares);
                    $sheet->setCellValue('G' . $row, $pago->referencia ?? '-');
                    $sheet->setCellValue('H' . $row, $pago->created_at->format('H:i:s'));

                    // Aplicar color rojo si tiene nota de crédito
                    if ($tieneNotaCredito) {
                        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setStrikethrough(true);
                    }

                    $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    $row++;
                }
            }

            // Aplicar estilos y auto-ajuste
            $lastCol = 'H';
            $sheet->getStyle('A1:' . $lastCol . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Guardar en archivo temporal
            $filename = 'cierre_caja_' . $this->caja->fecha->format('Y-m-d') . '_' . $this->caja->numero_corte . '_' . time() . '.xlsx';
            $tempPath = storage_path('app/temp/' . $filename);

            // Crear directorio si no existe
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);

            return $tempPath;

        } catch (\Exception $e) {
            \Log::error('Error al generar Excel temporal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener nombre amigable del método de pago
     * Usa MetodoPagoHelper para mantener consistencia en todo el sistema
     */
    private function getNombreAmigableMetodoPago(string $metodo): string
    {
        return MetodoPagoHelper::getNombreAmigable($metodo);
    }

    /**
     * Enviar mensaje WhatsApp con archivo adjunto
     */
    private function enviarWhatsAppConArchivo($telefono, $mensaje, $rutaArchivo)
    {
        try {
            // Obtener token JWT
            $jwtToken = config('whatsapp.api_key', 'test-api-key-vargas-centro');

            // Formatear número de teléfono
            $telefonoFormateado = $this->formatPhoneNumber($telefono);

            // Verificar que el archivo existe
            if (!file_exists($rutaArchivo)) {
                \Log::error('Archivo no encontrado para enviar por WhatsApp: ' . $rutaArchivo);
                return false;
            }

            // Preparar mensaje completo con el caption
            $caption = "✅ *CIERRE DE CAJA EXITOSO* ✅\n\n";
            $caption .= "📊 *Resumen del cierre:*\n";
            $caption .= "• Fecha: " . $this->caja->fecha->format('d/m/Y') . "\n";
            $caption .= "• Sucursal: " . $this->caja->sucursal->nombre . "\n";
            $caption .= "• Usuario: " . $this->caja->usuario->name . "\n";
            $caption .= "• Monto Inicial: $" . format_money($this->caja->monto_inicial, 2) . "\n";
            $caption .= "• Total Ingresos: $" . format_money($this->caja->total_ingresos, 2) . "\n";
            $caption .= "• Monto Final: $" . format_money($this->caja->monto_final, 2) . "\n\n";
            $caption .= "📎 Reporte detallado adjunto.";

            // Enviar solo el documento con el mensaje como caption
            $nombreArchivo = basename($rutaArchivo);
            $responseDoc = Http::withHeaders([
                'X-API-Key' => $jwtToken
            ])->attach(
                'document', file_get_contents($rutaArchivo), $nombreArchivo
            )->timeout(60)->post(config('whatsapp.api_url', 'http://82.165.213.124:8092') . '/api/whatsapp/send-document', [
                'to' => $telefonoFormateado,
                'caption' => $caption
            ]);

            if ($responseDoc->successful()) {
                \Log::info('Documento Excel enviado exitosamente por WhatsApp', [
                    'caja_id' => $this->caja->id,
                    'phone' => $telefono,
                    'filename' => $nombreArchivo
                ]);
                return true;
            } else {
                \Log::error('Error al enviar documento WhatsApp: ' . $responseDoc->body(), [
                    'caja_id' => $this->caja->id,
                    'phone' => $telefono,
                    'status' => $responseDoc->status(),
                    'response' => $responseDoc->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('Error al enviar WhatsApp con archivo: ' . $e->getMessage(), [
                'caja_id' => $this->caja->id,
                'phone' => $telefono,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Formatear número de teléfono para WhatsApp
     */
    private function formatPhoneNumber($phone)
    {
        // Obtener la empresa y su país
        $empresa = auth()->user()->empresa;
        if (!$empresa) {
            $empresa = \DB::table('empresas')->first();
        }

        // Obtener código de país
        $codigoPais = '58'; // Default Venezuela
        if ($empresa && $empresa->pais_id) {
            $pais = \DB::table('pais')->where('id', $empresa->pais_id)->first();
            if ($pais && $pais->codigo_telefonico) {
                $codigoPais = ltrim($pais->codigo_telefonico, '+');
            }
        }

        // Limpiar número
        $cleaned = preg_replace('/\D/', '', $phone);

        // Quitar el 0 inicial si existe
        if (str_starts_with($cleaned, '0')) {
            $cleaned = substr($cleaned, 1);
        }

        // Agregar código de país si no lo tiene
        if (strlen($cleaned) === 10 && !str_starts_with($cleaned, $codigoPais)) {
            $cleaned = $codigoPais . $cleaned;
        }

        // Agregar sufijo de WhatsApp
        return $cleaned . '@s.whatsapp.net';
    }
}
