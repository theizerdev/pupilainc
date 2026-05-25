<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Codedge\Fpdf\Fpdf\Fpdf;

class ReposoController extends Controller
{
    private Fpdf $pdf;
    private $empresa;
    private $consulta;

    public function generar($id)
    {
        $this->consulta = Consulta::with(['paciente', 'medico.especialidades', 'especialidad', 'reposo'])
            ->findOrFail($id);

        if (!$this->consulta->reposo) {
            abort(404, 'No se encontró reposo médico para esta consulta.');
        }

        $this->empresa = auth()->user()->empresa;
        $reposo        = $this->consulta->reposo;
        $paciente      = $this->consulta->paciente;

        $especialidad = $this->consulta->especialidad->nombre
            ?? $this->consulta->medico->especialidades()->take(1)->pluck('nombre')->first()
            ?? 'Medicina General';

        $medico = 'Dr(a). ' . $this->consulta->medico->nombre_completo;

        $this->pdf = new Fpdf('P', 'mm', 'A4');
        $this->pdf->SetMargins(25, 15, 25);
        $this->pdf->SetAutoPageBreak(true, 28);
        $this->pdf->AddPage();

        // ── Encabezado ────────────────────────────────────────────────────────
        $this->encabezado();

        // ── Número y fecha ────────────────────────────────────────────────────
        $this->pdf->SetFont('Arial', '', 8);
        $this->pdf->SetTextColor(130, 130, 130);
        $this->pdf->Cell(0, 5,
            utf8_decode('N° REP-' . str_pad($this->consulta->id, 6, '0', STR_PAD_LEFT) .
            '   |   Emitido: ' . now()->format('d/m/Y H:i')),
            0, 1, 'R'
        );
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(3);

        // ── Barra de título ───────────────────────────────────────────────────
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->Cell(0, 10, utf8_decode('CONSTANCIA DE REPOSO MÉDICO'), 0, 1, 'C', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(8);

        // ── Bloque paciente ───────────────────────────────────────────────────
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(0, 6, utf8_decode('Quien suscribe, ' . $medico . ', médico especialista en ' . $especialidad . ', hace constar por medio de la presente que:'), 0, 'J');
        $this->pdf->Ln(4);

        $this->pdf->SetFillColor(245, 248, 252);
        $this->pdf->SetDrawColor(41, 128, 185);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Rect(25, $this->pdf->GetY(), 160, 22, 'DF');
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);

        $yBloque = $this->pdf->GetY() + 3;
        $this->pdf->SetXY(25, $yBloque);
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(160, 7, utf8_decode(strtoupper($paciente->nombre_completo)), 0, 1, 'C');
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetX(25);
        $this->pdf->Cell(160, 6,
            utf8_decode('C.I. / Documento: ' . ($paciente->documento_identidad ?? 'N/A') .
            ($paciente->fecha_nacimiento ? '   |   Edad: ' . $paciente->edad_formateada : '')),
            0, 1, 'C'
        );
        $this->pdf->Ln(6);

        // ── Texto del reposo ──────────────────────────────────────────────────
        $fechaInicio = $reposo->fecha_inicio->format('d') . ' de ' . $this->mes($reposo->fecha_inicio->format('n')) . ' de ' . $reposo->fecha_inicio->format('Y');
        $fechaFin    = $reposo->fecha_fin->format('d') . ' de ' . $this->mes($reposo->fecha_fin->format('n')) . ' de ' . $reposo->fecha_fin->format('Y');

        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(0, 7,
            utf8_decode('requiere reposo médico por un período de ' . $reposo->dias_reposo . ' (' . $this->numerosALetras($reposo->dias_reposo) . ') días, comprendido desde el ' . $fechaInicio . ' hasta el ' . $fechaFin . ', ambas fechas inclusive.'),
            0, 'J'
        );

        // ── Bloque destacado de días ──────────────────────────────────────────
        $this->pdf->Ln(5);
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 22);
        $this->pdf->Cell(0, 16, $reposo->dias_reposo . ' DÍAS DE REPOSO', 0, 1, 'C', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(5);

        // ── Fechas en dos columnas ────────────────────────────────────────────
        $this->pdf->SetFillColor(245, 248, 252);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->SetLineWidth(0.2);

        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(80, 7, utf8_decode('DESDE'), 1, 0, 'C', true);
        $this->pdf->Cell(80, 7, utf8_decode('HASTA'), 1, 1, 'C', true);

        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->SetTextColor(41, 128, 185);
        $this->pdf->Cell(80, 10, $reposo->fecha_inicio->format('d/m/Y'), 1, 0, 'C');
        $this->pdf->Cell(80, 10, $reposo->fecha_fin->format('d/m/Y'), 1, 1, 'C');
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(5);

        // ── Motivo ────────────────────────────────────────────────────────────
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(0, 7, utf8_decode(' MOTIVO'), 0, 1, 'L', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(0, 6, utf8_decode($reposo->motivo), 0, 'J');

        if ($reposo->observaciones) {
            $this->pdf->Ln(3);
            $this->pdf->SetFillColor(41, 128, 185);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(0, 7, utf8_decode(' OBSERVACIONES'), 0, 1, 'L', true);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Ln(2);
            $this->pdf->SetFont('Arial', '', 10);
            $this->pdf->MultiCell(0, 6, utf8_decode($reposo->observaciones), 0, 'J');
        }

        // ── Cierre ────────────────────────────────────────────────────────────
        $this->pdf->Ln(5);
        $fechaEmision = now()->format('d') . ' de ' . $this->mes(now()->format('n')) . ' de ' . now()->format('Y');
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(0, 6,
            utf8_decode('La presente constancia se expide a solicitud del interesado, a los ' . $fechaEmision . ', para los fines que estime conveniente.'),
            0, 'J'
        );

        // ── Firma ─────────────────────────────────────────────────────────────
        $this->pdf->Ln(14);
        $xFirma = 72;
        $this->pdf->SetDrawColor(80, 80, 80);
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Line($xFirma, $this->pdf->GetY(), $xFirma + 66, $this->pdf->GetY());
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell(0, 6, utf8_decode($medico), 0, 1, 'C');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 5, utf8_decode($especialidad), 0, 1, 'C');
        if (!empty($this->consulta->medico->numero_colegiatura)) {
            $this->pdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . $this->consulta->medico->numero_colegiatura), 0, 1, 'C');
        }
        $this->pdf->SetTextColor(0, 0, 0);

        // ── Pie de página ─────────────────────────────────────────────────────
        $this->pdf->SetY(-18);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line(25, $this->pdf->GetY(), 185, $this->pdf->GetY());
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Arial', 'I', 7);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 4,
            utf8_decode('Documento médico oficial — ' .
            ($this->empresa->razon_social ?? $this->empresa->nombre ?? '') .
            ' — ' . now()->format('d/m/Y H:i')),
            0, 0, 'C'
        );

        return response($this->pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reposo_Medico_' . $this->consulta->id . '.pdf"',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function encabezado(): void
    {
        $logoPath  = public_path('logo/app.png');
        $tieneLogo = file_exists($logoPath);

        if ($tieneLogo) {
            $this->pdf->Image($logoPath, 25, 12, 35);
        }

        $xTexto = $tieneLogo ? 65 : 25;
        $this->pdf->SetXY($xTexto, 12);
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->SetTextColor(41, 128, 185);
        $nombre = $this->empresa->razon_social ?? $this->empresa->nombre ?? '';
        $this->pdf->Cell(0, 7, utf8_decode($nombre), 0, 1, 'L');

        $this->pdf->SetXY($xTexto, $this->pdf->GetY());
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetTextColor(100, 100, 100);

        if (!empty($this->empresa->rif)) {
            $this->pdf->Cell(0, 5, utf8_decode('RIF: ' . $this->empresa->rif), 0, 1, 'L');
            $this->pdf->SetX($xTexto);
        }
        if ($this->empresa->telefono) {
            $this->pdf->Cell(0, 5, utf8_decode('Tel: ' . $this->empresa->telefono), 0, 1, 'L');
            $this->pdf->SetX($xTexto);
        }
        if ($this->empresa->direccion) {
            $this->pdf->SetFont('Arial', '', 8);
            $this->pdf->MultiCell(120, 4, utf8_decode($this->empresa->direccion), 0, 'L');
        }

        $this->pdf->SetTextColor(0, 0, 0);
        $yLinea = max($this->pdf->GetY(), 48) + 3;
        $this->pdf->SetDrawColor(41, 128, 185);
        $this->pdf->SetLineWidth(0.8);
        $this->pdf->Line(25, $yLinea, 185, $yLinea);
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetY($yLinea + 4);
    }

    private function mes(int $n): string
    {
        return [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ][$n] ?? '';
    }

    private function numerosALetras(int $n): string
    {
        $letras = [
            1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
            11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            20 => 'veinte', 30 => 'treinta', 60 => 'sesenta', 90 => 'noventa',
        ];
        return $letras[$n] ?? $n . '';
    }
}
