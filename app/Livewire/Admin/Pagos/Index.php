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

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function printReceipt(Pago $pago)
    {
        $this->previewPagoId = $pago->id;
        $this->showPreview = true;
    }

    public function downloadReceipt($pagoId, $formato = 'letter')
    {
        $pago = Pago::with(['consulta.paciente', 'consulta.medico', 'detalles.baremo', 'empresa', 'clienteFiscal', 'pagoOrigen'])->findOrFail($pagoId);

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
        // Espacio para encabezado preimpreso
        $pdf->Ln(40);

        // DATOS DEL CLIENTE
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DATOS DEL CLIENTE', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);

        if ($pago->clienteFiscal) {
            $cliente = $pago->clienteFiscal;
            $pdf->Cell(50, 5, utf8_decode('Razón Social:'), 'LT', 0, 'L');
            $pdf->Cell(0, 5, utf8_decode($cliente->razon_social), 'RT', 1, 'L');
            $pdf->Cell(50, 5, 'RIF/CI:', 'L', 0, 'L');
            $pdf->Cell(0, 5, $cliente->documento_completo, 'R', 1, 'L');
            $pdf->Cell(50, 5, utf8_decode('Dirección Fiscal:'), 'L', 0, 'L');
            $pdf->MultiCell(0, 5, utf8_decode($cliente->direccion ?? 'N/A'), 'R', 'L');
            $pdf->Cell(50, 5, utf8_decode('Teléfono:'), 'L', 0, 'L');
            $pdf->Cell(70, 5, $cliente->telefono ?? 'N/A', 0, 0, 'L');
            $pdf->Cell(20, 5, 'Email:', 0, 0, 'L');
            $pdf->Cell(0, 5, substr($cliente->email ?? 'N/A', 0, 30), 'R', 1, 'L');
            $pdf->Cell(0, 0, '', 'LBR', 1, 'L');
        } elseif ($pago->consulta && $pago->consulta->paciente) {
            $paciente = $pago->consulta->paciente;
            $pdf->Cell(50, 5, 'Nombre:', 'LT', 0, 'L');
            $pdf->Cell(0, 5, utf8_decode($paciente->nombre_completo), 'RT', 1, 'L');
            $pdf->Cell(50, 5, utf8_decode('Cédula:'), 'L', 0, 'L');
            $pdf->Cell(0, 5, $paciente->documento_identidad ?? 'N/A', 'R', 1, 'L');
            $pdf->Cell(50, 5, utf8_decode('Dirección:'), 'L', 0, 'L');
            $pdf->MultiCell(0, 5, utf8_decode($paciente->direccion ?? 'N/A'), 'R', 'L');
            $pdf->Cell(50, 5, utf8_decode('Teléfono:'), 'LB', 0, 'L');
            $pdf->Cell(0, 5, $paciente->telefono ?? 'N/A', 'RB', 1, 'L');
        }

        $pdf->Ln(3);

        // INFORMACIÓN DE LA TRANSACCIÓN
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('INFORMACIÓN DE LA TRANSACCIÓN'), 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 5, utf8_decode('Fecha de Emisión:'), 'LT', 0, 'L');
        $pdf->Cell(70, 5, $pago->fecha->format('d/m/Y'), 'T', 0, 'L');
        $pdf->Cell(30, 5, utf8_decode('Condición:'), 'T', 0, 'L');
        $pdf->Cell(0, 5, strtoupper($pago->condicion_pago ?? 'CONTADO'), 'RT', 1, 'L');

        $pdf->Cell(50, 5, utf8_decode('-'), 'LB', 0, 'L');
        $metodoPago = strtoupper(str_replace('_', ' ', $pago->metodo_pago));
        $pdf->Cell(70, 5, utf8_decode('-'), 'B', 0, 'L');
        $pdf->Cell(30, 5, 'FACTURA NRO:', 'B', 0, 'L');
        $pdf->Cell(0, 5, $pago->numero_completo, 'B', 1, 'L');

        $pdf->Ln(3);

        // DETALLE DE SERVICIOS
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'DETALLE DE SERVICIOS', 1, 1, 'L', true);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(12, 6, 'Item', 1, 0, 'C');
        $pdf->Cell(85, 6, utf8_decode('Descripción'), 1, 0, 'C');
        $pdf->Cell(20, 6, 'Cant.', 1, 0, 'C');
        $pdf->Cell(35, 6, 'P. Unit. (Bs)', 1, 0, 'R');
        $pdf->Cell(38, 6, 'Total (Bs)', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        foreach ($pago->detalles as $detalle) {
            $pdf->Cell(12, 5, $item++, 1, 0, 'C');
            $pdf->Cell(85, 5, substr(utf8_decode($detalle->descripcion), 0, 50), 1, 0, 'L');
            $pdf->Cell(20, 5, format_money($detalle->cantidad, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell(35, 5, format_money($detalle->precio_unitario, 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(38, 5, format_money($detalle->subtotal, 2, ',', '.'), 1, 1, 'R');
        }




        $pdf->Ln(2);


        // RESUMEN DE TOTALES (Orden: Subtotal, Exento, Base Imp., IVA, Total)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'SUBTOTAL:', 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money($pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd), 2, ',', '.'), 1, 1, 'R');

        // Calcular monto en divisas para base imponible IGTF
        $montoEnDivisas = 0;
        if ($pago->metodo_pago === 'mixto' && ($pago->pagos_mixtos || $pago->detalles_pago_mixto)) {
            $pagosMixtosArray = json_decode($pago->pagos_mixtos, true) ?? $pago->detalles_pago_mixto ?? [];
            foreach ($pagosMixtosArray as $pm) {
                if (in_array($pm['metodo'] ?? '', ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
                    // Sumar tanto monto_bs como monto_usd convertido (el usuario puede ingresar en cualquiera)
                    $montoEnDivisas += ($pm['monto_bs'] ?? 0) + (($pm['monto_usd'] ?? 0) * $pago->tasa_cambio_usd);
                }
            }
        } elseif (in_array($pago->metodo_pago, ['efectivo_usd', 'transferencia_usd', 'zelle', 'paypal', 'usdt'])) {
            $montoEnDivisas = ($pago->subtotal_bs ?? ($pago->subtotal * $pago->tasa_cambio_usd)) + ($pago->iva_monto ?? 0);
        }

        // Solo mostrar líneas de IVA si NO es exento
        $esExento = ($pago->monto_exento ?? 0) > 0 && ($pago->base_imponible ?? 0) == 0;

        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'EXENTO:', 1, 0, 'R');
        $pdf->Cell(38, 5, 'Bs ' . format_money($pago->monto_exento ?? 0, 2, ',', '.'), 1, 1, 'R');

        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 5, 'BASE IMP. (IGTF):', 1, 0, 'R');
        // BASE IMP. muestra SOLO el monto pagado en divisas (para cálculo de IGTF)
        // Si no hay pago en divisas, muestra la base_imponible normal
        $baseImponibleMostrar = $montoEnDivisas > 0 ? $montoEnDivisas : ($pago->base_imponible ?? 0);
        $pdf->Cell(38, 5, 'Bs ' . format_money($baseImponibleMostrar, 2, ',', '.'), 1, 1, 'R');
if (!$esExento) {
            $ivaPorcentaje =  16;
            $pdf->Cell(117, 5, '', 0, 0);
            $pdf->Cell(35, 5, "IVA ({$ivaPorcentaje}%):", 1, 0, 'R');
            $pdf->Cell(38, 5, 'Bs ' . format_money($pago->iva_monto ?? 0, 2, ',', '.'), 1, 1, 'R');
        }

        // Mostrar IGTF si aplica
        if (($pago->igtf_monto ?? 0) > 0) {
            $pdf->Cell(117, 5, '', 0, 0);
            $pdf->Cell(35, 5, 'IGTF (3%):', 1, 0, 'R');
            $pdf->Cell(38, 5, 'Bs ' . format_money($pago->igtf_monto, 2, ',', '.'), 1, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(117, 5, '', 0, 0);
        $pdf->Cell(35, 7, 'TOTAL:', 1, 0, 'R');
        $pdf->Cell(38, 7, 'Bs ' . format_money($pago->total_bs, 2, ',', '.'), 1, 1, 'R');
        $pdf->Ln(2);

        // Coletilla IGTF solo si es a crédito
        if (strtoupper($pago->condicion_pago ?? 'CONTADO') === 'CREDITO' || strtoupper($pago->condicion_pago ?? 'CONTADO') === 'CRÉDITO') {
            $pdf->SetXY('40','140');
            $pdf->SetFont('Arial', 'I', 7);
            $texto = 'El pago total o parcial de esta factura en moneda diferente a Bs' . chr(10) . 'causará el 3% de IGTF adicional al monto total indicado.';
            $pdf->MultiCell(0, 3, utf8_decode($texto), 0, 'J');
        }

        // Solo mostrar USD si NO es factura fiscal
        if (!$pago->es_factura_fiscal) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(117, 4, '', 0, 0);
            $pdf->Cell(35, 4, 'Equivalente USD:', 0, 0, 'R');
            $pdf->Cell(38, 4, '$ ' . format_money($pago->total_usd, 2, '.', ','), 0, 1, 'R');

            $pdf->Cell(117, 4, '', 0, 0);
            $pdf->Cell(35, 4, 'Tasa BCV:', 0, 0, 'R');
            $pdf->Cell(38, 4, 'Bs ' . format_money($pago->tasa_cambio_usd, 2, ',', '.'), 0, 1, 'R');

            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 5, utf8_decode('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }




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

    public function render()
    {
        $pagos = Pago::with(['consulta.paciente', 'clienteFiscal', 'user', 'notasCredito', 'notasDebito', 'caja'])
            ->whereIn('tipo_pago', ['factura', 'boleta', 'recibo'])
            ->when($this->search, fn($q) => $q->where('serie', 'like', "%{$this->search}%")
                ->orWhere('numero', 'like', "%{$this->search}%")
                ->orWhereHas('consulta.paciente', fn($q) => $q->where('nombres', 'like', "%{$this->search}%")
                    ->orWhere('apellidos', 'like', "%{$this->search}%"))
                ->orWhereHas('clienteFiscal', fn($q) => $q->where('razon_social', 'like', "%{$this->search}%")))
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->when($this->metodo_pago, fn($q) => $q->where('metodo_pago', $this->metodo_pago))
            ->when($this->tipo_pago, fn($q) => $q->where('tipo_pago', $this->tipo_pago))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.pagos.index', compact('pagos'))->layout($this->getLayout());
    }
}
