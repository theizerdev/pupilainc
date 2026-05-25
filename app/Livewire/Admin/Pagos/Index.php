<?php

namespace App\Livewire\Admin\Pagos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pago;
use App\Traits\HasDynamicLayout;
use Codedge\Fpdf\Fpdf\Fpdf;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $estado = '';
    public $metodo_pago = '';
    public $tipo_pago = '';
    public $showPreview = false;
    public $previewPagoId;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'metodo_pago' => ['except' => ''],
        'tipo_pago' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingMetodoPago()
    {
        $this->resetPage();
    }

    public function updatingTipoPago()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'estado', 'metodo_pago', 'tipo_pago']);
        $this->resetPage();
    }

    public function printReceipt(Pago $pago)
    {
        $this->previewPagoId = $pago->id;
        $this->showPreview = true;
    }

    public function downloadReceipt($pagoId, $formato = 'letter')
    {
        $pago = Pago::with([
            'consulta.paciente', 'consulta.medico',
            'detalles.baremo',
            'ventasProductos.producto',
            'empresa',
            'clienteFiscal',
            'pagoOrigen',
        ])->findOrFail($pagoId);

        // Determinar qué tipo de documento es
        if ($pago->tipo_pago === 'nota_credito') {
            return $this->downloadNotaCredito($pagoId, $formato);
        } elseif ($pago->tipo_pago === 'nota_debito') {
            return $this->downloadNotaDebito($pagoId, $formato);
        }

        // Factura normal
        if ($formato === 'a4') {
            $pdf = new Fpdf('P', 'mm', 'A4');
            $pdf->AddPage();
            $this->generateFacturaA4($pdf, $pago);
        } else {
            $pdf = new Fpdf('P', 'mm', 'Letter');
            $pdf->AddPage();
            $pageHeight = 279.4;
            $halfPage = $pageHeight / 2;
            $this->generateFacturaMediaCarta($pdf, $pago, 'ORIGINAL', 5);
            $pdf->Line(10, $halfPage, 205, $halfPage);
            $this->generateFacturaMediaCarta($pdf, $pago, 'COPIA', $halfPage + 5);
        }

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura_' . $pago->numero_completo . '.pdf"'
        ]);
    }

    private function generateFacturaA4(Fpdf $pdf, Pago $pago)
    {
        $empresa = $pago->empresa;
        $pais = $empresa->pais ?? null;
        $esVenezuela = $pais && $pais->moneda_principal === 'VES';
        $tasaCambio = $esVenezuela ? (float) ($pago->tasa_cambio_usd ?? 1) : 1;

        // Determinar moneda y símbolos
        $monedaPrincipal = $esVenezuela ? 'Bs' : 'USD';
        $simboloMoneda = $esVenezuela ? 'Bs' : '$';

        // Determinar etiqueta de ID fiscal según país
        $labelIdFiscal = match($pais->codigo_iso ?? '') {
            'VE' => 'RIF',
            'MX' => 'RFC',
            'CO' => 'NIT',
            'AR' => 'CUIT',
            'ES' => 'NIF',
            default => 'ID Fiscal'
        };

        // Separadores de formato numérico
        $decimalSep = ',';
        $thousandsSep = '.';

        // Colores para el diseño
        $colorPrimario = [41, 128, 185];    // Azul profesional
        $colorSecundario = [44, 62, 80];     // Gris oscuro
        $colorExito = [39, 174, 96];         // Verde
        $colorFondoClaro = [248, 249, 250];  // Gris muy claro
        $colorBorde = [222, 226, 230];       // Borde suave

        // ══════════════════════════════════════════════════════════
        // SECCIÓN 1: ENCABEZADO CON BRANDING
        // ══════════════════════════════════════════════════════════

        // Logo de la empresa (si existe)

            try {
                $logoPath = public_path('logo/logo-angeles.png');
                if (file_exists($logoPath)) {
                    $pdf->Image($logoPath, 10, 10, 60, 0);
                    $pdf->SetX(45);
                }
            } catch (\Exception $e) {
                // Si falla el logo, continuar sin él
            }


        // Información de la empresa
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
        $pdf->Cell(0, 8, utf8_decode(strtoupper($empresa->razon_social ?? 'EMPRESA')), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, utf8_decode($labelIdFiscal . ': ' . ($empresa->rif_fiscal ?? 'N/A')), 0, 1, 'R');

        // Dirección en múltiples líneas si es necesario
        $direccion = utf8_decode($empresa->direccion_fiscal_completa ?? $empresa->direccion ?? '');
        $pdf->MultiCell(0, 4, $direccion, 0, 'R');

        $pdf->Cell(0, 4, 'Tel: ' . ($empresa->telefono ?? '') . ' | Email: ' . ($empresa->correo_fiscal ?? ''), 0, 1, 'R');

        $pdf->Ln(5);

        // Línea divisoria con color
        $pdf->SetDrawColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════
        // SECCIÓN 2: INFORMACIÓN DEL DOCUMENTO
        // ══════════════════════════════════════════════════════════

        // Tipo de documento destacado
        $tipoDoc = $pago->es_factura_fiscal ? 'FACTURA' : strtoupper($pago->tipo_pago);
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->Cell(0, 10, utf8_decode($tipoDoc), 0, 1, 'L');

        // Número de factura
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
        $pdf->Cell(0, 6, 'N° ' . $pago->numero_completo, 0, 1, 'L');

        if ($pago->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->Cell(0, 4, 'Control Fiscal: ' . $pago->numero_control_fiscal, 0, 1, 'L');
        }

        $pdf->Ln(5);

        // Caja de información del cliente
        $yInicioCliente = $pdf->GetY();
        $pdf->SetFillColor($colorFondoClaro[0], $colorFondoClaro[1], $colorFondoClaro[2]);
        $pdf->SetDrawColor($colorBorde[0], $colorBorde[1], $colorBorde[2]);
        $pdf->Rect(10, $yInicioCliente, 95, 45, 'DF');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->Text(15, $yInicioCliente + 6, 'CLIENTE');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);

        $yOffset = $yInicioCliente + 12;
        if ($pago->clienteFiscal) {
            $cliente = $pago->clienteFiscal;
            $pdf->Text(15, $yOffset, utf8_decode('Razón Social:'));
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Text(50, $yOffset, utf8_decode(substr($cliente->razon_social ?? $cliente->nombre ?? '', 0, 45)));
            $pdf->SetFont('Arial', '', 8);

            $pdf->Text(15, $yOffset + 5, $labelIdFiscal . '/CI:');
            $pdf->Text(50, $yOffset + 5, $cliente->documento_completo);

            $pdf->Text(15, $yOffset + 10, 'Teléfono:');
            $pdf->Text(50, $yOffset + 10, $cliente->telefono ?? 'N/A');

            $pdf->Text(15, $yOffset + 15, 'Email:');
            $pdf->Text(50, $yOffset + 15, substr($cliente->email ?? '', 0, 40));

            $pdf->Text(15, $yOffset + 20, utf8_decode('Dirección:'));
            $pdf->SetFont('Arial', '', 7);
            $pdf->MultiCell(70, 3.5, utf8_decode(substr($cliente->direccion_fiscal ?? 'N/A', 0, 80)), 0, 'L');
        } elseif ($pago->consulta && $pago->consulta->paciente) {
            $paciente = $pago->consulta->paciente;
            $pdf->Text(15, $yOffset, 'Nombre:');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Text(50, $yOffset, utf8_decode(substr($paciente->nombre_completo, 0, 45)));
            $pdf->SetFont('Arial', '', 8);

            $pdf->Text(15, $yOffset + 5, 'Cédula/ID:');
            $pdf->Text(50, $yOffset + 5, $paciente->documento_identidad ?? 'N/A');

            $pdf->Text(15, $yOffset + 10, 'Teléfono:');
            $pdf->Text(50, $yOffset + 10, $paciente->telefono ?? 'N/A');

            $pdf->Text(15, $yOffset + 15, utf8_decode('Dirección:'));
            $pdf->SetFont('Arial', '', 7);
            $pdf->MultiCell(70, 3.5, utf8_decode(substr($paciente->direccion ?? 'N/A', 0, 80)), 0, 'L');
        }

        // Caja de información de la transacción
        $pdf->SetFillColor($colorFondoClaro[0], $colorFondoClaro[1], $colorFondoClaro[2]);
        $pdf->Rect(110, $yInicioCliente, 90, 45, 'DF');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->Text(115, $yInicioCliente + 6, 'DETALLES');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);

        $pdf->Text(115, $yInicioCliente + 12, 'Fecha:');
        $pdf->Text(145, $yInicioCliente + 12, $pago->fecha->format('d/m/Y'));

        $pdf->Text(115, $yInicioCliente + 17, utf8_decode('Condición:'));
        $pdf->Text(145, $yInicioCliente + 17, strtoupper($pago->condicion_pago ?? 'CONTADO'));

        $pdf->Text(115, $yInicioCliente + 22, utf8_decode('Método Pago:'));
        $pdf->Text(145, $yInicioCliente + 22, strtoupper(str_replace('_', ' ', $pago->metodo_pago)));

        if ($esVenezuela && $tasaCambio > 1) {
            $pdf->Text(115, $yInicioCliente + 27, 'Tasa BCV:');
            $pdf->Text(145, $yInicioCliente + 27, $monedaPrincipal . ' ' . number_format($tasaCambio, 2, $decimalSep, $thousandsSep));
        }

        $pdf->Ln(50);

        // ══════════════════════════════════════════════════════════
        // SECCIÓN 3: DETALLE DE ITEMS (TABLA PROFESIONAL)
        // ══════════════════════════════════════════════════════════

        // Encabezado de tabla con fondo de color
        $pdf->SetFillColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);

        $yTabla = $pdf->GetY();
        $pdf->Cell(10, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(90, 7, utf8_decode('DESCRIPCIÓN'), 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'CANT.', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'P. UNIT. (' . $monedaPrincipal . ')', 1, 0, 'R', true);
        $pdf->Cell(35, 7, 'TOTAL (' . $monedaPrincipal . ')', 1, 1, 'R', true);

        // Filas de datos
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        $filaAlterna = false;

        // Servicios/baremos
        foreach ($pago->detalles as $detalle) {
            $precioUnit = $esVenezuela ? (float) $detalle->precio_unitario * $tasaCambio : (float) $detalle->precio_unitario;
            $subtotal   = $esVenezuela ? (float) $detalle->subtotal * $tasaCambio : (float) $detalle->subtotal;

            // Fondo alternado para mejor legibilidad
            if ($filaAlterna) {
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(10, 6, $item, 1, 0, 'C', true);
                $pdf->Cell(90, 6, substr(utf8_decode($detalle->descripcion), 0, 55), 1, 0, 'L', true);
                $pdf->Cell(20, 6, number_format((float) $detalle->cantidad, 2, $decimalSep, $thousandsSep), 1, 0, 'C', true);
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($precioUnit, 2, $decimalSep, $thousandsSep), 1, 0, 'R', true);
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($subtotal, 2, $decimalSep, $thousandsSep), 1, 1, 'R', true);
            } else {
                $pdf->Cell(10, 6, $item, 1, 0, 'C');
                $pdf->Cell(90, 6, substr(utf8_decode($detalle->descripcion), 0, 55), 1, 0, 'L');
                $pdf->Cell(20, 6, number_format((float) $detalle->cantidad, 2, $decimalSep, $thousandsSep), 1, 0, 'C');
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($precioUnit, 2, $decimalSep, $thousandsSep), 1, 0, 'R');
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($subtotal, 2, $decimalSep, $thousandsSep), 1, 1, 'R');
            }

            $item++;
            $filaAlterna = !$filaAlterna;
        }

        // Productos vendidos
        foreach ($pago->ventasProductos as $venta) {
            $precioUnit = $esVenezuela ? (float) $venta->precio_unitario * $tasaCambio : (float) $venta->precio_unitario;
            $subtotal   = $esVenezuela ? (float) $venta->subtotal * $tasaCambio : (float) $venta->subtotal;
            $nombre = $venta->producto->nombre ?? 'Producto';

            if ($filaAlterna) {
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(10, 6, $item, 1, 0, 'C', true);
                $pdf->Cell(90, 6, substr(utf8_decode($nombre), 0, 55), 1, 0, 'L', true);
                $pdf->Cell(20, 6, number_format((int) $venta->cantidad, 0, $decimalSep, $thousandsSep), 1, 0, 'C', true);
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($precioUnit, 2, $decimalSep, $thousandsSep), 1, 0, 'R', true);
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($subtotal, 2, $decimalSep, $thousandsSep), 1, 1, 'R', true);
            } else {
                $pdf->Cell(10, 6, $item, 1, 0, 'C');
                $pdf->Cell(90, 6, substr(utf8_decode($nombre), 0, 55), 1, 0, 'L');
                $pdf->Cell(20, 6, number_format((int) $venta->cantidad, 0, $decimalSep, $thousandsSep), 1, 0, 'C');
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($precioUnit, 2, $decimalSep, $thousandsSep), 1, 0, 'R');
                $pdf->Cell(35, 6, $simboloMoneda . ' ' . number_format($subtotal, 2, $decimalSep, $thousandsSep), 1, 1, 'R');
            }

            $item++;
            $filaAlterna = !$filaAlterna;
        }

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════
        // SECCIÓN 4: RESUMEN EJECUTIVO (TOTALES DESTACADOS)
        // ══════════════════════════════════════════════════════════

        $yTotales = $pdf->GetY();

        // Cálculo de totales
        $subtotal = $esVenezuela ? (float) $pago->subtotal * $tasaCambio : (float) $pago->subtotal;
        $exento = $esVenezuela ? (float) ($pago->monto_exento ?? 0) * $tasaCambio : (float) ($pago->monto_exento ?? 0);
        $baseImp = $esVenezuela ? (float) ($pago->base_imponible ?? 0) * $tasaCambio : (float) ($pago->base_imponible ?? 0);
        $ivaMonto = $esVenezuela ? (float) ($pago->iva_monto ?? 0) * $tasaCambio : (float) ($pago->iva_monto ?? 0);
        $igtfMonto = $esVenezuela ? (float) ($pago->igtf_monto ?? 0) * $tasaCambio : (float) ($pago->igtf_monto ?? 0);
        $totalMostrar = $esVenezuela ? (float) $pago->total_bs : (float) $pago->total_usd;

        // Panel de totales con fondo
        $pdf->SetFillColor($colorFondoClaro[0], $colorFondoClaro[1], $colorFondoClaro[2]);
        $pdf->SetDrawColor($colorBorde[0], $colorBorde[1], $colorBorde[2]);
        $pdf->Rect(120, $yTotales, 70, 50, 'DF');

        // Subtotal
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
        $pdf->Text(125, $yTotales + 7, 'Subtotal:');
        $pdf->Text(170, $yTotales + 7, $simboloMoneda . ' ' . number_format($subtotal, 2, $decimalSep, $thousandsSep));

        // Exento (si aplica)
        if ($exento > 0) {
            $pdf->Text(125, $yTotales + 12, 'Exento:');
            $pdf->Text(170, $yTotales + 12, $simboloMoneda . ' ' . number_format($exento, 2, $decimalSep, $thousandsSep));
        }

        // Base imponible e IVA (si aplica)
        if ($baseImp > 0) {
            $ivaPct = $pago->iva_porcentaje ?? 16;
            $pdf->Text(125, $yTotales + 17, "Base Imp. ({$ivaPct}%):");
            $pdf->Text(170, $yTotales + 17, $simboloMoneda . ' ' . number_format($baseImp, 2, $decimalSep, $thousandsSep));

            $pdf->Text(125, $yTotales + 22, "IVA ({$ivaPct}%):");
            $pdf->Text(170, $yTotales + 22, $simboloMoneda . ' ' . number_format($ivaMonto, 2, $decimalSep, $thousandsSep));
        }

        // IGTF (solo Venezuela)
        if ($esVenezuela && $igtfMonto > 0) {
            $igtfPct = $pago->igtf_porcentaje ?? 3;
            $pdf->Text(125, $yTotales + 27, "IGTF ({$igtfPct}%):");
            $pdf->Text(170, $yTotales + 27, $simboloMoneda . ' ' . number_format($igtfMonto, 2, $decimalSep, $thousandsSep));
        }

        // Línea separadora
        $pdf->SetDrawColor($colorBorde[0], $colorBorde[1], $colorBorde[2]);
        $pdf->Line(125, $yTotales + 30, 185, $yTotales + 30);

        // TOTAL DESTACADO
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
        $pdf->Text(125, $yTotales + 38, 'TOTAL:');
        $pdf->Text(165, $yTotales + 38, $simboloMoneda . ' ' . number_format($totalMostrar, 2, $decimalSep, $thousandsSep));

        // Equivalente USD para Venezuela
        if ($esVenezuela) {
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Text(125, $yTotales + 44, 'Equivalente USD:');
            $pdf->Text(165, $yTotales + 44, '$ ' . number_format((float) $pago->total_usd, 2, '.', ','));
        }

        $pdf->Ln(55);

        // ══════════════════════════════════════════════════════════
        // SECCIÓN 5: INFORMACIÓN ADICIONAL Y VALIDACIÓN
        // ══════════════════════════════════════════════════════════

        // Mensaje para facturas no fiscales
        if (!$pago->es_factura_fiscal) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(231, 76, 60); // Rojo para advertencia
            $mensajeNoFiscal = $esVenezuela ? 'SIN DERECHO A CRÉDITO FISCAL' : 'NO TAX INVOICE';
            $pdf->Cell(0, 6, utf8_decode($mensajeNoFiscal), 0, 1, 'C');
            $pdf->Ln(2);
        }

        // Coletilla IGTF a crédito (solo Venezuela)
        if ($esVenezuela && in_array(strtoupper($pago->condicion_pago ?? ''), ['CREDITO', 'CRÉDITO'])) {
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(0, 3, utf8_decode('El pago total o parcial de esta factura en moneda diferente a Bs causará el 3% de IGTF adicional al monto total indicado.'), 0, 'J');
            $pdf->Ln(2);
        }

        // Notas adicionales
        if (!empty($pago->observaciones)) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
            $pdf->Cell(0, 5, 'Notas:', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 7);
            $pdf->MultiCell(0, 4, utf8_decode($pago->observaciones), 0, 'L');
            $pdf->Ln(2);
        }

        // Código QR de validación (placeholder - se puede implementar con librería QR)
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 4, 'Código de validación: ' . substr(md5($pago->numero_completo . $pago->total_usd), 0, 16), 0, 1, 'L');

        $pdf->Ln(5);

        // Firma autorizada
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
        $pdf->Cell(0, 4, '________________________________________', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Firma y Sello Autorizado', 0, 1, 'C');

        // Pie de página
        $pdf->SetY(-20);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 4, utf8_decode('Documento generado electrónicamente - ') . date('d/m/Y H:i:s'), 0, 1, 'C');
    }

    private function generateFacturaMediaCarta(Fpdf $pdf, Pago $pago, $tipo, $yPosition)
    {
        $pdf->SetY($yPosition);
        $empresa = $pago->empresa;

        // ENCABEZADO
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->razon_social ?? 'EMPRESA')), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode('RIF: ' . ($empresa->rif_fiscal ?? 'J-00000000-0')), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 3, utf8_decode(substr($empresa->direccion_fiscal ?? $empresa->direccion ?? '', 0, 80)), 0, 1, 'C');
        $pdf->Cell(0, 3, 'Telf: ' . ($empresa->telefono ?? 'N/A'), 0, 1, 'C');

        if ($tipo) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 4, $tipo, 0, 1, 'C');
        }

        $pdf->Ln(1);
        $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY());
        $pdf->Ln(2);

        // TIPO Y NÚMERO
        $pdf->SetFont('Arial', 'B', 10);
        $tipoDoc = strtoupper($pago->tipo_pago);
        if ($pago->es_factura_fiscal) {
            $tipoDoc = 'FACTURA';
        }
        $pdf->Cell(0, 4, utf8_decode($tipoDoc . ' N° ' . $pago->numero_completo), 0, 1, 'C');

        if ($pago->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 3, utf8_decode('Control: ' . $pago->numero_control_fiscal), 0, 1, 'C');
        }

        $pdf->Ln(2);

        // DATOS CLIENTE
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, 'DATOS DEL CLIENTE', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);

        if ($pago->clienteFiscal) {
            $cliente = $pago->clienteFiscal;
            $pdf->Cell(30, 3, utf8_decode('Razón Social:'), 0, 0, 'L');
            $pdf->Cell(0, 3, utf8_decode(substr($cliente->razon_social, 0, 60)), 0, 1, 'L');
            $pdf->Cell(30, 3, 'RIF/CI:', 0, 0, 'L');
            $pdf->Cell(0, 3, $cliente->documento_completo, 0, 1, 'L');
            $pdf->Cell(30, 3, utf8_decode('Dirección:'), 0, 0, 'L');
            $pdf->Cell(0, 3, utf8_decode(substr($cliente->direccion ?? 'N/A', 0, 65)), 0, 1, 'L');
        } elseif ($pago->consulta && $pago->consulta->paciente) {
            $paciente = $pago->consulta->paciente;
            $pdf->Cell(30, 3, 'Nombre:', 0, 0, 'L');
            $pdf->Cell(0, 3, utf8_decode(substr($paciente->nombre_completo, 0, 60)), 0, 1, 'L');
            $pdf->Cell(30, 3, 'CI:', 0, 0, 'L');
            $pdf->Cell(0, 3, $paciente->documento_identidad ?? 'N/A', 0, 1, 'L');
            $pdf->Cell(30, 3, utf8_decode('Dirección:'), 0, 0, 'L');
            $pdf->Cell(0, 3, utf8_decode(substr($paciente->direccion ?? 'N/A', 0, 65)), 0, 1, 'L');
        }

        $pdf->Cell(30, 3, 'Fecha:', 0, 0, 'L');
        $pdf->Cell(60, 3, $pago->fecha->format('d/m/Y'), 0, 0, 'L');
        $pdf->Cell(25, 3, utf8_decode('Condición:'), 0, 0, 'L');
        $pdf->Cell(0, 3, strtoupper($pago->condicion_pago ?? 'CONTADO'), 0, 1, 'L');

        $pdf->Cell(30, 3, utf8_decode('Método Pago:'), 0, 0, 'L');
        $pdf->Cell(0, 3, strtoupper(str_replace('_', ' ', $pago->metodo_pago)), 0, 1, 'L');

        $pdf->Ln(2);

        // DETALLES
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(10, 4, 'Item', 1, 0, 'C');
        $pdf->Cell(90, 4, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(18, 4, 'Cant.', 1, 0, 'C');
        $pdf->Cell(30, 4, 'P.Unit (Bs)', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 6);
        $item = 1;
        foreach ($pago->detalles as $detalle) {
            $pdf->Cell(10, 4, $item++, 1, 0, 'C');
            $pdf->Cell(90, 4, substr(utf8_decode($detalle->descripcion), 0, 55), 1, 0, 'L');
            $pdf->Cell(18, 4, format_money($detalle->cantidad, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell(30, 4, format_money($detalle->precio_unitario, 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(37, 4, format_money($detalle->subtotal, 2, ',', '.'), 1, 1, 'R');
        }

        // TOTALES (Orden: Subtotal, Exento, Base Imp., IVA, Total)
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(148, 4, 'SUBTOTAL:', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Bs ' . format_money($pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd), 2, ',', '.'), 1, 1, 'R');

        $pdf->Cell(148, 4, 'EXENTO:', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Bs ' . format_money($pago->monto_exento ?? 0, 2, ',', '.'), 1, 1, 'R');

        // Calcular monto en divisas para base imponible IGTF (media carta)
        $montoEnDivisasMedia = 0;
        if ($pago->metodo_pago === 'mixto' && ($pago->pagos_mixtos || $pago->detalles_pago_mixto)) {
            $pagosMixtosArray = json_decode($pago->pagos_mixtos, true) ?? $pago->detalles_pago_mixto ?? [];
            foreach ($pagosMixtosArray as $pm) {
                if (in_array($pm['metodo'] ?? '', ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                    $montoEnDivisasMedia += ($pm['monto_bs'] ?? 0) + (($pm['monto_usd'] ?? 0) * $pago->tasa_cambio_usd);
                }
            }
        } elseif (in_array($pago->metodo_pago, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
            $montoEnDivisasMedia = ($pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd)) + ($pago->iva_monto ?? 0);
        }
        $baseImponibleMostrarMedia = $montoEnDivisasMedia > 0 ? $montoEnDivisasMedia : ($pago->base_imponible ?? 0);

        $pdf->Cell(148, 4, 'BASE IMP. (IGTF):', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Bs ' . format_money($baseImponibleMostrarMedia, 2, ',', '.'), 1, 1, 'R');

        $ivaPorcentaje = $pago->iva_porcentaje ?? 16;
        $pdf->Cell(148, 4, "IVA ({$ivaPorcentaje}%):", 1, 0, 'R');
        $pdf->Cell(37, 4, 'Bs ' . format_money($pago->iva_monto ?? 0, 2, ',', '.'), 1, 1, 'R');


        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(148, 5, 'TOTAL:', 1, 0, 'R');
        $pdf->Cell(37, 5, 'Bs ' . format_money($pago->total_bs, 2, ',', '.'), 1, 1, 'R');

        // Solo mostrar USD si NO es factura fiscal
        if (!$pago->es_factura_fiscal) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(148, 3, 'USD:', 0, 0, 'R');
            $pdf->Cell(37, 3, '$ ' . format_money($pago->total_usd, 2), 0, 1, 'R');
            $pdf->Cell(148, 3, 'Tasa:', 0, 0, 'R');
            $pdf->Cell(37, 3, 'Bs ' . format_money($pago->tasa_cambio_usd, 2, ',', '.'), 0, 1, 'R');

            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(0, 4, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }

        // Coletilla IGTF solo si es a crédito
        if (strtoupper($pago->condicion_pago ?? 'CONTADO') === 'CREDITO' || strtoupper($pago->condicion_pago ?? 'CONTADO') === 'CRÉDITO') {
            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'I', 6);
            $texto = 'El pago total o parcial de esta factura en moneda diferente a Bs' . chr(10) . 'causará el 3% de IGTF adicional al monto total indicado.';
            $pdf->MultiCell(0, 2.5, utf8_decode($texto), 0, 'J');
        }

        $pdf->Ln(3);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, '___________________________', 0, 1, 'C');
        $pdf->Cell(0, 3, 'Firma Autorizada', 0, 1, 'C');
    }

    public function downloadNotaCredito($pagoId, $formato = 'letter')
    {
        $nota = Pago::with(['pagoOrigen', 'pagoOrigen.consulta.paciente', 'pagoOrigen.clienteFiscal', 'detalles.baremo', 'empresa', 'clienteFiscal'])->findOrFail($pagoId);

        if ($formato === 'a4') {
            $pdf = new Fpdf('P', 'mm', 'A4');
            $pdf->AddPage();
            $this->generateNotaCreditoA4($pdf, $nota);
        } else {
            $pdf = new Fpdf('P', 'mm', 'Letter');
            $pdf->AddPage();
            $pageHeight = 279.4;
            $halfPage = $pageHeight / 2;
            $this->generateNotaCreditoMediaCarta($pdf, $nota, 'ORIGINAL', 5);
            $pdf->Line(10, $halfPage, 205, $halfPage);
            $this->generateNotaCreditoMediaCarta($pdf, $nota, 'COPIA', $halfPage + 5);
        }

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="nota_credito_' . $nota->numero_completo . '.pdf"'
        ]);
    }

    public function downloadNotaDebito($pagoId, $formato = 'letter')
    {
        $nota = Pago::with(['pagoOrigen', 'pagoOrigen.consulta.paciente', 'pagoOrigen.clienteFiscal', 'detalles.baremo', 'empresa', 'clienteFiscal'])->findOrFail($pagoId);

        if ($formato === 'a4') {
            $pdf = new Fpdf('P', 'mm', 'A4');
            $pdf->AddPage();
            $this->generateNotaDebitoA4($pdf, $nota);
        } else {
            $pdf = new Fpdf('P', 'mm', 'Letter');
            $pdf->AddPage();
            $pageHeight = 279.4;
            $halfPage = $pageHeight / 2;
            $this->generateNotaDebitoMediaCarta($pdf, $nota, 'ORIGINAL', 5);
            $pdf->Line(10, $halfPage, 205, $halfPage);
            $this->generateNotaDebitoMediaCarta($pdf, $nota, 'COPIA', $halfPage + 5);
        }

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="nota_debito_' . $nota->numero_completo . '.pdf"'
        ]);
    }

    private function generateNotaCreditoA4(Fpdf $pdf, Pago $nota)
    {
        $facturaOriginal = $nota->pagoOrigen;

        // Espacio para encabezado preimpreso
        $pdf->Ln(40);

        // FACTURA ASOCIADA
        $pdf->SetFillColor(255, 240, 240);
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 5, 'Factura Asociada:', 'LT', 0, 'L');
        $pdf->Cell(70, 5, $facturaOriginal->numero_completo, 'T', 0, 'L');
        $pdf->Cell(30, 5, 'Fecha de emision:', 'T', 0, 'L');
        $pdf->Cell(0, 5, $nota->fecha->format('d/m/Y'), 'RT', 1, 'L');

        $pdf->Cell(50, 5, 'Monto Original:', 'L', 0, 'L');
        $pdf->Cell(70, 5, 'Bs ' . format_money($facturaOriginal->total_bs, 2, ',', '.'), 0, 0, 'L');
        $pdf->Cell(30, 5, 'Fecha de la factura', 0, 0, 'L');
        $pdf->Cell(0, 5, $facturaOriginal->fecha->format('d/m/Y'), 'R', 1, 'L');

        $pdf->Cell(50, 5, 'NOTA DE CREDITO NRO:', 'LB', 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(0, 5, utf8_decode($nota->numero_completo), 'RB', 'L');

        $pdf->Ln(3);

        // DATOS DEL CLIENTE
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DATOS DEL CLIENTE', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        if ($nota->clienteFiscal) {
            $cliente = $nota->clienteFiscal;
            $pdf->Cell(50, 5, utf8_decode('Razón Social:'), 'LT', 0, 'L');
            $pdf->Cell(0, 5, utf8_decode($cliente->razon_social), 'RT', 1, 'L');
            $pdf->Cell(50, 5, 'RIF/CI:', 'LB', 0, 'L');
            $pdf->Cell(0, 5, $cliente->documento_completo, 'RB', 1, 'L');
        } elseif ($nota->consulta && $nota->consulta->paciente) {
            $paciente = $nota->consulta->paciente;
            $pdf->Cell(50, 5, 'Nombre:', 'LT', 0, 'L');
            $pdf->Cell(0, 5, utf8_decode($paciente->nombre_completo), 'RT', 1, 'L');
            $pdf->Cell(50, 5, 'CI:', 'LB', 0, 'L');
            $pdf->Cell(0, 5, $paciente->documento_identidad, 'RB', 1, 'L');
        }

        $pdf->Ln(3);

        // DETALLE DE SERVICIOS ANULADOS
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DETALLE DE SERVICIOS ANULADOS/DEVUELTOS', 1, 1, 'L', true);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(12, 6, 'Item', 1, 0, 'C');
        $pdf->Cell(85, 6, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(20, 6, 'Cant.', 1, 0, 'C');
        $pdf->Cell(35, 6, 'P. Unit. (Bs)', 1, 0, 'R');
        $pdf->Cell(38, 6, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        foreach ($nota->detalles as $detalle) {
            $pdf->Cell(12, 5, $item++, 1, 0, 'C');
            $pdf->Cell(85, 5, substr(utf8_decode($detalle->descripcion), 0, 50), 1, 0, 'L');
            $pdf->Cell(20, 5, format_money(abs($detalle->cantidad), 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell(35, 5, format_money($detalle->precio_unitario, 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(38, 5, format_money(abs($detalle->subtotal), 2, ',', '.'), 1, 1, 'R');
        }

        $pdf->Ln(2);

        // RESUMEN DE TOTALES - Mostrar montos de la FACTURA ORIGINAL
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'SUBTOTAL:', 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money(abs($facturaOriginal->subtotal_bs ?? ($facturaOriginal->subtotal * $facturaOriginal->tasa_cambio_usd)), 2, ',', '.'), 1, 1, 'R');

        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'EXENTO:', 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money(abs($facturaOriginal->monto_exento ?? 0), 2, ',', '.'), 1, 1, 'R');

        // Calcular monto en divisas de la factura original para BASE IMP. (IGTF)
        $montoEnDivisasOriginal = 0;
        if ($facturaOriginal->metodo_pago === 'mixto' && ($facturaOriginal->pagos_mixtos || $facturaOriginal->detalles_pago_mixto)) {
            $pagosMixtosArray = json_decode($facturaOriginal->pagos_mixtos, true) ?? $facturaOriginal->detalles_pago_mixto ?? [];
            foreach ($pagosMixtosArray as $pm) {
                if (in_array($pm['metodo'] ?? '', ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                    $montoEnDivisasOriginal += ($pm['monto_bs'] ?? 0) + (($pm['monto_usd'] ?? 0) * $facturaOriginal->tasa_cambio_usd);
                }
            }
        } elseif (in_array($facturaOriginal->metodo_pago, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
            $montoEnDivisasOriginal = ($facturaOriginal->subtotal_bs ?? ($facturaOriginal->subtotal * $facturaOriginal->tasa_cambio_usd)) + ($facturaOriginal->iva_monto ?? 0);
        }
        $baseImponibleMostrarOriginal = $montoEnDivisasOriginal > 0 ? $montoEnDivisasOriginal : ($facturaOriginal->base_imponible ?? 0);

        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'BASE IMP. (IGTF):', 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money(abs($baseImponibleMostrarOriginal), 2, ',', '.'), 1, 1, 'R');

        $ivaPorcentaje = $facturaOriginal->iva_porcentaje ?? 16;
        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, "IVA ({$ivaPorcentaje}%):", 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money(abs($facturaOriginal->iva_monto ?? 0), 2, ',', '.'), 1, 1, 'R');

        // Mostrar IGTF si aplica en la factura original
        if (($facturaOriginal->igtf_monto ?? 0) > 0) {
            $pdf->Cell(117, 5, '', 0, 0);
            $pdf->Cell(35, 5, 'IGTF (3%):', 1, 0, 'R');
            $pdf->Cell(38, 5, 'Bs ' . format_money($facturaOriginal->igtf_monto, 2, ',', '.'), 1, 1, 'R');
        }

        // TOTAL A ACREDITAR (de la nota de crédito)
        $pdf->SetTextColor(200, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 7, 'TOTAL:', 1, 0, 'R');
        $totalAcreditar = abs($nota->total_bs ?? 0);
        $pdf->Cell(38, 7, 'Bs ' . format_money($facturaOriginal->total_bs ?? ($facturaOriginal->total * $facturaOriginal->tasa_cambio_usd), 2, ',', '.'), 1, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        if (!$nota->es_factura_fiscal) {
            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 5, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }
    }

    private function generateNotaCreditoMediaCarta(Fpdf $pdf, Pago $nota, $tipo, $yPosition)
    {
        $pdf->SetY($yPosition);
        $empresa = $nota->empresa;
        $facturaOriginal = $nota->pagoOrigen;

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->razon_social)), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode('RIF: ' . $empresa->rif_fiscal), 0, 1, 'C');

        if ($tipo) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 4, $tipo, 0, 1, 'C');
        }

        $pdf->Ln(1);
        $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY());
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(0, 4, utf8_decode('NOTA DE CRÉDITO N° ' . $nota->numero_completo), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);

        if ($nota->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 3, utf8_decode('Control: ' . $nota->numero_control_fiscal), 0, 1, 'C');
        }

        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(30, 3, 'Factura Afectada:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 3, $facturaOriginal->numero_completo, 0, 1, 'L');

        $pdf->SetFont('Arial', 'I', 6);
        $pdf->MultiCell(0, 3, utf8_decode('Motivo: ' . $nota->motivo_nota), 0, 'L');

        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(10, 4, 'Item', 1, 0, 'C');
        $pdf->Cell(90, 4, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(18, 4, 'Cant.', 1, 0, 'C');
        $pdf->Cell(30, 4, 'P.Unit (Bs)', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 6);
        $item = 1;
        foreach ($nota->detalles as $detalle) {
            $pdf->Cell(10, 4, $item++, 1, 0, 'C');
            $pdf->Cell(90, 4, substr(utf8_decode($detalle->descripcion), 0, 55), 1, 0, 'L');
            $pdf->Cell(18, 4, format_money(abs($detalle->cantidad), 2, ',', '.'), 1, 0, 'C');
            $precioUnitBs = $detalle->precio_unitario * $nota->tasa_cambio_usd;
            $pdf->Cell(30, 4, format_money($precioUnitBs, 2, ',', '.'), 1, 0, 'R');
            $subtotalBs = abs($detalle->subtotal * $nota->tasa_cambio_usd);
            $pdf->Cell(37, 4, format_money($subtotalBs, 2, ',', '.'), 1, 1, 'R');
        }

        $pdf->SetTextColor(200, 0, 0);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(148, 5, 'TOTAL:', 1, 0, 'R');
        $pdf->Cell(37, 5, 'Bs ' . format_money(abs($nota->total_bs), 2, ',', '.'), 1, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        if (!$nota->es_factura_fiscal) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(0, 4, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }

        $pdf->Ln(3);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, '___________________________', 0, 1, 'C');
        $pdf->Cell(0, 3, 'Firma Autorizada', 0, 1, 'C');
    }

    private function generateNotaDebitoA4(Fpdf $pdf, Pago $nota)
    {
        $empresa = $nota->empresa;
        $facturaOriginal = $nota->pagoOrigen;

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 8, utf8_decode(strtoupper($empresa->razon_social)), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, utf8_decode('RIF: ' . $empresa->rif_fiscal), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 4, utf8_decode($empresa->direccion_fiscal ?? $empresa->direccion), 0, 'C');

        $pdf->Ln(3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(0, 0, 200);
        $pdf->Cell(0, 7, utf8_decode('NOTA DE DÉBITO'), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, utf8_decode('N° ' . $nota->numero_completo), 0, 1, 'C');

        if ($nota->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode('N° Control Fiscal: ' . $nota->numero_control_fiscal), 0, 1, 'C');
        }

        $pdf->Ln(4);

        $pdf->SetFillColor(240, 240, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DOCUMENTO AFECTADO', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 5, 'Factura N°:', 'LT', 0, 'L');
        $pdf->Cell(70, 5, $facturaOriginal->numero_completo, 'T', 0, 'L');
        $pdf->Cell(30, 5, 'Fecha:', 'T', 0, 'L');
        $pdf->Cell(0, 5, $facturaOriginal->fecha->format('d/m/Y'), 'RT', 1, 'L');

        $pdf->Cell(50, 5, 'Monto Original:', 'L', 0, 'L');
        $pdf->Cell(70, 5, 'Bs ' . format_money($facturaOriginal->total_bs, 2, ',', '.'), 0, 0, 'L');
        $pdf->Cell(30, 5, 'Control:', 0, 0, 'L');
        $pdf->Cell(0, 5, $facturaOriginal->numero_control_fiscal ?? 'N/A', 'R', 1, 'L');

        $pdf->Cell(50, 5, 'Motivo:', 'LB', 0, 'L');
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->MultiCell(0, 5, utf8_decode($nota->motivo_nota), 'RB', 'L');

        $pdf->Ln(3);

        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DATOS DEL CLIENTE', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        if ($nota->clienteFiscal) {
            $cliente = $nota->clienteFiscal;
            $pdf->Cell(50, 5, utf8_decode('Razón Social:'), 'LT', 0, 'L');
            $pdf->Cell(0, 5, utf8_decode($cliente->razon_social), 'RT', 1, 'L');
            $pdf->Cell(50, 5, 'RIF/CI:', 'LB', 0, 'L');
            $pdf->Cell(0, 5, $cliente->documento_completo, 'RB', 1, 'L');
        }

        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DETALLE DE CARGOS ADICIONALES', 1, 1, 'L', true);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(12, 6, 'Item', 1, 0, 'C');
        $pdf->Cell(85, 6, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(20, 6, 'Cant.', 1, 0, 'C');
        $pdf->Cell(35, 6, 'P. Unit. (Bs)', 1, 0, 'R');
        $pdf->Cell(38, 6, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        foreach ($nota->detalles as $detalle) {
            $pdf->Cell(12, 5, $item++, 1, 0, 'C');
            $pdf->Cell(85, 5, substr(utf8_decode($detalle->descripcion), 0, 50), 1, 0, 'L');
            $pdf->Cell(20, 5, format_money($detalle->cantidad, 2, ',', '.'), 1, 0, 'C');
            $precioUnitBs = $detalle->precio_unitario * $nota->tasa_cambio_usd;
            $pdf->Cell(35, 5, format_money($precioUnitBs, 2, ',', '.'), 1, 0, 'R');
            $subtotalBs = $detalle->subtotal * $nota->tasa_cambio_usd;
            $pdf->Cell(38, 5, format_money($subtotalBs, 2, ',', '.'), 1, 1, 'R');
        }

        $pdf->Ln(2);

        // Totales fiscales - Mostrar montos de la FACTURA ORIGINAL
        $labelW = 117;
        $valueW = 73;
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($labelW, 5, 'SUBTOTAL:', 0, 0, 'R');
        $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->subtotal_bs ?? ($facturaOriginal->subtotal * $facturaOriginal->tasa_cambio_usd), 2, ',', '.'), 0, 1, 'R');

        if ($facturaOriginal->es_factura_fiscal) {
            if ($facturaOriginal->monto_exento > 0) {
                $pdf->Cell($labelW, 5, 'MONTO EXENTO:', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->monto_exento, 2, ',', '.'), 0, 1, 'R');
            }

            // Calcular monto en divisas de la factura original para BASE IMP. (IGTF)
            $montoEnDivisasOriginalND = 0;
            if ($facturaOriginal->metodo_pago === 'mixto' && ($facturaOriginal->pagos_mixtos || $facturaOriginal->detalles_pago_mixto)) {
                $pagosMixtosArray = json_decode($facturaOriginal->pagos_mixtos, true) ?? $facturaOriginal->detalles_pago_mixto ?? [];
                foreach ($pagosMixtosArray as $pm) {
                    if (in_array($pm['metodo'] ?? '', ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                        $montoEnDivisasOriginalND += ($pm['monto_bs'] ?? 0) + (($pm['monto_usd'] ?? 0) * $facturaOriginal->tasa_cambio_usd);
                    }
                }
            } elseif (in_array($facturaOriginal->metodo_pago, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                $montoEnDivisasOriginalND = ($facturaOriginal->subtotal_bs ?? ($facturaOriginal->subtotal * $facturaOriginal->tasa_cambio_usd)) + ($facturaOriginal->iva_monto ?? 0);
            }
            $baseImponibleMostrarOriginalND = $montoEnDivisasOriginalND > 0 ? $montoEnDivisasOriginalND : ($facturaOriginal->base_imponible ?? 0);

            if ($facturaOriginal->base_imponible_general > 0) {
                $pdf->Cell($labelW, 5, utf8_decode('BASE IMPONIBLE (' . ($facturaOriginal->iva_porcentaje ?? 16) . '%)'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->base_imponible_general, 2, ',', '.'), 0, 1, 'R');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell($labelW, 5, utf8_decode('IVA (' . ($facturaOriginal->iva_porcentaje ?? 16) . '%)'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->iva_monto_general, 2, ',', '.'), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 9);
            }

            if ($facturaOriginal->base_imponible_reducida > 0) {
                $pdf->Cell($labelW, 5, 'BASE IMPONIBLE (8%):', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->base_imponible_reducida, 2, ',', '.'), 0, 1, 'R');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell($labelW, 5, 'IVA (8%):', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->iva_monto_reducida, 2, ',', '.'), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 9);
            }

            // Mostrar BASE IMP. (IGTF) de la factura original
            if ($baseImponibleMostrarOriginalND > 0) {
                $pdf->Cell($labelW, 5, 'BASE IMP. (IGTF):', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($baseImponibleMostrarOriginalND, 2, ',', '.'), 0, 1, 'R');
            }

            if ($facturaOriginal->aplica_igtf && $facturaOriginal->igtf_monto > 0) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell($labelW, 5, utf8_decode('IGTF (' . ($facturaOriginal->igtf_porcentaje ?? 3) . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs ' . format_money($facturaOriginal->igtf_monto, 2, ',', '.'), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 9);
            }
        }

        $pdf->Line(117, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(1);

        $pdf->SetTextColor(0, 0, 200);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, 7, 'TOTAL FACTURA:', 0, 0, 'R');
        $pdf->Cell($valueW, 7, 'Bs ' . format_money($facturaOriginal->total_bs, 2, ',', '.'), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($labelW, 7, 'CARGO ADICIONAL:', 0, 0, 'R');
        $pdf->Cell($valueW, 7, 'Bs ' . format_money($nota->total_bs, 2, ',', '.'), 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($labelW, 4, 'Ref. USD:', 0, 0, 'R');
        $pdf->Cell($valueW, 4, '$' . format_money($nota->total_usd ?: $nota->total, 2), 0, 1, 'R');

        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, '________________________________________', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Firma y Sello Autorizado', 0, 1, 'C');

        $pdf->Ln(4);
        if ($nota->es_factura_fiscal) {
            $pdf->SetFont('Arial', 'I', 7);
            $leyenda = "Documento emitido conforme a las Providencias SNAT/2011/0071 y SNAT/2024/000102.";
            $pdf->MultiCell(0, 3, utf8_decode($leyenda), 0, 'J');
        } else {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 5, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }
        if ($nota->aplica_igtf) {
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->MultiCell(0, 3, utf8_decode('IGTF aplicado conforme a la Providencia SNAT/2022/000013.'), 0, 'J');
        }
    }

    private function generateNotaDebitoMediaCarta(Fpdf $pdf, Pago $nota, $tipo, $yPosition)
    {
        $pdf->SetY($yPosition);
        $empresa = $nota->empresa;
        $facturaOriginal = $nota->pagoOrigen;

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->razon_social)), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode('RIF: ' . $empresa->rif_fiscal), 0, 1, 'C');

        if ($tipo) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 4, $tipo, 0, 1, 'C');
        }

        $pdf->Ln(1);
        $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY());
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 200);
        $pdf->Cell(0, 4, utf8_decode('NOTA DE DÉBITO N° ' . $nota->numero_completo), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);

        if ($nota->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 3, utf8_decode('Control: ' . $nota->numero_control_fiscal), 0, 1, 'C');
        }

        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(30, 3, 'Factura Afectada:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 3, $facturaOriginal->numero_completo, 0, 1, 'L');

        $pdf->SetFont('Arial', 'I', 6);
        $pdf->MultiCell(0, 3, utf8_decode('Motivo: ' . $nota->motivo_nota), 0, 'L');

        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(10, 4, 'Item', 1, 0, 'C');
        $pdf->Cell(90, 4, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(18, 4, 'Cant.', 1, 0, 'C');
        $pdf->Cell(30, 4, 'P.Unit (Bs)', 1, 0, 'R');
        $pdf->Cell(37, 4, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 6);
        $item = 1;
        foreach ($nota->detalles as $detalle) {
            $pdf->Cell(10, 4, $item++, 1, 0, 'C');
            $pdf->Cell(90, 4, substr(utf8_decode($detalle->descripcion), 0, 55), 1, 0, 'L');
            $pdf->Cell(18, 4, format_money($detalle->cantidad, 2, ',', '.'), 1, 0, 'C');
            $precioUnitBs = $detalle->precio_unitario * $nota->tasa_cambio_usd;
            $pdf->Cell(30, 4, format_money($precioUnitBs, 2, ',', '.'), 1, 0, 'R');
            $subtotalBs = $detalle->subtotal * $nota->tasa_cambio_usd;
            $pdf->Cell(37, 4, format_money($subtotalBs, 2, ',', '.'), 1, 1, 'R');
        }

        // Totales fiscales
        $pdf->SetFont('Arial', '', 6);
        if ($nota->es_factura_fiscal && $nota->iva_monto > 0) {
            $pdf->Cell(148, 4, utf8_decode('IVA (' . ($nota->iva_porcentaje ?? 16) . '%):'), 0, 0, 'R');
            $pdf->Cell(37, 4, 'Bs ' . format_money($nota->iva_monto, 2, ',', '.'), 0, 1, 'R');
        }
        if ($nota->aplica_igtf && $nota->igtf_monto > 0) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(148, 4, utf8_decode('IGTF (' . ($nota->igtf_porcentaje ?? 3) . '%):'), 0, 0, 'R');
            $pdf->Cell(37, 4, 'Bs ' . format_money($nota->igtf_monto, 2, ',', '.'), 0, 1, 'R');
        }

        $pdf->Line(130, $pdf->GetY(), 185, $pdf->GetY());
        $pdf->Ln(1);

        $pdf->SetTextColor(0, 0, 200);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(148, 5, 'TOTAL Bs.:', 0, 0, 'R');
        $pdf->Cell(37, 5, 'Bs ' . format_money($nota->total_bs, 2, ',', '.'), 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        if (!$nota->es_factura_fiscal) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(0, 4, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }

        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, '___________________________', 0, 1, 'C');
        $pdf->Cell(0, 3, 'Firma Autorizada', 0, 1, 'C');
    }

    public function closePreview()
    {
        $this->showPreview = false;
        $this->previewPagoId = null;
    }

    public function getPagosProperty()
    {
        return Pago::with(['consulta.paciente', 'clienteFiscal', 'user', 'notasCredito', 'notasDebito', 'caja'])
            ->whereIn('tipo_pago', ['factura', 'boleta', 'recibo'])
            ->when($this->search, fn($q) => $q->where('serie', 'like', "%{$this->search}%")
                ->orWhere('numero', 'like', "%{$this->search}%")
                ->orWhereHas('consulta.paciente', fn($q) => $q->where('nombres', 'like', "%{$this->search}%")
                    ->orWhere('apellidos', 'like', "%{$this->search}%"))
                ->orWhereHas('clienteFiscal', fn($q) => $q->where('razon_social', 'like', "%{$this->search}%")))
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->when($this->metodo_pago, fn($q) => $q->where('metodo_pago', $this->metodo_pago))
            ->when($this->tipo_pago, fn($q) => $q->where('tipo_pago', $this->tipo_pago))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        $query = Pago::whereIn('tipo_pago', ['factura', 'boleta', 'recibo']);

        return [
            'total' => $query->count(),
            'aprobados' => $query->where('estado', 'aprobado')->count(),
            'pendientes' => $query->where('estado', 'pendiente')->count(),
            'total_usd' => $query->where('estado', 'aprobado')->sum('total_usd'),
            'total_bs' => $query->where('estado', 'aprobado')->sum('total_bs'),
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Pagos y Facturación';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.pagos.index' => 'Pagos y Facturación'
        ];
    }

    public function render()
    {
        return view('livewire.admin.pagos.index', [
            'pagos' => $this->pagos,
            'stats' => $this->stats,
            'totalSumado' => Pago::sum('total_usd'),
        ])->layout($this->getLayout());
    }
}
