<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\ImpuestoConfiguracion;
use Codedge\Fpdf\Fpdf\Fpdf;

class ComprobanteController extends Controller
{
    public function generar($id, $formato = 'a4')
    {
        $pago = Pago::with(['consulta.paciente', 'detalles.baremo', 'clienteFiscal', 'empresa', 'user', 'pagoOrigen'])->findOrFail($id);

        $tasa = (float) ($pago->tasa_cambio_usd ?: 1);

        if ($formato === 'carta') {
            // Media carta: 140mm x 216mm (half letter)
            $pdf = new Fpdf('P', 'mm', [140, 216]);
            $pdf->SetMargins(7, 7, 7);
            $pdf->SetAutoPageBreak(true, 7);
            $this->generarMediaCarta($pdf, $pago, $tasa);
        } else {
            // A4: 210mm x 297mm
            $pdf = new Fpdf('P', 'mm', 'A4');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);
            $this->generarA4($pdf, $pago, $tasa, 'ORIGINAL');
            $this->generarA4($pdf, $pago, $tasa, 'COPIA - CLIENTE');
        }

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="comprobante-' . $pago->numero_completo . '.pdf"');
    }

    // ============================================================
    // FORMATO A4
    // ============================================================
    private function generarA4($pdf, $pago, $tasa, $tipo)
    {
        $pdf->AddPage();
        $empresa = $pago->empresa;
        $pageW = 190; // 210 - 10 - 10 margins





        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(45);
        if ($empresa->rif_fiscal) {
            $pdf->Cell(0, 4, $this->u('RIF: ' . $empresa->rif_fiscal), 0, 1);
            $pdf->SetX(45);
        }
        if ($empresa->direccion_fiscal_completa) {
            $pdf->MultiCell(95, 3, $this->u($empresa->direccion_fiscal_completa));
        }
        if ($empresa->telefono_fiscal) {
            $pdf->SetX(45);
            $pdf->Cell(0, 3, $this->u('Telf: ' . $empresa->telefono_fiscal), 0, 1);
        }
        if ($empresa->correo_fiscal) {
            $pdf->SetX(45);
            $pdf->Cell(0, 3, $this->u('Email: ' . $empresa->correo_fiscal), 0, 1);
        }

        // Recuadro tipo documento (derecha)
        $tipoDoc = $this->getTipoDocLabel($pago->tipo_pago);
        $codigoSeniat = $pago->seniat_tipo_documento ? ' (' . $pago->seniat_tipo_documento . ')' : '';

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetXY(140, 10);
        $pdf->Cell(60, 7, $this->u($tipoDoc . $codigoSeniat), 1, 1, 'C');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(140);
        $pdf->Cell(60, 5, $this->u('N°: ') . $pago->numero_completo, 0, 1, 'C');

        if ($pago->numero_control_fiscal) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetX(140);
            $pdf->Cell(60, 5, $this->u('N° Control: ') . $pago->numero_control_fiscal, 0, 1, 'C');
        }
        if ($pago->serieModel && $pago->serieModel->control_fiscal_actual) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetX(140);
            $pdf->Cell(60, 3, $this->u('Controles autorizados: desde 00000001'), 0, 1, 'C');
        }

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetX(140);
        $pdf->Cell(60, 4, $this->u($tipo), 0, 1, 'C');

        // Coletilla no fiscal
        if (!$pago->es_factura_fiscal) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(200, 0, 0);
            $pdf->Cell(0, 6, $this->u('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }

        // Línea separadora
        $pdf->Ln(3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);

        // ---- DATOS DEL CLIENTE ----
        $this->renderCliente($pdf, $pago);

        // Fecha, método, condición
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(15, 4, 'Fecha:', 0, 0);
        $pdf->Cell(35, 4, $pago->fecha->format('d/m/Y'), 0, 0);
        $pdf->Cell(20, 4, $this->u('Moneda:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(30, 4, $this->u('BOLÍVARES'), 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(25, 4, $this->u('Condición:'), 0, 0);
        $pdf->Cell(0, 4, $this->u(ucfirst($pago->condicion_pago ?? 'Contado')), 0, 1);

        $pdf->Cell(15, 4, $this->u('Método:'), 0, 0);
        $pdf->Cell(45, 4, $this->u(str_replace('_', ' ', ucwords($pago->metodo_pago))), 0, 0);
        if ($pago->referencia) {
            $pdf->Cell(20, 4, 'Referencia:', 0, 0);
            $pdf->Cell(0, 4, $pago->referencia, 0, 1);
        } else {
            $pdf->Ln(4);
        }

        // Tasa de cambio
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 3, $this->u('Tasa de cambio BCV del día: $1,00 = Bs. ' . $this->bs($tasa)), 0, 1);

        // ---- DOCUMENTO AFECTADO (NC / ND) ----
        if ($pago->pago_origen_id && $pago->pagoOrigen) {
            $this->renderDocumentoAfectado($pdf, $pago, $tasa);
        }

        $pdf->Ln(3);

        // ---- TABLA DE DETALLES (en Bs) ----
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
        $pdf->Cell(70, 6, $this->u('Descripción'), 1, 0, 'L', true);
        $pdf->Cell(15, 6, 'Cant.', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'P/U (Bs.)', 1, 0, 'R', true);
        $pdf->Cell(30, 6, 'Subtotal (Bs.)', 1, 0, 'R', true);
        $pdf->Cell(25, 6, 'IVA %', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);
        $num = 1;
        foreach ($pago->detalles as $detalle) {
            $puBs = (float) $detalle->precio_unitario * $tasa;
            $subBs = (float) $detalle->subtotal * $tasa;
            $ivaLabel = $detalle->exento_iva ? 'Exento' : ($detalle->iva_alicuota ?? 16) . '%';

            $pdf->Cell(10, 5, $num++, 1, 0, 'C');
            $pdf->Cell(70, 5, $this->u($detalle->descripcion), 1, 0, 'L');
            $pdf->Cell(15, 5, $detalle->cantidad, 1, 0, 'C');
            $pdf->Cell(30, 5, 'Bs. ' . $this->bs($puBs), 1, 0, 'R');
            $pdf->Cell(30, 5, 'Bs. ' . $this->bs($subBs), 1, 0, 'R');
            $pdf->Cell(25, 5, $ivaLabel, 1, 1, 'C');
        }

        // ---- TOTALES EN BS ----
        $this->renderTotalesA4($pdf, $pago, $tasa);

        // ---- PIE ----
        $pdf->SetY(-30);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 3, $this->u('Procesado por: ' . $pago->user->name), 0, 1, 'C');
        $pdf->Cell(0, 3, $this->u('Fecha de emisión: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'C');
        if ($pago->es_factura_fiscal) {
            $pdf->Cell(0, 3, $this->u('Documento emitido conforme a las Providencias SNAT/2011/0071 y SNAT/2024/000102'), 0, 1, 'C');
        } else {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 3, $this->u('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }
        if ($pago->aplica_igtf) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 3, $this->u('IGTF aplicado conforme a la Providencia SNAT/2022/000013'), 0, 1, 'C');
        }
    }

    private function renderTotalesA4($pdf, $pago, $tasa)
    {
        $pdf->Ln(2);
        $labelW = 140;
        $valueW = 40;

        $subtotalBs = (float) $pago->subtotal * $tasa;
        $descuentoBs = (float) $pago->descuento * $tasa;

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($labelW, 5, 'SUBTOTAL:', 0, 0, 'R');
        $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($subtotalBs), 0, 1, 'R');

        if ($pago->descuento > 0) {
            $pdf->Cell($labelW, 5, 'DESCUENTO:', 0, 0, 'R');
            $pdf->Cell($valueW, 5, '-Bs. ' . $this->bs($descuentoBs), 0, 1, 'R');
        }

        if ($pago->es_factura_fiscal) {
            $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
            $ivaPct = $ivaConfig ? $ivaConfig->porcentaje : 16;

            if ($pago->monto_exento > 0) {
                $pdf->Cell($labelW, 5, 'MONTO EXENTO:', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->monto_exento * $tasa), 0, 1, 'R');
            }

            if ($pago->base_imponible_general > 0) {
                $pdf->Cell($labelW, 5, $this->u('BASE IMPONIBLE (' . $ivaPct . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->base_imponible_general * $tasa), 0, 1, 'R');

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($labelW, 5, $this->u('IVA (' . $ivaPct . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->iva_monto_general * $tasa), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 8);
            }

            if ($pago->base_imponible_reducida > 0) {
                $pdf->Cell($labelW, 5, 'BASE IMPONIBLE (8%):', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->base_imponible_reducida * $tasa), 0, 1, 'R');

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($labelW, 5, 'IVA (8%):', 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->iva_monto_reducida * $tasa), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 8);
            }

            if ($pago->aplica_igtf && $pago->igtf_monto > 0) {
                $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
                $igtfPct = $igtfConfig ? $igtfConfig->porcentaje : 3;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($labelW, 5, $this->u('IGTF (' . $igtfPct . '%) - Pago en divisas:'), 0, 0, 'R');
                $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($pago->igtf_monto * $tasa), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 8);
            }
        }

        // Línea separadora
        $pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(1);

        // Total principal en Bs
        $totalBs = (float) ($pago->total_bs ?: $pago->total * $tasa);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, 6, 'TOTAL Bs.:', 0, 0, 'R');
        $pdf->Cell($valueW, 6, 'Bs. ' . $this->bs($totalBs), 0, 1, 'R');

        // Referencia en USD
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($labelW, 5, 'Ref. USD:', 0, 0, 'R');
        $pdf->Cell($valueW, 5, '$' . format_money($pago->total_usd ?: $pago->total, 2), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($labelW + $valueW, 4, $this->u('Tasa BCV: $1,00 = Bs. ' . $this->bs($tasa)), 0, 1, 'R');

        // Coletilla IGTF
        if ($pago->aplica_igtf && $pago->igtf_monto > 0) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial', '', 6);
            $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
            if ($igtfConfig && $igtfConfig->coletilla_fiscal) {
                $pdf->MultiCell(0, 3, $this->u($igtfConfig->coletilla_fiscal), 0, 'J');
            }
        }

        // Observaciones
        if ($pago->observaciones) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 4, 'OBSERVACIONES:', 0, 1);
            $pdf->SetFont('Arial', '', 7);
            $pdf->MultiCell(0, 3, $this->u($pago->observaciones));
        }
    }

    // ============================================================
    // FORMATO MEDIA CARTA (140mm x 216mm)
    // ============================================================
    private function generarMediaCarta($pdf, $pago, $tasa)
    {
        $pdf->AddPage();
        $empresa = $pago->empresa;
        $w = 126; // 140 - 7 - 7 margins

        // ---- ENCABEZADO ----
        if (file_exists(public_path('logo/logo.png'))) {
            $pdf->Image(public_path('logo/logo.png'), 7, 7, 20);
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(30, 7);
        $pdf->Cell(70, 4, $this->u($empresa->razon_social), 0, 1);

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetX(30);
        if ($empresa->rif_fiscal) {
            $pdf->Cell(70, 3, $this->u('RIF: ' . $empresa->rif_fiscal), 0, 1);
            $pdf->SetX(30);
        }
        if ($empresa->direccion_fiscal_completa) {
            $pdf->MultiCell(70, 3, $this->u($empresa->direccion_fiscal_completa));
        }
        if ($empresa->telefono_fiscal) {
            $pdf->SetX(30);
            $pdf->Cell(70, 3, $this->u('Telf: ' . $empresa->telefono_fiscal), 0, 1);
        }

        // Recuadro tipo documento (derecha)
        $tipoDoc = $this->getTipoDocLabel($pago->tipo_pago);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(100, 7);
        $pdf->Cell(33, 5, $this->u($tipoDoc), 1, 1, 'C');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetX(100);
        $pdf->Cell(33, 4, $pago->numero_completo, 0, 1, 'C');

        if ($pago->numero_control_fiscal) {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetX(100);
            $pdf->Cell(33, 3, $this->u('Ctrl: ') . $pago->numero_control_fiscal, 0, 1, 'C');
        }

        // Coletilla no fiscal
        if (!$pago->es_factura_fiscal) {
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetTextColor(200, 0, 0);
            $pdf->Cell($w, 4, $this->u('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Ln(2);
        $pdf->Line(7, $pdf->GetY(), 133, $pdf->GetY());
        $pdf->Ln(2);

        // ---- DATOS CLIENTE (compacto) ----
        $pdf->SetFont('Arial', 'B', 7);
        if ($pago->clienteFiscal) {
            $pdf->Cell(12, 3, 'Cliente:', 0, 0);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(75, 3, $this->u($pago->clienteFiscal->razon_social), 0, 0);
            $pdf->Cell(12, 3, 'RIF:', 0, 0);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(0, 3, $pago->clienteFiscal->documento_completo, 0, 1);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(12, 3, $this->u('Dir.:'), 0, 0);
            $pdf->Cell(0, 3, $this->u($this->truncar($pago->clienteFiscal->direccion_fiscal, 80)), 0, 1);
        } elseif ($pago->consulta && $pago->consulta->paciente) {
            $pdf->Cell(15, 3, 'Paciente:', 0, 0);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 3, $this->u($pago->consulta->paciente->nombre_completo), 0, 1);
        }

        // Fecha + Moneda + Método
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(12, 3, 'Fecha:', 0, 0);
        $pdf->Cell(25, 3, $pago->fecha->format('d/m/Y'), 0, 0);
        $pdf->Cell(14, 3, 'Moneda:', 0, 0);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(20, 3, $this->u('BOLÍVARES'), 0, 0);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(14, 3, $this->u('Método:'), 0, 0);
        $pdf->Cell(0, 3, $this->u(str_replace('_', ' ', ucwords($pago->metodo_pago))), 0, 1);

        $pdf->Cell(0, 3, $this->u('Tasa BCV: $1,00 = Bs. ' . $this->bs($tasa)), 0, 1);

        // Documento afectado (NC/ND)
        if ($pago->pago_origen_id && $pago->pagoOrigen) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetFillColor(255, 245, 230);
            $pdf->Cell($w, 4, $this->u(' DOC. AFECTADO: ') . $pago->pagoOrigen->numero_completo . ' | ' . $pago->pagoOrigen->fecha->format('d/m/Y'), 1, 1, 'L', true);
            if ($pago->motivo_nota) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(12, 3, 'Motivo:', 0, 0);
                $pdf->Cell(0, 3, $this->u($this->truncar($pago->motivo_nota, 80)), 0, 1);
            }
        }

        $pdf->Ln(2);

        // ---- TABLA DE DETALLES (compacta, en Bs) ----
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(8, 5, '#', 1, 0, 'C', true);
        $pdf->Cell(52, 5, $this->u('Descripción'), 1, 0, 'L', true);
        $pdf->Cell(12, 5, 'Cant.', 1, 0, 'C', true);
        $pdf->Cell(22, 5, 'P/U Bs.', 1, 0, 'R', true);
        $pdf->Cell(22, 5, 'Subt. Bs.', 1, 0, 'R', true);
        $pdf->Cell(10, 5, 'IVA', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 6);
        $num = 1;
        foreach ($pago->detalles as $detalle) {
            $puBs = (float) $detalle->precio_unitario * $tasa;
            $subBs = (float) $detalle->subtotal * $tasa;
            $ivaLabel = $detalle->exento_iva ? 'E' : ($detalle->iva_alicuota ?? 16) . '%';

            $pdf->Cell(8, 4, $num++, 1, 0, 'C');
            $pdf->Cell(52, 4, $this->u($this->truncar($detalle->descripcion, 45)), 1, 0, 'L');
            $pdf->Cell(12, 4, $detalle->cantidad, 1, 0, 'C');
            $pdf->Cell(22, 4, $this->bs($puBs), 1, 0, 'R');
            $pdf->Cell(22, 4, $this->bs($subBs), 1, 0, 'R');
            $pdf->Cell(10, 4, $ivaLabel, 1, 1, 'C');
        }

        // ---- TOTALES (compactos, en Bs) ----
        $pdf->Ln(2);
        $labelW = 94;
        $valueW = 32;

        $subtotalBs = (float) $pago->subtotal * $tasa;
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($labelW, 4, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell($valueW, 4, 'Bs. ' . $this->bs($subtotalBs), 0, 1, 'R');

        if ($pago->descuento > 0) {
            $pdf->Cell($labelW, 4, 'Descuento:', 0, 0, 'R');
            $pdf->Cell($valueW, 4, '-Bs. ' . $this->bs($pago->descuento * $tasa), 0, 1, 'R');
        }

        if ($pago->es_factura_fiscal) {
            $ivaConfig = ImpuestoConfiguracion::where('codigo', 'IVA')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
            $ivaPct = $ivaConfig ? $ivaConfig->porcentaje : 16;

            if ($pago->monto_exento > 0) {
                $pdf->Cell($labelW, 4, 'Exento:', 0, 0, 'R');
                $pdf->Cell($valueW, 4, 'Bs. ' . $this->bs($pago->monto_exento * $tasa), 0, 1, 'R');
            }

            if ($pago->base_imponible_general > 0) {
                $pdf->Cell($labelW, 4, $this->u('Base Imp. (' . $ivaPct . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 4, 'Bs. ' . $this->bs($pago->base_imponible_general * $tasa), 0, 1, 'R');

                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell($labelW, 4, $this->u('IVA (' . $ivaPct . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 4, 'Bs. ' . $this->bs($pago->iva_monto_general * $tasa), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 7);
            }

            if ($pago->aplica_igtf && $pago->igtf_monto > 0) {
                $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
                $igtfPct = $igtfConfig ? $igtfConfig->porcentaje : 3;

                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell($labelW, 4, $this->u('IGTF (' . $igtfPct . '%):'), 0, 0, 'R');
                $pdf->Cell($valueW, 4, 'Bs. ' . $this->bs($pago->igtf_monto * $tasa), 0, 1, 'R');
                $pdf->SetFont('Arial', '', 7);
            }
        }

        $pdf->Line(90, $pdf->GetY(), 133, $pdf->GetY());
        $pdf->Ln(1);

        $totalBs = (float) ($pago->total_bs ?: $pago->total * $tasa);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($labelW, 5, 'TOTAL Bs.:', 0, 0, 'R');
        $pdf->Cell($valueW, 5, 'Bs. ' . $this->bs($totalBs), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell($labelW, 3, 'Ref. USD:', 0, 0, 'R');
        $pdf->Cell($valueW, 3, '$' . format_money($pago->total_usd ?: $pago->total, 2), 0, 1, 'R');

        // Coletilla IGTF
        if ($pago->aplica_igtf && $pago->igtf_monto > 0) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', '', 5);
            $igtfConfig = ImpuestoConfiguracion::where('codigo', 'IGTF')->where('empresa_id', $pago->empresa_id)->where('activo', true)->first();
            if ($igtfConfig && $igtfConfig->coletilla_fiscal) {
                $pdf->MultiCell(0, 2, $this->u($igtfConfig->coletilla_fiscal), 0, 'J');
            }
        }

        // ---- PIE ----
        $pdf->SetY(-15);
        $pdf->Line(7, $pdf->GetY(), 133, $pdf->GetY());
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 5);
        $pdf->Cell(0, 3, $this->u('Procesado por: ' . $pago->user->name . ' | ' . now()->format('d/m/Y H:i:s')), 0, 1, 'C');
        if ($pago->es_factura_fiscal) {
            $pdf->Cell(0, 3, $this->u('Documento emitido conforme a las Providencias SNAT/2011/0071 y SNAT/2024/000102'), 0, 1, 'C');
        } else {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(0, 3, $this->u('SIN DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        }
        if ($pago->aplica_igtf) {
            $pdf->SetFont('Arial', '', 5);
            $pdf->Cell(0, 3, $this->u('IGTF aplicado conforme a la Providencia SNAT/2022/000013'), 0, 1, 'C');
        }
    }

    // ============================================================
    // HELPERS COMPARTIDOS
    // ============================================================
    private function renderCliente($pdf, $pago)
    {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, $this->u('DATOS DEL CLIENTE'), 0, 1);
        $pdf->SetFont('Arial', '', 9);

        if ($pago->clienteFiscal) {
            $pdf->Cell(30, 4, $this->u('Razón Social:'), 0, 0);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(70, 4, $this->u($pago->clienteFiscal->razon_social), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(20, 4, 'RIF/CI:', 0, 0);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 4, $pago->clienteFiscal->documento_completo, 0, 1);
            $pdf->SetFont('Arial', '', 9);

            $pdf->Cell(30, 4, $this->u('Dirección:'), 0, 0);
            $pdf->MultiCell(0, 4, $this->u($pago->clienteFiscal->direccion_fiscal));

            if ($pago->clienteFiscal->telefono) {
                $pdf->Cell(30, 4, $this->u('Teléfono:'), 0, 0);
                $pdf->Cell(70, 4, $pago->clienteFiscal->telefono, 0, 0);
            }
            if ($pago->clienteFiscal->email) {
                $pdf->Cell(15, 4, 'Email:', 0, 0);
                $pdf->Cell(0, 4, $pago->clienteFiscal->email, 0, 1);
            } else {
                $pdf->Ln(4);
            }
        } elseif ($pago->consulta) {
            $paciente = $pago->consulta->paciente;
            $pdf->Cell(30, 4, 'Paciente:', 0, 0);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(70, 4, $this->u($paciente->nombre_completo), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(25, 4, 'Documento:', 0, 0);
            $pdf->Cell(0, 4, $paciente->numero_documento ?? '-', 0, 1);
        }
    }

    private function renderDocumentoAfectado($pdf, $pago, $tasa)
    {
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(255, 245, 230);
        $pdf->Cell(0, 5, $this->u(' DOCUMENTO AFECTADO'), 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $origen = $pago->pagoOrigen;
        $origenTotalBs = (float) ($origen->total_bs ?: $origen->total * $tasa);
        $pdf->Cell(35, 4, $this->u('  Factura N°: ') . $origen->numero_completo, 0, 0);
        if ($origen->numero_control_fiscal) {
            $pdf->Cell(45, 4, $this->u('N° Control: ') . $origen->numero_control_fiscal, 0, 0);
        }
        $pdf->Cell(35, 4, 'Fecha: ' . $origen->fecha->format('d/m/Y'), 0, 0);
        $pdf->Cell(0, 4, 'Monto: Bs. ' . $this->bs($origenTotalBs), 0, 1);
        if ($pago->motivo_nota) {
            $pdf->Cell(20, 4, '  Motivo:', 0, 0);
            $pdf->MultiCell(0, 4, $this->u($pago->motivo_nota));
        }
    }

    private function getTipoDocLabel(string $tipo): string
    {
        return match ($tipo) {
            'factura' => 'FACTURA',
            'nota_credito' => 'NOTA DE CRÉDITO',
            'nota_debito' => 'NOTA DE DÉBITO',
            'boleta' => 'BOLETA',
            'recibo' => 'RECIBO',
            default => strtoupper(str_replace('_', ' ', $tipo)),
        };
    }

    /**
     * Formatea un número al estilo venezolano: separador de miles con punto, decimales con coma.
     */
    private function bs(float $amount): string
    {
        return format_money($amount, 2, ',', '.');
    }

    /**
     * utf8_decode wrapper.
     */
    private function u(?string $text): string
    {
        return utf8_decode($text ?? '');
    }

    /**
     * Trunca un string a una longitud máxima.
     */
    private function truncar(?string $text, int $max): string
    {
        if (!$text) return '';
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '...' : $text;
    }
}
