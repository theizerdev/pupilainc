<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;

class ConstanciaAsistenciaController extends Controller
{
    private Fpdf $pdf;
    private $empresa;
    private $consulta;

    public function generar(Request $request, $id)
    {
        $this->consulta = Consulta::with(['paciente.tutor', 'medico.especialidades', 'especialidad'])
            ->findOrFail($id);

        $this->empresa = auth()->user()->empresa;
        $paciente      = $this->consulta->paciente;

        $conAcompanante       = $request->boolean('acompanante');
        $nombreAcompanante    = trim($request->get('nombre_acompanante', ''));
        $documentoAcompanante = trim($request->get('documento_acompanante', ''));
        $relacionAcompanante  = trim($request->get('relacion_acompanante', ''));
        $motivo               = trim($request->get('motivo', 'Consulta médica'));

        $especialidad = $this->consulta->especialidad->nombre
            ?? $this->consulta->medico->especialidades()->take(1)->pluck('nombre')->first()
            ?? 'Medicina General';

        $medico        = 'Dr(a). ' . $this->consulta->medico->nombre_completo;
        $fechaConsulta = $this->consulta->fecha_consulta->format('d') . ' de '
            . $this->mes($this->consulta->fecha_consulta->format('n')) . ' de '
            . $this->consulta->fecha_consulta->format('Y');
        $horaConsulta  = $this->consulta->fecha_consulta->format('h:i A');
        $fechaEmision  = now()->format('d') . ' de ' . $this->mes(now()->format('n')) . ' de ' . now()->format('Y');

        // ── Inicializar PDF ───────────────────────────────────────────────────
        $this->pdf = new Fpdf('P', 'mm', 'A4');
        $this->pdf->SetMargins(25, 20, 25);
        $this->pdf->SetAutoPageBreak(true, 30);
        $this->pdf->AddPage();

        // ── Encabezado con logo ───────────────────────────────────────────────
        $this->encabezado();

        // ── Número de constancia ──────────────────────────────────────────────
        $this->pdf->SetFont('Arial', '', 8);
        $this->pdf->SetTextColor(130, 130, 130);
        $this->pdf->Cell(0, 5,
            utf8_decode('N° CONST-' . str_pad($this->consulta->id, 6, '0', STR_PAD_LEFT) .
            '   |   Emitida: ' . now()->format('d/m/Y H:i')),
            0, 1, 'R'
        );
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(4);

        // ── Título del documento ──────────────────────────────────────────────
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->Cell(0, 10, utf8_decode('CONSTANCIA DE ASISTENCIA MÉDICA'), 0, 1, 'C', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(8);

        // ── Cuerpo principal ──────────────────────────────────────────────────
        $this->pdf->SetFont('Arial', '', 11);

        // Línea de apertura
        $this->parrafo(
            'Quien suscribe, ' . $medico . ', médico especialista en ' . $especialidad .
            ', hace constar por medio de la presente que:'
        );
        $this->pdf->Ln(5);

        // Bloque destacado del paciente
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

        // Párrafo de asistencia
        $this->parrafo(
            'asistió a consulta de ' . $especialidad . ' el día ' . $fechaConsulta .
            ' a las ' . $horaConsulta . ', en las instalaciones de esta institución, ' .
            'por motivo de: ' . $motivo . '.'
        );

        // ── Acompañante manual ────────────────────────────────────────────────
        if ($conAcompanante && !empty($nombreAcompanante)) {
            $this->pdf->Ln(5);
            $relTexto = !empty($relacionAcompanante) ? ', en calidad de ' . $relacionAcompanante . ',' : '';
            $docTexto = !empty($documentoAcompanante)
                ? ', titular del documento N° ' . $documentoAcompanante . ','
                : '';

            $this->parrafo(
                'Asimismo, se hace constar que el/la ciudadano(a) ' . $nombreAcompanante .
                $docTexto . ' asistió como acompañante' . $relTexto .
                ' del paciente antes mencionado durante la consulta.'
            );
        }

        // ── Tutor automático para menores ─────────────────────────────────────
        if (!$conAcompanante && $paciente->es_menor && $paciente->tutor) {
            $tutor = $paciente->tutor;
            $this->pdf->Ln(5);
            $this->parrafo(
                'El paciente es menor de edad y asistió acompañado por su ' .
                $tutor->parentesco . ', ciudadano(a) ' . $tutor->nombre_completo .
                ', titular del documento N° ' . $tutor->documento_identidad . '.'
            );
        }

        // ── Cierre ────────────────────────────────────────────────────────────
        $this->pdf->Ln(5);
        $this->parrafo(
            'La presente constancia se expide a solicitud del interesado, a los ' .
            $fechaEmision . ', para los fines que estime conveniente.'
        );

        // ── Firma ─────────────────────────────────────────────────────────────
        $this->firma($medico, $especialidad);

        // ── Pie de página ─────────────────────────────────────────────────────
        $this->piePagina();

        return response($this->pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Constancia_Asistencia_' . $this->consulta->id . '.pdf"',
        ]);
    }

    // ── Métodos privados ──────────────────────────────────────────────────────

    private function encabezado(): void
    {
        $logoPath = public_path('logo/app.png');
        $tienelogo = file_exists($logoPath);

        if ($tienelogo) {
            $this->pdf->Image($logoPath, 25, 15, 35);
        }

        // Datos de la empresa a la derecha del logo
        $xTexto = $tienelogo ? 65 : 25;
        $this->pdf->SetXY($xTexto, 15);

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

        // Línea separadora
        $this->pdf->SetTextColor(0, 0, 0);
        $yLinea = max($this->pdf->GetY(), 50) + 3;
        $this->pdf->SetDrawColor(41, 128, 185);
        $this->pdf->SetLineWidth(0.8);
        $this->pdf->Line(25, $yLinea, 185, $yLinea);
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetY($yLinea + 5);
    }

    private function parrafo(string $texto): void
    {
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->SetTextColor(30, 30, 30);
        $this->pdf->MultiCell(0, 7, utf8_decode($texto), 0, 'J');
    }

    private function firma(string $medico, string $especialidad): void
    {
        $this->pdf->Ln(15);

        // Línea de firma centrada
        $xFirma = 72;
        $this->pdf->SetDrawColor(80, 80, 80);
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Line($xFirma, $this->pdf->GetY(), $xFirma + 66, $this->pdf->GetY());
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(0, 6, utf8_decode($medico), 0, 1, 'C');

        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 5, utf8_decode($especialidad), 0, 1, 'C');

        if (!empty($this->consulta->medico->numero_colegiatura)) {
            $this->pdf->Cell(0, 5,
                utf8_decode('Reg. Médico: ' . $this->consulta->medico->numero_colegiatura),
                0, 1, 'C'
            );
        }

        $this->pdf->SetTextColor(0, 0, 0);
    }

    private function piePagina(): void
    {
        $this->pdf->SetY(-20);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line(25, $this->pdf->GetY(), 185, $this->pdf->GetY());
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Arial', 'I', 7);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 4,
            utf8_decode('Documento generado electrónicamente — ' .
            ($this->empresa->razon_social ?? $this->empresa->nombre ?? '') .
            ' — ' . now()->format('d/m/Y H:i')),
            0, 0, 'C'
        );
    }

    private function mes(int $n): string
    {
        return [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ][$n] ?? '';
    }
}
