<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Codedge\Fpdf\Fpdf\Fpdf;

class ReposoController extends Controller
{
    public function generar($id)
    {
        $consulta = Consulta::with(['paciente', 'medico', 'reposo'])->findOrFail($id);
        
        if (!$consulta->reposo) {
            abort(404, 'No se encontró reposo médico para esta consulta');
        }
        
        $reposo = $consulta->reposo;
        $empresa = auth()->user()->empresa;
        
        $fpdf = new Fpdf('P', 'mm', 'A4');
        $fpdf->AddPage();
        $fpdf->SetMargins(20, 20, 20);

        // Logo
        $logoPath = public_path('logo/app.png');
        if (file_exists($logoPath)) {
            $fpdf->Image($logoPath, 20, 10, 40);
        }

        // Encabezado
        $fpdf->SetFont('Arial', 'B', 16);
        $fpdf->SetTextColor(0, 0, 0);
        $fpdf->Cell(0, 10, utf8_decode('CONSTANCIA DE REPOSO MÉDICO'), 0, 1, 'C');
        $fpdf->Ln(5);

        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->Cell(0, 6, utf8_decode($empresa->nombre), 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 10);
        $fpdf->Cell(0, 6, utf8_decode('RIF: ' . $empresa->rif), 0, 1, 'C');
        if ($empresa->telefono) {
            $fpdf->Cell(0, 6, utf8_decode('Tel: ' . $empresa->telefono), 0, 1, 'C');
        }
        
        $fpdf->Ln(8);
        $fpdf->SetDrawColor(41, 128, 185);
        $fpdf->SetLineWidth(0.5);
        $fpdf->Line(20, $fpdf->GetY(), 190, $fpdf->GetY());
        $fpdf->SetDrawColor(0, 0, 0);
        $fpdf->SetLineWidth(0.2);
        $fpdf->Ln(10);

        // Datos del paciente
        $fpdf->SetFillColor(234, 231, 230);
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->Cell(0, 7, utf8_decode('DATOS DEL PACIENTE'), 1, 1, 'C', true);
        
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(50, 6, utf8_decode('Nombre:'), 1, 0);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(120, 6, utf8_decode($consulta->paciente->nombre_completo), 1, 1);
        
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(50, 6, utf8_decode('Documento:'), 1, 0);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(120, 6, utf8_decode($consulta->paciente->documento_identidad), 1, 1);
        
        $fpdf->Ln(5);

        // Datos del reposo
        $fpdf->SetFillColor(234, 231, 230);
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->Cell(0, 7, utf8_decode('DATOS DEL REPOSO'), 1, 1, 'C', true);
        
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(50, 6, utf8_decode('Días de Reposo:'), 1, 0);
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->Cell(120, 6, $reposo->dias_reposo . ' días', 1, 1);
        
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(50, 6, utf8_decode('Desde:'), 1, 0);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(60, 6, $reposo->fecha_inicio->format('d/m/Y'), 1, 0);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(30, 6, utf8_decode('Hasta:'), 1, 0);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(30, 6, $reposo->fecha_fin->format('d/m/Y'), 1, 1);
        
        $fpdf->Ln(3);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(50, 6, utf8_decode('Motivo:'), 1, 0);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->MultiCell(120, 6, utf8_decode($reposo->motivo), 1);
        
        if ($reposo->observaciones) {
            $fpdf->SetFont('Arial', 'B', 9);
            $fpdf->Cell(50, 6, utf8_decode('Observaciones:'), 1, 0);
            $fpdf->SetFont('Arial', '', 9);
            $fpdf->MultiCell(120, 6, utf8_decode($reposo->observaciones), 1);
        }

        // Firma
        $fpdf->Ln(15);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(150, 150, 150);
        $fpdf->Cell(0, 5, utf8_decode('Documento médico oficial'), 0, 1, 'C');
        $fpdf->SetTextColor(0, 0, 0);
        
        $fpdf->Ln(10);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(0, 6, utf8_decode('_________________________________'), 0, 1, 'C');
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->Cell(0, 5, utf8_decode('Dr(a). ' . $consulta->medico->nombre_completo), 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . ($consulta->medico->numero_colegiatura ?? 'N/A')), 0, 1, 'C');
        $fpdf->Cell(0, 5, utf8_decode('Fecha de emisión: ' . now()->format('d/m/Y')), 0, 1, 'C');

        return response($fpdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reposo_Medico_' . $consulta->id . '.pdf"'
        ]);
    }
}
